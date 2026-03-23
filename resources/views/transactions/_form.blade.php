@php
    $transaction = $transaction ?? null;
@endphp

<div class="space-y-5">
    <div>
        <label for="budget_id" class="mb-1 block text-sm font-medium text-slate-700">Budget</label>
        <select
            id="budget_id"
            name="budget_id"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
            <option value="">Select a budget</option>
            @foreach ($budgets as $budget)
                <option value="{{ $budget->id }}" @selected((string) old('budget_id', $transaction?->budget_id) === (string) $budget->id)>
                    {{ $budget->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="category_id" class="mb-1 block text-sm font-medium text-slate-700">Category</label>
        <select
            id="category_id"
            name="category_id"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
            <option value="">Select a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $transaction?->category_id) === (string) $category->id)>
                    {{ $category->name }} ({{ ucfirst($category->type) }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="title" class="mb-1 block text-sm font-medium text-slate-700">Title</label>
        <input
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $transaction?->title) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
    </div>

    <div>
        <label for="amount" class="mb-1 block text-sm font-medium text-slate-700">Amount</label>
        <input
            id="amount"
            name="amount"
            type="number"
            step="0.01"
            min="0"
            value="{{ old('amount', $transaction?->amount) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
    </div>

    <div>
        <label for="type" class="mb-1 block text-sm font-medium text-slate-700">Type</label>
        <select
            id="type"
            name="type"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
            @foreach (['income' => 'Income', 'expense' => 'Expense'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $transaction?->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="transaction_date" class="mb-1 block text-sm font-medium text-slate-700">Transaction Date</label>
        <input
            id="transaction_date"
            name="transaction_date"
            type="date"
            value="{{ old('transaction_date', optional($transaction?->transaction_date)->format('Y-m-d')) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
    </div>

    <div>
        <label for="payment_method" class="mb-1 block text-sm font-medium text-slate-700">Payment Method</label>
        <input
            id="payment_method"
            name="payment_method"
            type="text"
            value="{{ old('payment_method', $transaction?->payment_method) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
        >
    </div>

    <div>
        <label for="notes" class="mb-1 block text-sm font-medium text-slate-700">Notes</label>
        <textarea
            id="notes"
            name="notes"
            rows="4"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
        >{{ old('notes', $transaction?->notes) }}</textarea>
    </div>

    <div>
        <label for="attachment_path" class="mb-1 block text-sm font-medium text-slate-700">Attachment Path (Optional)</label>
        <input
            id="attachment_path"
            name="attachment_path"
            type="text"
            value="{{ old('attachment_path', $transaction?->attachment_path) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
        >
    </div>
</div>

