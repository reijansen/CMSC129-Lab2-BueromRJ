@php
    $transaction = $transaction ?? null;
@endphp

<div class="space-y-5">
    <div>
        <label for="category_id" class="label-control">Category</label>
        <select
            id="category_id"
            name="category_id"
            class="input-control"
            required
            data-transaction-category
        >
            <option value="">Select a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    data-category-type="{{ $category->type }}"
                    @selected((string) old('category_id', $transaction?->category_id) === (string) $category->id)>
                    {{ $category->name }} ({{ ucfirst($category->type) }})
                </option>
            @endforeach
        </select>
    </div>

    <div class="hidden" data-budget-wrapper>
        <label for="budget_id" class="label-control">Budget <span class="text-xs font-normal text-slate-500">(required for expense)</span></label>
        <select
            id="budget_id"
            name="budget_id"
            class="input-control"
            data-transaction-budget
        >
            <option value="">Select a budget</option>
            @foreach ($budgets as $budget)
                <option value="{{ $budget->id }}" @selected((string) old('budget_id', $transaction?->budget_id) === (string) $budget->id)>
                    {{ $budget->title }}
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-slate-500" data-budget-hint>
            Budget is required for expense transactions.
        </p>
    </div>

    <div>
        <label for="title" class="label-control">Title</label>
        <input
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $transaction?->title) }}"
            class="input-control"
            required
        >
    </div>

    <div>
        <label for="amount" class="label-control">Amount</label>
        <input
            id="amount"
            name="amount"
            type="number"
            step="0.01"
            min="0"
            value="{{ old('amount', $transaction?->amount) }}"
            class="input-control"
            required
        >
    </div>

    <div>
        <label for="type" class="label-control">Type</label>
        <select
            id="type"
            name="type"
            class="input-control"
            required
            data-transaction-type
        >
            @foreach (['income' => 'Income', 'expense' => 'Expense'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $transaction?->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="transaction_date" class="label-control">Transaction Date</label>
        <input
            id="transaction_date"
            name="transaction_date"
            type="date"
            value="{{ old('transaction_date', optional($transaction?->transaction_date)->format('Y-m-d')) }}"
            class="input-control"
            required
        >
    </div>

    <div>
        <label for="payment_method" class="label-control">Payment Method</label>
        <input
            id="payment_method"
            name="payment_method"
            type="text"
            value="{{ old('payment_method', $transaction?->payment_method) }}"
            class="input-control"
        >
    </div>

    <div>
        <label for="notes" class="label-control">Notes</label>
        <textarea
            id="notes"
            name="notes"
            rows="4"
            class="input-control"
        >{{ old('notes', $transaction?->notes) }}</textarea>
    </div>

    <div>
        <label for="attachment" class="label-control">Attachment (Optional)</label>
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

<script>
    (() => {
        const categorySelect = document.querySelector('[data-transaction-category]');
        const budgetWrapper = document.querySelector('[data-budget-wrapper]');
        const budgetSelect = document.querySelector('[data-transaction-budget]');
        if (!categorySelect || !budgetWrapper || !budgetSelect) return;

        const syncBudgetRequirement = () => {
            const selected = categorySelect.options[categorySelect.selectedIndex];
            const categoryType = selected?.dataset?.categoryType || '';
            const needsBudget = categoryType === 'expense' || categoryType === 'both';

            budgetWrapper.classList.toggle('hidden', !needsBudget);
            budgetSelect.required = needsBudget;
        };

        categorySelect.addEventListener('change', syncBudgetRequirement);
        syncBudgetRequirement();
    })();
</script>

