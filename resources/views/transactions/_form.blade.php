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
        <label for="attachment" class="mb-1 block text-sm font-medium text-slate-700">Attachment (Optional)</label>
        <input
            id="attachment"
            name="attachment"
            type="file"
            accept=".jpg,.jpeg,.png,.pdf"
            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-emerald-600 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-emerald-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
        >
        <p class="mt-1 text-xs text-slate-500">Allowed: JPG, JPEG, PNG, PDF. Max size: 4 MB.</p>
        @error('attachment')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror

        @if ($transaction?->attachment_path)
            <div class="mt-2">
                <p class="text-xs text-slate-500">Current attachment:</p>
                <a
                    href="{{ asset('storage/' . $transaction->attachment_path) }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm font-medium text-emerald-700 hover:text-emerald-800 hover:underline"
                >
                    View current file
                </a>
            </div>
        @endif
    </div>
</div>
