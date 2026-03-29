@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    <div class="app-card">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Transactions</h1>
                <p class="text-sm text-slate-500">Track all income and expense records.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('transactions.trash') }}" class="btn-secondary">
                    View Trash
                </a>
                <a href="{{ route('transactions.create') }}" class="btn-primary">New Transaction</a>
            </div>
        </div>

        <form method="GET" action="{{ route('transactions.index') }}" class="mb-6 rounded-lg border border-emerald-100 bg-emerald-50/40 p-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Title or notes"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                </div>

                <div>
                    <label for="type_filter" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Type</label>
                    <select
                        id="type_filter"
                        name="type"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                        <option value="">All types</option>
                        <option value="income" @selected(($filters['type'] ?? '') === 'income')>Income</option>
                        <option value="expense" @selected(($filters['type'] ?? '') === 'expense')>Expense</option>
                    </select>
                </div>

                <div>
                    <label for="category_id" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Category</label>
                    <select
                        id="category_id"
                        name="category_id"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="payment_method" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Payment Method</label>
                    <input
                        id="payment_method"
                        name="payment_method"
                        type="text"
                        value="{{ $filters['payment_method'] ?? '' }}"
                        placeholder="Cash, Card, GCash..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                </div>

                <div>
                    <label for="date_from" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Date From</label>
                    <input
                        id="date_from"
                        name="date_from"
                        type="date"
                        value="{{ $filters['date_from'] ?? '' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                </div>

                <div>
                    <label for="date_to" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Date To</label>
                    <input
                        id="date_to"
                        name="date_to"
                        type="date"
                        value="{{ $filters['date_to'] ?? '' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                </div>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <button type="submit" class="btn-primary">
                    Apply Filters
                </button>
                <a href="{{ route('transactions.index') }}" class="btn-secondary">
                    Clear
                </a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Title</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Budget</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Category</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Type</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Amount</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Date</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Payment</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Attachment</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $transaction->title }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->budget?->title ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->category?->name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($transaction->type === 'income')
                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        Income
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                                        Expense
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">PHP {{ number_format((float) $transaction->amount, 2) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->transaction_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->payment_method ?: '-' }}</td>
                            <td class="px-4 py-3">
                                @if ($transaction->attachment_path)
                                    <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        Attached
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">None</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" class="btn-secondary px-3 py-1.5 text-xs" data-open-view-modal="{{ $transaction->id }}">
                                        View
                                    </button>
                                    <button type="button" class="btn-secondary px-3 py-1.5 text-xs" data-open-edit-modal="{{ $transaction->id }}">
                                        Edit
                                    </button>
                                    <form method="post" action="{{ route('transactions.destroy', $transaction) }}" data-confirm-message="Delete this transaction?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-red-300 px-3 py-1.5 text-red-700 hover:bg-red-50">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center">
                                <p class="text-sm font-medium text-slate-700">No transactions recorded yet.</p>
                                <p class="mt-1 text-xs text-slate-500">Add your first income or expense to populate this list.</p>
                                <a href="{{ route('transactions.create') }}" class="mt-3 inline-flex btn-primary px-3 py-1.5 text-xs">Create Transaction</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>

    @foreach ($transactions as $transaction)
        @php
            $isEditingOld = (string) old('edit_transaction_id') === (string) $transaction->id;
            $selectedCategoryId = $isEditingOld ? old('category_id', $transaction->category_id) : $transaction->category_id;
            $selectedBudgetId = $isEditingOld ? old('budget_id', $transaction->budget_id) : $transaction->budget_id;
        @endphp

        <div class="fixed inset-0 z-50 hidden items-center justify-center p-4" data-view-modal="{{ $transaction->id }}">
            <div class="absolute inset-0 bg-slate-900/45" data-close-modal></div>
            <div class="relative w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">Transaction Details</h2>
                    <button type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        data-close-modal aria-label="Close transaction details modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="grid gap-4 px-5 py-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Title</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $transaction->title }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Amount</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">PHP {{ number_format((float) $transaction->amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Type</p>
                        <p class="mt-1 text-sm text-slate-700">{{ ucfirst($transaction->type) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Date</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $transaction->transaction_date?->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Category</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $transaction->category?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Budget</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $transaction->budget?->title ?? '-' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Payment Method</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $transaction->payment_method ?: '-' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $transaction->notes ?: '-' }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Attachment</p>
                        @if ($transaction->attachment_path)
                            <a href="{{ asset('storage/' . $transaction->attachment_path) }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex text-sm font-medium text-emerald-700 hover:text-emerald-800 hover:underline">
                                View attachment
                            </a>
                        @else
                            <p class="mt-1 text-sm text-slate-700">None</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-end border-t border-slate-200 px-5 py-3">
                    <button type="button" class="btn-secondary" data-close-modal>Close</button>
                </div>
            </div>
        </div>

        <div class="fixed inset-0 z-50 hidden items-center justify-center p-4" data-edit-modal="{{ $transaction->id }}">
            <div class="absolute inset-0 bg-slate-900/45" data-close-modal></div>
            <div class="relative flex w-full max-w-2xl max-h-[90vh] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Edit Transaction</h2>
                        <p class="text-sm text-slate-500">Update this income or expense record.</p>
                    </div>
                    <button type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        data-close-modal aria-label="Close edit transaction modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="post" action="{{ route('transactions.update', $transaction) }}" enctype="multipart/form-data"
                    class="flex-1 space-y-5 overflow-y-auto px-5 py-4" data-edit-transaction-form>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="edit_transaction_id" value="{{ $transaction->id }}">

                    <div class="space-y-5">
                        <div>
                            <label for="category_id_{{ $transaction->id }}" class="label-control">Category</label>
                            <select
                                id="category_id_{{ $transaction->id }}"
                                name="category_id"
                                class="input-control"
                                required
                                data-transaction-category
                            >
                                <option value="">Select a category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        data-category-type="{{ $category->type }}"
                                        @selected((string) $selectedCategoryId === (string) $category->id)>
                                        {{ $category->name }} ({{ ucfirst($category->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="hidden" data-budget-wrapper>
                            <label for="budget_id_{{ $transaction->id }}" class="label-control">Budget <span class="text-xs font-normal text-slate-500">(required for expense)</span></label>
                            <select
                                id="budget_id_{{ $transaction->id }}"
                                name="budget_id"
                                class="input-control"
                                data-transaction-budget
                            >
                                <option value="">Select a budget</option>
                                @foreach ($budgets as $budget)
                                    <option value="{{ $budget->id }}" @selected((string) $selectedBudgetId === (string) $budget->id)>
                                        {{ $budget->title }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">
                                Budget is required for expense transactions.
                            </p>
                        </div>

                        <div>
                            <label for="title_{{ $transaction->id }}" class="label-control">Title</label>
                            <input
                                id="title_{{ $transaction->id }}"
                                name="title"
                                type="text"
                                value="{{ $isEditingOld ? old('title', $transaction->title) : $transaction->title }}"
                                class="input-control"
                                required
                            >
                        </div>

                        <div>
                            <label for="amount_{{ $transaction->id }}" class="label-control">Amount</label>
                            <input
                                id="amount_{{ $transaction->id }}"
                                name="amount"
                                type="number"
                                step="0.01"
                                min="0"
                                value="{{ $isEditingOld ? old('amount', $transaction->amount) : $transaction->amount }}"
                                class="input-control"
                                required
                            >
                        </div>

                        <div>
                            <label for="type_{{ $transaction->id }}" class="label-control">Type</label>
                            <select
                                id="type_{{ $transaction->id }}"
                                name="type"
                                class="input-control"
                                required
                            >
                                @php
                                    $selectedType = $isEditingOld ? old('type', $transaction->type) : $transaction->type;
                                @endphp
                                <option value="income" @selected($selectedType === 'income')>Income</option>
                                <option value="expense" @selected($selectedType === 'expense')>Expense</option>
                            </select>
                        </div>

                        <div>
                            <label for="transaction_date_{{ $transaction->id }}" class="label-control">Transaction Date</label>
                            <input
                                id="transaction_date_{{ $transaction->id }}"
                                name="transaction_date"
                                type="date"
                                value="{{ $isEditingOld ? old('transaction_date', optional($transaction->transaction_date)->format('Y-m-d')) : optional($transaction->transaction_date)->format('Y-m-d') }}"
                                class="input-control"
                                required
                            >
                        </div>

                        <div>
                            <label for="payment_method_{{ $transaction->id }}" class="label-control">Payment Method</label>
                            <input
                                id="payment_method_{{ $transaction->id }}"
                                name="payment_method"
                                type="text"
                                value="{{ $isEditingOld ? old('payment_method', $transaction->payment_method) : $transaction->payment_method }}"
                                class="input-control"
                            >
                        </div>

                        <div>
                            <label for="notes_{{ $transaction->id }}" class="label-control">Notes</label>
                            <textarea
                                id="notes_{{ $transaction->id }}"
                                name="notes"
                                rows="4"
                                class="input-control"
                            >{{ $isEditingOld ? old('notes', $transaction->notes) : $transaction->notes }}</textarea>
                        </div>

                        <div>
                            <label for="attachment_{{ $transaction->id }}" class="label-control">Attachment (Optional)</label>
                            <input
                                id="attachment_{{ $transaction->id }}"
                                name="attachment"
                                type="file"
                                accept=".jpg,.jpeg,.png,.pdf"
                                class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm file:mr-3 file:rounded-md file:border-0 file:bg-emerald-600 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-emerald-700 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                            <p class="mt-1 text-xs text-slate-500">Allowed: JPG, JPEG, PNG, PDF. Max size: 4 MB.</p>
                            @if ($transaction->attachment_path)
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

                    <div class="sticky bottom-0 flex items-center justify-end gap-2 border-t border-slate-200 bg-white pt-3">
                        <button type="button" class="btn-secondary" data-close-modal>Cancel</button>
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    <script>
        (() => {
            const openModal = (modal) => {
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = (modal) => {
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            document.querySelectorAll('[data-open-view-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-open-view-modal');
                    openModal(document.querySelector(`[data-view-modal="${id}"]`));
                });
            });

            document.querySelectorAll('[data-open-edit-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-open-edit-modal');
                    openModal(document.querySelector(`[data-edit-modal="${id}"]`));
                });
            });

            document.querySelectorAll('[data-close-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const modal = button.closest('[data-view-modal], [data-edit-modal]');
                    closeModal(modal);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                const openModalElement = document.querySelector('[data-view-modal].flex, [data-edit-modal].flex');
                closeModal(openModalElement);
            });

            document.querySelectorAll('[data-edit-transaction-form]').forEach((form) => {
                const categorySelect = form.querySelector('[data-transaction-category]');
                const budgetWrapper = form.querySelector('[data-budget-wrapper]');
                const budgetSelect = form.querySelector('[data-transaction-budget]');
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
            });

            @if ($errors->any() && old('edit_transaction_id'))
                openModal(document.querySelector('[data-edit-modal="{{ old('edit_transaction_id') }}"]'));
            @endif
        })();
    </script>
@endsection


