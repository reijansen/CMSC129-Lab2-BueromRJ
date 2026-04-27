<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class AIInquiryService
{
    public function __construct(
        private readonly AIService $aiService
    ) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    public function reply(int $userId, string $userMessage, array $history): string
    {
        $result = $this->replyWithContext($userId, $userMessage, $history, AIContextState::default());

        return (string) ($result['reply'] ?? '');
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @param  array<string, mixed>  $contextState
     * @return array{reply: string, context_update: array<string, mixed>}
     */
    public function replyWithContext(int $userId, string $userMessage, array $history, array $contextState): array
    {
        $contextState = AIContextState::normalize($contextState);

        $implicit = $this->tryImplicitFollowupReply($userId, $userMessage, $contextState);
        if ($implicit) {
            return $implicit;
        }

        $intentPayload = $this->classifyIntent($userMessage, $history);

        return $this->replyFromIntentPayload($userId, $intentPayload, $userMessage, $contextState);
    }

    /**
     * Use a precomputed intent payload (e.g., provided by an assistant router) to avoid an extra LLM call.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @param  array<string, mixed>  $contextState
     * @param  array<string, mixed>  $intentPayload
     * @return array{reply: string, context_update: array<string, mixed>}
     */
    public function replyWithContextFromIntentPayload(int $userId, string $userMessage, array $history, array $contextState, array $intentPayload): array
    {
        $contextState = AIContextState::normalize($contextState);

        $implicit = $this->tryImplicitFollowupReply($userId, $userMessage, $contextState);
        if ($implicit) {
            return $implicit;
        }

        return $this->replyFromIntentPayload($userId, $intentPayload, $userMessage, $contextState);
    }

    /**
     * @param  array<string, mixed>  $intentPayload
     * @param  array<string, mixed>  $contextState
     * @return array{reply: string, context_update: array<string, mixed>}
     */
    private function replyFromIntentPayload(int $userId, array $intentPayload, string $userMessage, array $contextState): array
    {
        $intent = (string) ($intentPayload['intent'] ?? 'clarify');
        $filters = is_array($intentPayload['filters'] ?? null) ? $intentPayload['filters'] : [];

        $resolved = $this->resolveFilters($intent, $userMessage, $filters, $contextState);

        if ($intent === 'clarify') {
            $clarify = (string) ($intentPayload['clarify_question'] ?? '');

            return [
                'reply' => $clarify !== '' ? $clarify : $this->defaultClarify(),
                'context_update' => [],
            ];
        }

        return match ($intent) {
            'list_categories' => $this->listCategories($userId),
            'count_categories' => $this->countCategories($userId),
            'list_budgets' => $this->listBudgets($userId, (int) ($resolved['limit'] ?? 10)),
            'budget_status' => $this->budgetStatus(
                $userId,
                (string) ($resolved['budget_title'] ?? ''),
                $resolved['date_from'] ?? null,
                $resolved['date_to'] ?? null,
                (int) ($resolved['limit'] ?? 10)
            ),
            'list_transactions' => $this->listTransactions(
                $userId,
                $resolved['date_from'] ?? null,
                $resolved['date_to'] ?? null,
                (int) ($resolved['limit'] ?? 10)
            ),
            'sum_expenses' => $this->sumByType(
                $userId,
                'expense',
                $resolved['date_from'] ?? null,
                $resolved['date_to'] ?? null,
                (string) ($resolved['category_name'] ?? '')
            ),
            'sum_income' => $this->sumByType(
                $userId,
                'income',
                $resolved['date_from'] ?? null,
                $resolved['date_to'] ?? null,
                ''
            ),
            'top_spending_categories' => $this->topSpendingCategories(
                $userId,
                $resolved['date_from'] ?? null,
                $resolved['date_to'] ?? null,
                5
            ),
            'max_expense' => $this->maxExpense(
                $userId,
                $resolved['date_from'] ?? null,
                $resolved['date_to'] ?? null
            ),
            'filter_transactions' => $this->filterTransactions($userId, $resolved),
            default => [
                'reply' => $this->defaultClarify(),
                'context_update' => [],
            ],
        };
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<string, mixed>
     */
    private function classifyIntent(string $userMessage, array $history): array
    {
        $prompt = $this->intentSystemPrompt();
        $context = $this->recentContextMessages($history, 6);

        $messages = array_merge(
            [['role' => 'system', 'content' => $prompt]],
            $context,
            [['role' => 'user', 'content' => $userMessage]]
        );

        $routerModel = (string) config('ai.ollama.router_model', config('ai.ollama.model'));
        $result = $this->aiService->chat($messages, $routerModel);
        $content = (string) ($result['content'] ?? '');

        $json = $this->extractJsonObject($content);
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            return [
                'intent' => 'clarify',
                'clarify_question' => $this->defaultClarify(),
            ];
        }

        return $decoded;
    }

    private function intentSystemPrompt(): string
    {
        return implode("\n", [
            'You are an intent classifier for a student finance tracker app called Finko.',
            'Return STRICT JSON only. No markdown, no code fences, no extra text.',
            'Choose one intent and set filters. If unclear, use intent "clarify" and add "clarify_question".',
            'Valid intents:',
            '- list_categories',
            '- count_categories',
            '- list_budgets',
            '- budget_status',
            '- list_transactions',
            '- sum_expenses',
            '- sum_income',
            '- top_spending_categories',
            '- max_expense',
            '- filter_transactions',
            'Output schema:',
            '{',
            '  "intent": "...",',
            '  "filters": {',
            '    "date_from": "YYYY-MM-DD|null",',
            '    "date_to": "YYYY-MM-DD|null",',
            '    "type": "income|expense|null",',
            '    "category_name": "string|null",',
            '    "budget_title": "string|null",',
            '    "payment_method": "string|null",',
            '    "limit": 10',
            '  }',
            '}',
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
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function resolveFilters(string $intent, string $userMessage, array $filters, array $contextState): array
    {
        $limit = (int) ($filters['limit'] ?? 10);
        if ($limit <= 0) {
            $limit = 10;
        }
        $limit = min($limit, 20);

        $dateFrom = $this->parseDateOrNull($filters['date_from'] ?? null);
        $dateTo = $this->parseDateOrNull($filters['date_to'] ?? null);

        if (! $dateFrom && ! $dateTo) {
            [$dateFrom, $dateTo] = $this->inferRelativeDateRange($userMessage);
        }

        if (! $dateFrom && ! $dateTo) {
            $fallback = $this->fallbackDateRangeFromContext($intent, $contextState);
            if ($fallback) {
                $dateFrom = $this->parseDateOrNull($fallback['date_from'] ?? null);
                $dateTo = $this->parseDateOrNull($fallback['date_to'] ?? null);
            }
        }

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'type' => $this->normalizeType($filters['type'] ?? null),
            'category_name' => $this->normalizeOptionalString($filters['category_name'] ?? null),
            'budget_title' => $this->normalizeOptionalString($filters['budget_title'] ?? null),
            'payment_method' => $this->normalizeOptionalString($filters['payment_method'] ?? null),
            'limit' => $limit,
        ];
    }

    private function normalizeType(mixed $type): ?string
    {
        if (! is_string($type)) {
            return null;
        }

        $value = strtolower(trim($type));
        if (in_array($value, ['income', 'expense'], true)) {
            return $value;
        }

        return null;
    }

    private function normalizeOptionalString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function parseDateOrNull(mixed $value): ?Carbon
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        if ($trimmed === '' || strtolower($trimmed) === 'null') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $trimmed)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array{0: Carbon|null, 1: Carbon|null}
     */
    private function inferRelativeDateRange(string $text): array
    {
        $lower = strtolower($text);
        $today = Carbon::today();

        if (Str::contains($lower, 'today')) {
            return [$today->copy()->startOfDay(), $today->copy()->endOfDay()];
        }

        if (Str::contains($lower, 'yesterday')) {
            $d = $today->copy()->subDay();

            return [$d->copy()->startOfDay(), $d->copy()->endOfDay()];
        }

        if (Str::contains($lower, 'this week')) {
            return [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()];
        }

        if (Str::contains($lower, 'last week')) {
            $start = $today->copy()->startOfWeek()->subWeek();

            return [$start->copy(), $start->copy()->endOfWeek()];
        }

        if (Str::contains($lower, 'this month')) {
            return [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()];
        }

        if (Str::contains($lower, 'last month')) {
            $start = $today->copy()->startOfMonth()->subMonth();

            return [$start->copy(), $start->copy()->endOfMonth()];
        }

        if (Str::contains($lower, 'this year')) {
            return [$today->copy()->startOfYear(), $today->copy()->endOfYear()];
        }

        if (Str::contains($lower, 'last year')) {
            $start = $today->copy()->startOfYear()->subYear();

            return [$start->copy(), $start->copy()->endOfYear()];
        }

        return [null, null];
    }

    private function listCategories(int $userId): array
    {
        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get(['id', 'name', 'type']);

        if ($categories->isEmpty()) {
            return [
                'reply' => "You don't have any categories yet.",
                'context_update' => [
                    'last_list' => null,
                    'last_entity_type' => null,
                    'last_entity_id' => null,
                ],
            ];
        }

        $slice = $categories->take(15)->values();
        $lines = $slice->map(function (Category $c, int $index): string {
            $i = $index + 1;

            return "{$i}) #{$c->id} — {$c->name}\n"
                . "   Type: {$c->type}";
        })->all();

        $extra = $categories->count() > 15 ? "\n(Showing 15 of {$categories->count()} categories.)" : '';

        $ids = $slice->pluck('id')->all();

        return [
            'reply' => "Here are your categories:\n\n" . implode("\n\n", $lines) . $extra,
            'context_update' => [
                'last_list' => [
                    'resource' => 'category',
                    'ids' => $ids,
                    'filters' => [],
                ],
                'last_entity_type' => 'category',
                'last_entity_id' => (int) ($ids[0] ?? 0) ?: null,
            ],
        ];
    }

    private function countCategories(int $userId): array
    {
        $counts = Category::query()
            ->where('user_id', $userId)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $total = (int) $counts->sum();

        if ($total === 0) {
            return [
                'reply' => "You don't have any categories yet.",
                'context_update' => [],
            ];
        }

        $expense = (int) ($counts['expense'] ?? 0);
        $income = (int) ($counts['income'] ?? 0);
        $both = (int) ($counts['both'] ?? 0);

        return [
            'reply' => "You have {$total} categories total.\n\n"
                . "Expense: {$expense}\n"
                . "Income: {$income}\n"
                . "Both: {$both}",
            'context_update' => [],
        ];
    }

    private function listBudgets(int $userId, int $limit): array
    {
        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->with('category')
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($budgets->isEmpty()) {
            return [
                'reply' => "You don't have any budgets yet.",
                'context_update' => [],
            ];
        }

        $lines = $budgets->values()->map(function (Budget $b, int $index): string {
            $i = $index + 1;
            $category = $b->category?->name ?? 'Uncategorized';
            $start = Carbon::parse($b->period_start)->toDateString();
            $end = Carbon::parse($b->period_end)->toDateString();
            $allocated = $this->formatMoney((float) $b->allocated_amount);

            return "{$i}) #{$b->id} — {$b->title}\n"
                . "   Category: {$category}\n"
                . "   Allocated: {$allocated}\n"
                . "   Period: {$start} to {$end}\n"
                . "   Status: {$b->status}";
        })->all();

        $ids = $budgets->pluck('id')->all();

        return [
            'reply' => "Here are your latest budgets:\n\n" . implode("\n\n", $lines),
            'context_update' => [
                'last_list' => [
                    'resource' => 'budget',
                    'ids' => $ids,
                    'filters' => [],
                ],
                'last_entity_type' => 'budget',
                'last_entity_id' => (int) ($ids[0] ?? 0) ?: null,
            ],
        ];
    }

    private function listTransactions(int $userId, ?Carbon $from, ?Carbon $to, int $limit): array
    {
        $query = Transaction::query()
            ->where('user_id', $userId)
            ->with(['category', 'budget']);

        $this->applyDateRange($query, $from, $to);

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($transactions->isEmpty()) {
            return [
                'reply' => "No transactions found for that query.\n\nTry: \"List my latest 10 transactions\" or \"Show expenses this week\".",
                'context_update' => [
                    'last_list' => null,
                ],
            ];
        }

        $ids = $transactions->pluck('id')->all();

        return [
            'reply' => $this->formatTransactionsReply($transactions),
            'context_update' => [
                'last_list' => [
                    'resource' => 'transaction',
                    'ids' => $ids,
                    'filters' => $this->dateRangeFilters($from, $to),
                ],
                'last_entity_type' => 'transaction',
                'last_entity_id' => (int) ($ids[0] ?? 0) ?: null,
                'last_date_range' => $this->dateRangeForContext($from, $to),
            ],
        ];
    }

    private function sumByType(int $userId, string $type, ?Carbon $from, ?Carbon $to, string $categoryName): array
    {
        if (! $from && ! $to) {
            return [
                'reply' => $this->needDateRange($type === 'expense' ? 'expenses' : 'income'),
                'context_update' => [],
            ];
        }

        $query = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', $type);

        $this->applyDateRange($query, $from, $to);

        $category = null;
        if ($type === 'expense' && $categoryName !== '') {
            $category = Category::query()
                ->where('user_id', $userId)
                ->where('name', 'ilike', $categoryName)
                ->first();

            if (! $category) {
                $category = Category::query()
                    ->where('user_id', $userId)
                    ->where('name', 'ilike', '%' . $categoryName . '%')
                    ->first();
            }

            if (! $category) {
                return [
                    'reply' => "I couldn't find a category matching \"{$categoryName}\". Try asking: \"List my categories\".",
                    'context_update' => [],
                ];
            }

            $query->where('category_id', $category->id);
        }

        $sum = (float) $query->sum('amount');
        $formatted = $this->formatMoney($sum);

        $range = $this->describeDateRange($from, $to);
        if ($type === 'expense') {
            $suffix = $category ? " in category \"{$category->name}\"" : '';

            return [
                'reply' => "Total expenses{$suffix}{$range}: {$formatted}",
                'context_update' => [
                    'last_date_range' => $this->dateRangeForContext($from, $to),
                ],
            ];
        }

        return [
            'reply' => "Total income{$range}: {$formatted}",
            'context_update' => [
                'last_date_range' => $this->dateRangeForContext($from, $to),
            ],
        ];
    }

    private function topSpendingCategories(int $userId, ?Carbon $from, ?Carbon $to, int $limit): array
    {
        if (! $from && ! $to) {
            return [
                'reply' => $this->needDateRange('top spending categories'),
                'context_update' => [],
            ];
        }

        $query = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('categories.user_id', $userId);

        $this->applyDateRange($query, $from, $to);

        $rows = $query
            ->selectRaw('categories.name as category_name, SUM(transactions.amount) as total_spent')
            ->groupBy('categories.name')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'reply' => "No expense transactions found for that date range.\n\nTry a different range like \"this month\" or \"last month\".",
                'context_update' => [
                    'last_date_range' => $this->dateRangeForContext($from, $to),
                ],
            ];
        }

        $range = $this->describeDateRange($from, $to);
        $lines = $rows->map(function ($row): string {
            $name = (string) $row->category_name;
            $total = $this->formatMoney((float) $row->total_spent);

            return "- {$name}: {$total}";
        })->all();

        return [
            'reply' => "Top spending categories{$range}:\n" . implode("\n", $lines),
            'context_update' => [
                'last_date_range' => $this->dateRangeForContext($from, $to),
            ],
        ];
    }

    private function maxExpense(int $userId, ?Carbon $from, ?Carbon $to): array
    {
        if (! $from && ! $to) {
            return [
                'reply' => $this->needDateRange('maximum expense'),
                'context_update' => [],
            ];
        }

        $query = Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'expense')
            ->with('category');

        $this->applyDateRange($query, $from, $to);

        $transaction = $query
            ->orderByDesc('amount')
            ->orderByDesc('transaction_date')
            ->first();

        if (! $transaction) {
            return [
                'reply' => "No expense transactions found for that date range.\n\nTry: \"List my latest 10 transactions\".",
                'context_update' => [
                    'last_date_range' => $this->dateRangeForContext($from, $to),
                ],
            ];
        }

        $range = $this->describeDateRange($from, $to);
        $date = Carbon::parse($transaction->transaction_date)->toDateString();
        $amount = $this->formatMoney((float) $transaction->amount);
        $category = $transaction->category?->name ?? 'Unknown Category';

        return [
            'reply' => "Maximum expense{$range}: {$amount}\n\n"
                . "Transaction: #{$transaction->id} — {$transaction->title}\n"
                . "Category: {$category}\n"
                . "Date: {$date}",
            'context_update' => [
                'last_entity_type' => 'transaction',
                'last_entity_id' => (int) $transaction->id,
                'last_date_range' => $this->dateRangeForContext($from, $to),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filterTransactions(int $userId, array $filters): array
    {
        $from = $filters['date_from'] ?? null;
        $to = $filters['date_to'] ?? null;
        $type = $filters['type'] ?? null;
        $categoryName = (string) ($filters['category_name'] ?? '');
        $paymentMethod = (string) ($filters['payment_method'] ?? '');
        $limit = (int) ($filters['limit'] ?? 10);

        $query = Transaction::query()
            ->where('user_id', $userId)
            ->with(['category', 'budget']);

        $this->applyDateRange($query, $from, $to);

        if (is_string($type) && in_array($type, ['income', 'expense'], true)) {
            $query->where('type', $type);
        }

        if ($paymentMethod !== '') {
            $query->where('payment_method', 'ilike', '%' . $paymentMethod . '%');
        }

        if ($categoryName !== '') {
            $category = Category::query()
                ->where('user_id', $userId)
                ->where('name', 'ilike', $categoryName)
                ->first();

            if (! $category) {
                $category = Category::query()
                    ->where('user_id', $userId)
                    ->where('name', 'ilike', '%' . $categoryName . '%')
                    ->first();
            }

            if (! $category) {
                return [
                    'reply' => "I couldn't find a category matching \"{$categoryName}\". Try asking: \"List my categories\".",
                    'context_update' => [],
                ];
            }

            $query->where('category_id', $category->id);
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($transactions->isEmpty()) {
            return [
                'reply' => "No transactions found for that query.\n\nTry removing one filter (date, payment method, or category) and retry.",
                'context_update' => [
                    'last_list' => null,
                    'last_date_range' => $this->dateRangeForContext($from, $to),
                ],
            ];
        }

        $ids = $transactions->pluck('id')->all();

        return [
            'reply' => $this->formatTransactionsReply($transactions),
            'context_update' => [
                'last_list' => [
                    'resource' => 'transaction',
                    'ids' => $ids,
                    'filters' => array_filter([
                        ...$this->dateRangeFilters($from, $to),
                        'type' => is_string($type) ? $type : null,
                        'category_name' => $categoryName !== '' ? $categoryName : null,
                        'payment_method' => $paymentMethod !== '' ? $paymentMethod : null,
                    ], fn ($v) => $v !== null),
                ],
                'last_entity_type' => 'transaction',
                'last_entity_id' => (int) ($ids[0] ?? 0) ?: null,
                'last_date_range' => $this->dateRangeForContext($from, $to),
            ],
        ];
    }

    private function budgetStatus(int $userId, string $budgetTitle, ?Carbon $from, ?Carbon $to, int $limit): array
    {
        $budgetsQuery = Budget::query()
            ->where('user_id', $userId)
            ->with('category')
            ->orderByDesc('period_start')
            ->orderByDesc('id');

        if ($budgetTitle !== '') {
            $budgetsQuery->where('title', 'ilike', '%' . $budgetTitle . '%');
        }

        $budgets = $budgetsQuery->limit($limit)->get();

        if ($budgets->isEmpty()) {
            return [
                'reply' => $budgetTitle !== ''
                    ? "I couldn't find a budget matching \"{$budgetTitle}\"."
                    : "You don't have any budgets yet.",
                'context_update' => [],
            ];
        }

        $spentByBudget = $this->spentByBudget($userId, $budgets, $from, $to);

        $lines = $budgets->values()->map(function (Budget $b, int $index) use ($spentByBudget, $from, $to): string {
            $i = $index + 1;
            $categoryType = $b->category?->type ?? 'expense';
            $trackedType = $categoryType === 'income' ? 'income' : 'expense';
            $spent = (float) ($spentByBudget[$b->id][$trackedType] ?? 0);
            $allocated = (float) $b->allocated_amount;
            $remaining = $allocated - $spent;
            $category = $b->category?->name ?? 'Uncategorized';

            $spentText = $this->formatMoney($spent);
            $allocatedText = $this->formatMoney($allocated);
            $remainingText = $this->formatMoney($remaining);

            $range = ($from || $to) ? $this->describeDateRange($from, $to) : ' (budget period)';

            if ($trackedType === 'income') {
                $status = $spent >= $allocated ? 'MET' : 'BELOW';
                $stillNeededText = $this->formatMoney(max(0, $allocated - $spent));

                return "{$i}) #{$b->id} — {$b->title} ({$category}){$range}\n"
                    . "   Received: {$spentText}\n"
                    . "   Target: {$allocatedText}\n"
                    . "   Still needed: {$stillNeededText}\n"
                    . "   Status: {$status}";
            }

            $status = $spent > $allocated ? 'EXCEEDED' : 'OK';

            return "{$i}) #{$b->id} — {$b->title} ({$category}){$range}\n"
                . "   Spent: {$spentText}\n"
                . "   Allocated: {$allocatedText}\n"
                . "   Remaining: {$remainingText}\n"
                . "   Status: {$status}";
        })->all();

        $ids = $budgets->pluck('id')->all();

        return [
            'reply' => "Budget status:\n\n" . implode("\n\n", $lines),
            'context_update' => [
                'last_list' => [
                    'resource' => 'budget',
                    'ids' => $ids,
                    'filters' => array_filter([
                        ...$this->dateRangeFilters($from, $to),
                        'budget_title' => $budgetTitle !== '' ? $budgetTitle : null,
                    ], fn ($v) => $v !== null),
                ],
                'last_entity_type' => 'budget',
                'last_entity_id' => (int) ($ids[0] ?? 0) ?: null,
                'last_date_range' => $this->dateRangeForContext($from, $to),
            ],
        ];
    }

    /**
     * @param  Collection<int, Budget>  $budgets
     * @return array<int, float>
     */
    private function spentByBudget(int $userId, Collection $budgets, ?Carbon $from, ?Carbon $to): array
    {
        $budgetIds = $budgets->pluck('id')->all();

        $query = Transaction::query()
            ->where('user_id', $userId)
            ->whereIn('budget_id', $budgetIds)
            ->whereIn('type', ['expense', 'income']);

        if ($from || $to) {
            $this->applyDateRange($query, $from, $to);
        } else {
            $minStart = $budgets->min('period_start');
            $maxEnd = $budgets->max('period_end');
            if ($minStart && $maxEnd) {
                $this->applyDateRange($query, Carbon::parse($minStart)->startOfDay(), Carbon::parse($maxEnd)->endOfDay());
            }
        }

        $rows = $query
            ->selectRaw('budget_id, type, SUM(amount) as total_amount')
            ->groupBy('budget_id')
            ->groupBy('type')
            ->get();

        $map = [];
        foreach ($budgetIds as $budgetId) {
            $map[(int) $budgetId] = ['expense' => 0.0, 'income' => 0.0];
        }

        foreach ($rows as $row) {
            $budgetId = (int) ($row->budget_id ?? 0);
            $type = (string) ($row->type ?? '');
            if (! isset($map[$budgetId]) || ! in_array($type, ['expense', 'income'], true)) {
                continue;
            }

            $map[$budgetId][$type] = (float) ($row->total_amount ?? 0);
        }

        return $map;
    }

    private function applyDateRange($query, ?Carbon $from, ?Carbon $to): void
    {
        if ($from) {
            $query->whereDate('transaction_date', '>=', $from->toDateString());
        }
        if ($to) {
            $query->whereDate('transaction_date', '<=', $to->toDateString());
        }
    }

    private function describeDateRange(?Carbon $from, ?Carbon $to): string
    {
        if ($from && $to) {
            return ' (' . $from->toDateString() . ' to ' . $to->toDateString() . ')';
        }
        if ($from && ! $to) {
            return ' (from ' . $from->toDateString() . ')';
        }
        if (! $from && $to) {
            return ' (until ' . $to->toDateString() . ')';
        }

        return '';
    }

    private function defaultClarify(): string
    {
        return 'Do you mean budgets, transactions, or categories? If you want totals, please include a date range (e.g., "this week", "last month", or "2026-04-01 to 2026-04-26").';
    }

    private function needDateRange(string $topic): string
    {
        return "What date range should I use for {$topic}? (e.g., \"this week\", \"last month\", or \"2026-04-01 to 2026-04-26\")";
    }

    /**
     * @param  array<string, mixed>  $contextState
     */
    private function tryImplicitFollowupReply(int $userId, string $userMessage, array $contextState): ?array
    {
        $lower = strtolower(trim($userMessage));

        $looksLikeAmountQuestion = Str::contains($lower, ['how much', 'amount', 'how much was it', 'how much is it']);

        if ($looksLikeAmountQuestion && ($contextState['last_entity_type'] ?? null) === 'transaction' && ($contextState['last_entity_id'] ?? null)) {
            $transaction = Transaction::query()
                ->where('user_id', $userId)
                ->where('id', (int) $contextState['last_entity_id'])
                ->with('category')
                ->first();

            if ($transaction) {
                $amount = number_format((float) $transaction->amount, 2);
                $date = Carbon::parse($transaction->transaction_date)->toDateString();
                $category = $transaction->category?->name ?? 'Unknown Category';

                return [
                    'reply' => "That transaction is {$amount} ({$category}) on {$date}: \"{$transaction->title}\".",
                    'context_update' => [],
                ];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $contextState
     */
    private function fallbackDateRangeFromContext(string $intent, array $contextState): ?array
    {
        $wantsDateRange = in_array($intent, [
            'sum_expenses',
            'sum_income',
            'top_spending_categories',
            'max_expense',
            'filter_transactions',
            'budget_status',
        ], true);

        if (! $wantsDateRange) {
            return null;
        }

        $range = $contextState['last_date_range'] ?? null;

        return is_array($range) ? $range : null;
    }

    private function dateRangeForContext(?Carbon $from, ?Carbon $to): ?array
    {
        if (! $from && ! $to) {
            return null;
        }

        return [
            'date_from' => $from?->toDateString(),
            'date_to' => $to?->toDateString(),
        ];
    }

    private function dateRangeFilters(?Carbon $from, ?Carbon $to): array
    {
        return array_filter([
            'date_from' => $from?->toDateString(),
            'date_to' => $to?->toDateString(),
        ], fn ($v) => $v !== null);
    }

    /**
     * @param  Collection<int, Transaction>  $transactions
     */
    private function formatTransactionsReply(Collection $transactions): string
    {
        $lines = $transactions->values()->map(function (Transaction $t, int $index): string {
            $i = $index + 1;
            $date = Carbon::parse($t->transaction_date)->toDateString();
            $amount = $this->formatMoney((float) $t->amount);
            $category = $t->category?->name ?? 'Unknown Category';
            $budget = $t->budget?->title ?? 'No Budget';

            return "{$i}) #{$t->id} — {$date} — {$t->type}\n"
                . "   Title: {$t->title}\n"
                . "   Amount: {$amount}\n"
                . "   Category: {$category}\n"
                . "   Budget: {$budget}";
        })->all();

        return "Here are the transactions I found:\n\n" . implode("\n\n", $lines);
    }

    private function formatMoney(float $amount): string
    {
        return '₱' . number_format($amount, 2);
    }
}
