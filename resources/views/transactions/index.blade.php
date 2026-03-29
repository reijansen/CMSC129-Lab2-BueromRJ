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
                <button type="button" class="btn-primary" data-open-transaction-modal>
                    New Transaction
                </button>
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
                                    <a href="{{ route('transactions.show', $transaction) }}" class="btn-secondary px-3 py-1.5 text-xs">
                                        View
                                    </a>
                                    <a href="{{ route('transactions.edit', $transaction) }}" class="btn-secondary px-3 py-1.5 text-xs">
                                        Edit
                                    </a>
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
                                <button type="button" data-open-transaction-modal class="mt-3 inline-flex btn-primary px-3 py-1.5 text-xs">Create Transaction</button>
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

    <div class="fixed inset-0 z-50 hidden items-center justify-center p-4" data-transaction-modal>
        <div class="absolute inset-0 bg-slate-900/45" data-close-transaction-modal></div>
        <div class="relative flex w-full max-w-2xl max-h-[90vh] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Create Transaction</h2>
                    <p class="text-sm text-slate-500">Add a new income or expense record.</p>
                </div>
                <button type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                    data-close-transaction-modal aria-label="Close create transaction modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="post" action="{{ route('transactions.store') }}" enctype="multipart/form-data"
                class="flex-1 space-y-5 overflow-y-auto px-5 py-4" data-transaction-create-form>
                @csrf
                @include('transactions._form')
                <div class="sticky bottom-0 flex items-center justify-end gap-2 border-t border-slate-200 bg-white pt-3">
                    <button type="button" class="btn-secondary" data-close-transaction-modal>Cancel</button>
                    <button type="submit" class="btn-primary">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.querySelector('[data-transaction-modal]');
            if (!modal) return;

            const openButtons = document.querySelectorAll('[data-open-transaction-modal]');
            const closeButtons = modal.querySelectorAll('[data-close-transaction-modal]');
            const form = modal.querySelector('[data-transaction-create-form]');
            const params = new URLSearchParams(window.location.search);

            const resetTransactionForm = () => {
                if (!form) return;
                form.reset();
                const categorySelect = form.querySelector('[data-transaction-category]');
                categorySelect?.dispatchEvent(new Event('change'));
            };

            const openModal = (reset = false) => {
                if (reset) {
                    resetTransactionForm();
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', () => openModal(true));
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });

            if (params.get('open') === 'create') {
                openModal(true);
            }

            @if ($errors->any() && (old('title') !== null || old('amount') !== null || old('transaction_date') !== null || old('category_id') !== null || old('budget_id') !== null))
                openModal(false);
            @endif
        })();
    </script>
@endsection


