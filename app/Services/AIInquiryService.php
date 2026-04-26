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
        $intentPayload = $this->classifyIntent($userMessage, $history);

        $intent = (string) ($intentPayload['intent'] ?? 'clarify');
        $filters = is_array($intentPayload['filters'] ?? null) ? $intentPayload['filters'] : [];

        $resolved = $this->resolveFilters($userMessage, $filters);

        if ($intent === 'clarify') {
            $clarify = (string) ($intentPayload['clarify_question'] ?? '');

            return $clarify !== '' ? $clarify : $this->defaultClarify();
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
            default => $this->defaultClarify(),
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

        $result = $this->aiService->chat($messages);
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
        $slice = array_slice($withoutSystem, -max);

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
    private function resolveFilters(string $userMessage, array $filters): array
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

    private function listCategories(int $userId): string
    {
        $categories = Category::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get(['name', 'type']);

        if ($categories->isEmpty()) {
            return "You don't have any categories yet.";
        }

        $lines = $categories->take(15)->map(fn (Category $c) => "- {$c->name} ({$c->type})")->all();

        $extra = $categories->count() > 15 ? "\n(Showing 15 of {$categories->count()} categories.)" : '';

        return "Here are your categories:\n" . implode("\n", $lines) . $extra;
    }

    private function countCategories(int $userId): string
    {
        $counts = Category::query()
            ->where('user_id', $userId)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        $total = (int) $counts->sum();

        if ($total === 0) {
            return "You don't have any categories yet.";
        }

        $expense = (int) ($counts['expense'] ?? 0);
        $income = (int) ($counts['income'] ?? 0);
        $both = (int) ($counts['both'] ?? 0);

        return "You have {$total} categories total (expense: {$expense}, income: {$income}, both: {$both}).";
    }

    private function listBudgets(int $userId, int $limit): string
    {
        $budgets = Budget::query()
            ->where('user_id', $userId)
            ->with('category')
            ->orderByDesc('period_start')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($budgets->isEmpty()) {
            return "You don't have any budgets yet.";
        }

        $lines = $budgets->map(function (Budget $b): string {
            $category = $b->category?->name ?? 'Uncategorized';
            $start = Carbon::parse($b->period_start)->toDateString();
            $end = Carbon::parse($b->period_end)->toDateString();
            $allocated = number_format((float) $b->allocated_amount, 2);

            return "- {$b->title} ({$category}) | Allocated: {$allocated} | {$start} to {$end} | Status: {$b->status}";
        })->all();

        return "Here are your latest budgets:\n" . implode("\n", $lines);
    }

    private function listTransactions(int $userId, ?Carbon $from, ?Carbon $to, int $limit): string
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
            return 'No transactions found for that query.';
        }

        return $this->formatTransactionsReply($transactions);
    }

    private function sumByType(int $userId, string $type, ?Carbon $from, ?Carbon $to, string $categoryName): string
    {
        if (! $from && ! $to) {
            return $this->needDateRange($type === 'expense' ? 'expenses' : 'income');
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
                return "I couldn't find a category matching \"{$categoryName}\". Try asking: \"List my categories\".";
            }

            $query->where('category_id', $category->id);
        }

        $sum = (float) $query->sum('amount');
        $formatted = number_format($sum, 2);

        $range = $this->describeDateRange($from, $to);
        if ($type === 'expense') {
            $suffix = $category ? " in category \"{$category->name}\"" : '';

            return "Your total expenses{$suffix}{$range}: {$formatted}.";
        }

        return "Your total income{$range}: {$formatted}.";
    }

    private function topSpendingCategories(int $userId, ?Carbon $from, ?Carbon $to, int $limit): string
    {
        if (! $from && ! $to) {
            return $this->needDateRange('top spending categories');
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
            return 'No expense transactions found for that date range.';
        }

        $range = $this->describeDateRange($from, $to);
        $lines = $rows->map(function ($row): string {
            $name = (string) $row->category_name;
            $total = number_format((float) $row->total_spent, 2);

            return "- {$name}: {$total}";
        })->all();

        return "Top spending categories{$range}:\n" . implode("\n", $lines);
    }

    private function maxExpense(int $userId, ?Carbon $from, ?Carbon $to): string
    {
        if (! $from && ! $to) {
            return $this->needDateRange('maximum expense');
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
            return 'No expense transactions found for that date range.';
        }

        $range = $this->describeDateRange($from, $to);
        $date = Carbon::parse($transaction->transaction_date)->toDateString();
        $amount = number_format((float) $transaction->amount, 2);
        $category = $transaction->category?->name ?? 'Unknown Category';

        return "Your maximum expense{$range} is {$amount} for \"{$transaction->title}\" ({$category}) on {$date}.";
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filterTransactions(int $userId, array $filters): string
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
                return "I couldn't find a category matching \"{$categoryName}\". Try asking: \"List my categories\".";
            }

            $query->where('category_id', $category->id);
        }

        $transactions = $query
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        if ($transactions->isEmpty()) {
            return 'No transactions found for that query.';
        }

        return $this->formatTransactionsReply($transactions);
    }

    private function budgetStatus(int $userId, string $budgetTitle, ?Carbon $from, ?Carbon $to, int $limit): string
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
            return $budgetTitle !== ''
                ? "I couldn't find a budget matching \"{$budgetTitle}\"."
                : "You don't have any budgets yet.";
        }

        $spentByBudget = $this->spentByBudget($userId, $budgets, $from, $to);

        $lines = $budgets->map(function (Budget $b) use ($spentByBudget, $from, $to): string {
            $spent = (float) ($spentByBudget[$b->id] ?? 0);
            $allocated = (float) $b->allocated_amount;
            $remaining = $allocated - $spent;
            $category = $b->category?->name ?? 'Uncategorized';

            $spentText = number_format($spent, 2);
            $allocatedText = number_format($allocated, 2);
            $remainingText = number_format($remaining, 2);

            $range = ($from || $to) ? $this->describeDateRange($from, $to) : ' (budget period)';

            $status = $spent > $allocated ? 'EXCEEDED' : 'OK';

            return "- {$b->title} ({$category}){$range}: Spent {$spentText} / {$allocatedText} (Remaining {$remainingText}) → {$status}";
        })->all();

        return "Budget status:\n" . implode("\n", $lines);
    }

    /**
     * @param  Collection<int, Budget>  $budgets
     * @return array<int, float>
     */
    private function spentByBudget(int $userId, Collection $budgets, ?Carbon $from, ?Carbon $to): array
    {
        if ($from || $to) {
            $budgetIds = $budgets->pluck('id')->all();

            $query = Transaction::query()
                ->where('user_id', $userId)
                ->where('type', 'expense')
                ->whereIn('budget_id', $budgetIds);

            $this->applyDateRange($query, $from, $to);

            $rows = $query
                ->selectRaw('budget_id, SUM(amount) as total_spent')
                ->groupBy('budget_id')
                ->get();

            $map = [];
            foreach ($rows as $row) {
                $budgetId = (int) ($row->budget_id ?? 0);
                $map[$budgetId] = (float) ($row->total_spent ?? 0);
            }

            return $map;
        }

        $map = [];
        foreach ($budgets as $budget) {
            $start = Carbon::parse($budget->period_start)->startOfDay();
            $end = Carbon::parse($budget->period_end)->endOfDay();

            $map[$budget->id] = (float) Transaction::query()
                ->where('user_id', $userId)
                ->where('type', 'expense')
                ->where('budget_id', $budget->id)
                ->whereDate('transaction_date', '>=', $start->toDateString())
                ->whereDate('transaction_date', '<=', $end->toDateString())
                ->sum('amount');
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
     * @param  Collection<int, Transaction>  $transactions
     */
    private function formatTransactionsReply(Collection $transactions): string
    {
        $lines = $transactions->map(function (Transaction $t): string {
            $date = Carbon::parse($t->transaction_date)->toDateString();
            $amount = number_format((float) $t->amount, 2);
            $category = $t->category?->name ?? 'Unknown Category';
            $budget = $t->budget?->title ?? 'No Budget';

            return "- [{$date}] {$t->type}: {$t->title} | {$amount} | {$category} | {$budget}";
        })->all();

        return "Here are the transactions I found:\n" . implode("\n", $lines);
    }
}
