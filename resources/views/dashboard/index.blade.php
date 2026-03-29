@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="section-title">Dashboard</h1>
                <p class="section-subtitle">Welcome back. Here is your latest finance overview.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn-primary" data-open-dashboard-transaction-modal>New Transaction</button>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="app-card p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Allocated</p>
                <p class="mt-2 text-xl font-semibold text-slate-900">PHP {{ number_format($totalAllocatedBudget, 2) }}</p>
            </div>
            <div class="app-card p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Expenses</p>
                <p class="mt-2 text-xl font-semibold text-amber-700">PHP {{ number_format($totalExpenses, 2) }}</p>
            </div>
            <div class="app-card glow-card p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Income</p>
                <p class="mt-2 text-xl font-semibold text-emerald-700">PHP {{ number_format($totalIncome, 2) }}</p>
            </div>
            <div class="app-card glow-card p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Remaining Balance</p>
                <p class="mt-2 text-xl font-semibold {{ $remainingBalance >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                    PHP {{ number_format($remainingBalance, 2) }}
                </p>
            </div>
            <div class="app-card p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Active Budgets</p>
                <p class="mt-2 text-xl font-semibold text-slate-900">{{ $activeBudgetsCount }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="app-card p-5">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Recent Transactions</h2>

                @if ($recentTransactions->isEmpty())
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-6 text-center">
                        <h3 class="text-sm font-semibold text-emerald-900">No transactions recorded yet</h3>
                        <p class="mt-1 text-sm text-emerald-800">Add your first transaction to start tracking daily spending and income.</p>
                        <div class="mt-4">
                            <button type="button" class="btn-primary inline-flex" data-open-dashboard-transaction-modal>Create Transaction</button>
                        </div>
                    </div>
                @else
                    <div class="table-shell">
                        <table class="table-base">
                            <thead class="table-head">
                                <tr>
                                    <th class="table-th">Title</th>
                                    <th class="table-th">Category</th>
                                    <th class="table-th">Budget</th>
                                    <th class="table-th">Type</th>
                                    <th class="table-th">Amount</th>
                                    <th class="table-th">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($recentTransactions as $transaction)
                                    <tr>
                                        <td class="table-td font-medium text-slate-800">{{ $transaction->title }}</td>
                                        <td class="table-td text-slate-600">{{ $transaction->category?->name ?? '-' }}</td>
                                        <td class="table-td text-slate-600">{{ $transaction->budget?->title ?? '-' }}</td>
                                        <td class="table-td">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs {{ $transaction->type === 'income' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </td>
                                        <td class="table-td">PHP {{ number_format((float) $transaction->amount, 2) }}</td>
                                        <td class="table-td text-slate-600">{{ $transaction->transaction_date?->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="app-card p-5">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Active Budgets</h2>

                @if ($activeBudgets->isEmpty())
                    @include('partials.empty-state', [
                        'title' => 'No budgets created yet',
                        'message' => 'Set up your first budget to monitor spending limits and goals.',
                        'actionLabel' => 'Create Budget',
                        'actionHref' => route('budgets.create'),
                    ])
                @else
                    <div class="table-shell">
                        <table class="table-base">
                            <thead class="table-head">
                                <tr>
                                    <th class="table-th">Title</th>
                                    <th class="table-th">Category</th>
                                    <th class="table-th">Allocated</th>
                                    <th class="table-th">Status</th>
                                    <th class="table-th">Period</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($activeBudgets as $budget)
                                    <tr>
                                        <td class="table-td font-medium text-slate-800">{{ $budget->title }}</td>
                                        <td class="table-td text-slate-600">{{ $budget->category?->name ?? '-' }}</td>
                                        <td class="table-td">PHP {{ number_format((float) $budget->allocated_amount, 2) }}</td>
                                        <td class="table-td">
                                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                                                {{ ucfirst($budget->status) }}
                                            </span>
                                        </td>
                                        <td class="table-td text-slate-600">
                                            {{ $budget->period_start?->format('M d, Y') }} - {{ $budget->period_end?->format('M d, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden items-center justify-center p-4" data-dashboard-transaction-modal>
        <div class="absolute inset-0 bg-slate-900/45" data-close-dashboard-transaction-modal></div>
        <div class="relative flex w-full max-w-2xl max-h-[90vh] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Create Transaction</h2>
                    <p class="text-sm text-slate-500">Add a new income or expense record.</p>
                </div>
                <button type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                    data-close-dashboard-transaction-modal aria-label="Close create transaction modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="post" action="{{ route('transactions.store') }}" enctype="multipart/form-data"
                class="flex-1 space-y-5 overflow-y-auto px-5 py-4" data-dashboard-transaction-form>
                @csrf
                @include('transactions._form')
                <div class="sticky bottom-0 flex items-center justify-end gap-2 border-t border-slate-200 bg-white pt-3">
                    <button type="button" class="btn-secondary" data-close-dashboard-transaction-modal>Cancel</button>
                    <button type="submit" class="btn-primary">Save Transaction</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.querySelector('[data-dashboard-transaction-modal]');
            if (!modal) return;

            const openButtons = document.querySelectorAll('[data-open-dashboard-transaction-modal]');
            const closeButtons = modal.querySelectorAll('[data-close-dashboard-transaction-modal]');
            const form = modal.querySelector('[data-dashboard-transaction-form]');

            const resetTransactionForm = () => {
                if (!form) return;

                const setValue = (name, value = '') => {
                    const el = form.querySelector(`[name="${name}"]`);
                    if (el) {
                        el.value = value;
                    }
                };

                setValue('category_id', '');
                setValue('budget_id', '');
                setValue('title', '');
                setValue('amount', '');
                setValue('type', 'income');
                setValue('transaction_date', '');
                setValue('payment_method', '');
                setValue('notes', '');
                setValue('attachment', '');

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

            @if ($errors->any() && (old('title') !== null || old('amount') !== null || old('transaction_date') !== null || old('category_id') !== null || old('budget_id') !== null))
                openModal(false);
            @endif
        })();
    </script>
@endsection
