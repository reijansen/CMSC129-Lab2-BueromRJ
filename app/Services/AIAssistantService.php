<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AIAssistantService
{
    private const SESSION_PENDING_ACTION_KEY = 'ai_assistant_pending_action';

    public function __construct(
        private readonly AIService $aiService,
        private readonly AIInquiryService $aiInquiryService
    ) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<string, mixed>
     */
    public function handle(int $userId, string $userMessage, array $history, Request $request): array
    {
        $actionPayload = $this->classifyAction($userMessage, $history);
        $action = (string) ($actionPayload['action'] ?? 'clarify');

        if ($action === 'inquiry') {
            return [
                'mode' => 'reply',
                'reply' => $this->aiInquiryService->reply($userId, $userMessage, $history),
            ];
        }

        if ($action === 'clarify') {
            $question = (string) ($actionPayload['question'] ?? '');

            return [
                'mode' => 'reply',
                'reply' => $question !== '' ? $question : 'What would you like to do (create, update, delete, or ask a question)?',
            ];
        }

        $resource = (string) ($actionPayload['resource'] ?? '');
        if (! in_array($resource, ['category', 'budget', 'transaction'], true)) {
            return [
                'mode' => 'reply',
                'reply' => 'Which resource do you want to work with: categories, budgets, or transactions?',
            ];
        }

        return match ($action) {
            'create' => $this->handleCreate($userId, $resource, $actionPayload['data'] ?? []),
            'update' => $this->handleUpdateOrDelete($request, $userId, 'update', $resource, $actionPayload),
            'delete' => $this->handleUpdateOrDelete($request, $userId, 'delete', $resource, $actionPayload),
            default => [
                'mode' => 'reply',
                'reply' => 'I can help with create, update, delete, or inquiries. Please rephrase your request.',
            ],
        };
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<string, mixed>
     */
    private function classifyAction(string $userMessage, array $history): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->actionSystemPrompt()],
            ...$this->recentContextMessages($history, 6),
            ['role' => 'user', 'content' => $userMessage],
        ];

        $result = $this->aiService->chat($messages);
        $content = (string) ($result['content'] ?? '');

        $json = $this->extractJsonObject($content);
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return [
                'action' => 'clarify',
                'question' => 'Do you want to create, update, delete, or ask a question about your data?',
            ];
        }

        return $decoded;
    }

    private function actionSystemPrompt(): string
    {
        return implode("\n", [
            'You are a strict JSON action router for a finance tracker app called Finko.',
            'Return STRICT JSON only. No markdown, no code fences, no extra text.',
            'If user is asking a question, return: { "action": "inquiry" }',
            'If unclear, return: { "action": "clarify", "question": "..." }',
            'Otherwise, return exactly one of these shapes:',
            '{ "action":"create", "resource":"category|budget|transaction", "data":{...} }',
            '{ "action":"update", "resource":"category|budget|transaction", "selector":{ "id":123 | "title":"..." | "name":"..." }, "data":{...} }',
            '{ "action":"delete", "resource":"category|budget|transaction", "selector":{ "id":123 | "title":"..." | "name":"..." } }',
            'Rules:',
            '- Never output multi-record operations. If user says "all" or "every", use action "clarify" and ask them to specify one record by id or exact name/title.',
            '- For updates/deletes, always include a selector.',
            '- Use these fields when possible:',
            '  Category: name, type (expense|income|both), color (#RRGGBB), description',
            '  Budget: category_id OR category_name, title, allocated_amount, period_start (YYYY-MM-DD), period_end (YYYY-MM-DD), status (active|completed|exceeded|archived), notes',
            '  Transaction: category_id OR category_name, budget_id OR budget_title (nullable), title, amount, type (income|expense), transaction_date (YYYY-MM-DD), payment_method, notes',
        ]);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<int, array{role: string, content: string}>
     */
    private function recentContextMessages(array $history, int $max): array
    {
        $withoutSystem = array_values(array_filter($history, fn (array $m) => ($m['role'] ?? null) !== 'system'));
        $slice = array_slice($withoutSystem, -$max);

        return array_map(function (array $message): array {
            $role = (string) ($message['role'] ?? 'user');
            if (! in_array($role, ['user', 'assistant'], true)) {
                $role = 'user';
            }

            return [
                'role' => $role,
                'content' => (string) ($message['content'] ?? ''),
            ];
        }, $slice);
    }

    private function extractJsonObject(string $text): string
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return '{}';
        }

        $start = strpos($trimmed, '{');
        $end = strrpos($trimmed, '}');

        if ($start === false || $end === false || $end <= $start) {
            return '{}';
        }

        return substr($trimmed, $start, $end - $start + 1);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function handleCreate(int $userId, string $resource, array $data): array
    {
        if (! is_array($data)) {
            $data = [];
        }

        return match ($resource) {
            'category' => $this->createCategory($userId, $data),
            'budget' => $this->createBudget($userId, $data),
            'transaction' => $this->createTransaction($userId, $data),
            default => [
                'mode' => 'reply',
                'reply' => 'Unsupported resource.',
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function handleUpdateOrDelete(Request $request, int $userId, string $op, string $resource, array $payload): array
    {
        $selector = $payload['selector'] ?? null;
        if (! is_array($selector)) {
            return [
                'mode' => 'reply',
                'reply' => 'Please specify which record to ' . $op . ' (by id or name/title).',
            ];
        }

        if (isset($selector['id'])) {
            $id = (int) $selector['id'];
            if ($id <= 0) {
                return [
                    'mode' => 'reply',
                    'reply' => 'Please provide a valid id.',
                ];
            }

            $record = $this->findById($userId, $resource, $id);
            if (! $record) {
                return [
                    'mode' => 'reply',
                    'reply' => "I couldn't find that {$resource} (id {$id}).",
                ];
            }

            return $this->propose($request, $op, $resource, ['id' => $id], $payload['data'] ?? null, $record);
        }

        $nameOrTitle = null;
        if (isset($selector['name']) && is_string($selector['name'])) {
            $nameOrTitle = trim($selector['name']);
        }
        if (! $nameOrTitle && isset($selector['title']) && is_string($selector['title'])) {
            $nameOrTitle = trim($selector['title']);
        }

        if (! $nameOrTitle) {
            return [
                'mode' => 'reply',
                'reply' => 'Please specify which record to ' . $op . ' (by id or exact name/title).',
            ];
        }

        if ($this->looksLikeMultiRecord($nameOrTitle)) {
            return [
                'mode' => 'reply',
                'reply' => 'I can only help with one record at a time right now. Please specify a single record by id or exact name/title.',
            ];
        }

        $matches = $this->findMatchesByNameOrTitle($userId, $resource, $nameOrTitle);
        if ($matches->count() === 0) {
            return [
                'mode' => 'reply',
                'reply' => "I couldn't find a matching {$resource} for \"{$nameOrTitle}\".",
            ];
        }

        if ($matches->count() > 1) {
            $examples = $matches->take(5)->map(function ($model) use ($resource): string {
                $label = $resource === 'category' ? $model->name : $model->title;

                return "- #{$model->id}: {$label}";
            })->implode("\n");

            return [
                'mode' => 'reply',
                'reply' => "I found multiple matches. Which one do you mean?\n{$examples}",
            ];
        }

        $record = $matches->first();
        $selectorKey = $resource === 'category' ? 'name' : 'title';

        return $this->propose($request, $op, $resource, [$selectorKey => $nameOrTitle], $payload['data'] ?? null, $record);
    }

    private function looksLikeMultiRecord(string $value): bool
    {
        $lower = strtolower($value);

        return Str::contains($lower, [' all ', ' every ', ' everything', ' all', ' every', '*']);
    }

    private function propose(Request $request, string $op, string $resource, array $selector, mixed $data, $record): array
    {
        if ($op === 'update' && ! is_array($data)) {
            return [
                'mode' => 'reply',
                'reply' => 'What fields do you want to update?',
            ];
        }

        $proposedAction = [
            'action' => $op,
            'resource' => $resource,
            'selector' => $selector,
            'data' => is_array($data) ? $this->filterAllowedData($resource, $data) : null,
        ];

        $preview = $this->previewFor($resource, $record, $proposedAction['data']);

        $request->session()->put(self::SESSION_PENDING_ACTION_KEY, $proposedAction);

        $title = $op === 'delete' ? 'Proposed delete' : 'Proposed update';
        $confirmLine = 'Confirm required before I execute this.';

        return [
            'mode' => 'propose',
            'reply' => "{$title}: {$confirmLine}",
            'proposed_action' => $proposedAction,
            'preview' => $preview,
        ];
    }

    private function previewFor(string $resource, $record, ?array $data): array
    {
        $base = match ($resource) {
            'category' => [
                'id' => $record->id,
                'name' => $record->name,
                'type' => $record->type,
                'color' => $record->color,
                'description' => $record->description,
            ],
            'budget' => [
                'id' => $record->id,
                'title' => $record->title,
                'allocated_amount' => $record->allocated_amount,
                'period_start' => Carbon::parse($record->period_start)->toDateString(),
                'period_end' => Carbon::parse($record->period_end)->toDateString(),
                'status' => $record->status,
                'category_id' => $record->category_id,
            ],
            'transaction' => [
                'id' => $record->id,
                'title' => $record->title,
                'amount' => $record->amount,
                'type' => $record->type,
                'transaction_date' => Carbon::parse($record->transaction_date)->toDateString(),
                'category_id' => $record->category_id,
                'budget_id' => $record->budget_id,
            ],
            default => [],
        };

        if (! $data) {
            return ['before' => $base];
        }

        $after = [...$base, ...$data];

        return [
            'before' => $base,
            'after' => $after,
        ];
    }

    private function filterAllowedData(string $resource, array $data): array
    {
        $allowed = match ($resource) {
            'category' => ['name', 'type', 'color', 'description'],
            'budget' => ['category_id', 'title', 'allocated_amount', 'period_start', 'period_end', 'status', 'notes'],
            'transaction' => ['budget_id', 'category_id', 'title', 'amount', 'type', 'transaction_date', 'payment_method', 'notes'],
            default => [],
        };

        $filtered = [];
        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $filtered[$key] = $data[$key];
            }
        }

        return $filtered;
    }

    private function findById(int $userId, string $resource, int $id)
    {
        return match ($resource) {
            'category' => Category::query()->where('user_id', $userId)->where('id', $id)->first(),
            'budget' => Budget::query()->where('user_id', $userId)->where('id', $id)->first(),
            'transaction' => Transaction::query()->where('user_id', $userId)->where('id', $id)->first(),
            default => null,
        };
    }

    private function findMatchesByNameOrTitle(int $userId, string $resource, string $value)
    {
        return match ($resource) {
            'category' => Category::query()
                ->where('user_id', $userId)
                ->where('name', 'ilike', '%' . $value . '%')
                ->orderBy('name')
                ->get(['id', 'name']),
            'budget' => Budget::query()
                ->where('user_id', $userId)
                ->where('title', 'ilike', '%' . $value . '%')
                ->orderBy('title')
                ->get(['id', 'title']),
            'transaction' => Transaction::query()
                ->where('user_id', $userId)
                ->where('title', 'ilike', '%' . $value . '%')
                ->orderByDesc('transaction_date')
                ->get(['id', 'title', 'transaction_date']),
            default => collect(),
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function createCategory(int $userId, array $data): array
    {
        $payload = [
            'name' => $data['name'] ?? null,
            'type' => $data['type'] ?? null,
            'color' => $data['color'] ?? null,
            'description' => $data['description'] ?? null,
        ];

        $validator = Validator::make($payload, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:expense,income,both'],
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return [
                'mode' => 'reply',
                'reply' => 'I could not create the category: ' . $validator->errors()->first(),
            ];
        }

        $category = Category::create([
            'user_id' => $userId,
            'name' => $payload['name'],
            'type' => $payload['type'],
            'color' => $payload['color'] ?: '#22c55e',
            'description' => $payload['description'],
        ]);

        return [
            'mode' => 'reply',
            'reply' => "Created category #{$category->id}: {$category->name} ({$category->type}).",
            'created' => [
                'resource' => 'category',
                'id' => $category->id,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function createBudget(int $userId, array $data): array
    {
        $categoryId = $data['category_id'] ?? null;
        if (! $categoryId && isset($data['category_name']) && is_string($data['category_name'])) {
            $categoryId = Category::query()
                ->where('user_id', $userId)
                ->where('name', 'ilike', '%' . trim($data['category_name']) . '%')
                ->value('id');
        }

        $payload = [
            'category_id' => $categoryId,
            'title' => $data['title'] ?? null,
            'allocated_amount' => $data['allocated_amount'] ?? null,
            'period_start' => $data['period_start'] ?? null,
            'period_end' => $data['period_end'] ?? null,
            'status' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
        ];

        $validator = Validator::make($payload, [
            'category_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'allocated_amount' => ['required', 'numeric', 'min:0'],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'status' => ['required', 'in:active,completed,exceeded,archived'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return [
                'mode' => 'reply',
                'reply' => 'I could not create the budget: ' . $validator->errors()->first(),
            ];
        }

        $categoryExists = Category::query()
            ->where('user_id', $userId)
            ->where('id', (int) $payload['category_id'])
            ->exists();

        if (! $categoryExists) {
            return [
                'mode' => 'reply',
                'reply' => 'I could not create the budget: category does not exist.',
            ];
        }

        $budget = Budget::create([
            ...$validator->validated(),
            'user_id' => $userId,
        ]);

        return [
            'mode' => 'reply',
            'reply' => "Created budget #{$budget->id}: {$budget->title}.",
            'created' => [
                'resource' => 'budget',
                'id' => $budget->id,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function createTransaction(int $userId, array $data): array
    {
        $categoryId = $data['category_id'] ?? null;
        if (! $categoryId && isset($data['category_name']) && is_string($data['category_name'])) {
            $categoryId = Category::query()
                ->where('user_id', $userId)
                ->where('name', 'ilike', '%' . trim($data['category_name']) . '%')
                ->value('id');
        }

        $budgetId = $data['budget_id'] ?? null;
        if (! $budgetId && isset($data['budget_title']) && is_string($data['budget_title'])) {
            $budgetId = Budget::query()
                ->where('user_id', $userId)
                ->where('title', 'ilike', '%' . trim($data['budget_title']) . '%')
                ->value('id');
        }

        $payload = [
            'budget_id' => $budgetId,
            'category_id' => $categoryId,
            'title' => $data['title'] ?? null,
            'amount' => $data['amount'] ?? null,
            'type' => $data['type'] ?? null,
            'transaction_date' => $data['transaction_date'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        $categoryType = null;
        if ($payload['category_id']) {
            $categoryType = Category::query()
                ->where('user_id', $userId)
                ->where('id', (int) $payload['category_id'])
                ->value('type');
        }

        $requiresBudget = $categoryType === 'expense' || $categoryType === 'both';

        $validator = Validator::make($payload, [
            'budget_id' => [
                'nullable',
                'integer',
            ],
            'category_id' => ['required', 'integer'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:income,expense'],
            'transaction_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return [
                'mode' => 'reply',
                'reply' => 'I could not create the transaction: ' . $validator->errors()->first(),
            ];
        }

        if ($requiresBudget && ! $payload['budget_id']) {
            return [
                'mode' => 'reply',
                'reply' => 'I could not create the transaction: this category requires a budget.',
            ];
        }

        $categoryExists = Category::query()
            ->where('user_id', $userId)
            ->where('id', (int) $payload['category_id'])
            ->exists();

        if (! $categoryExists) {
            return [
                'mode' => 'reply',
                'reply' => 'I could not create the transaction: category does not exist.',
            ];
        }

        if ($payload['budget_id']) {
            $budgetExists = Budget::query()
                ->where('user_id', $userId)
                ->where('id', (int) $payload['budget_id'])
                ->exists();

            if (! $budgetExists) {
                return [
                    'mode' => 'reply',
                    'reply' => 'I could not create the transaction: budget does not exist.',
                ];
            }
        }

        $transaction = Transaction::create([
            ...$validator->validated(),
            'user_id' => $userId,
            'attachment_path' => null,
        ]);

        $amount = number_format((float) $transaction->amount, 2);
        $date = Carbon::parse($transaction->transaction_date)->toDateString();

        return [
            'mode' => 'reply',
            'reply' => "Created transaction #{$transaction->id}: {$transaction->type} \"{$transaction->title}\" ({$amount}) on {$date}.",
            'created' => [
                'resource' => 'transaction',
                'id' => $transaction->id,
            ],
        ];
    }
}
