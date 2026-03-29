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
                <a href="{{ route('transactions.index', ['open' => 'create', 'from' => 'dashboard']) }}" class="btn-primary">New Transaction</a>
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
            <div class="app-card p-4">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Income</p>
                <p class="mt-2 text-xl font-semibold text-emerald-700">PHP {{ number_format($totalIncome, 2) }}</p>
            </div>
            <div class="app-card p-4">
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
                    @include('partials.empty-state', [
                        'title' => 'No transactions recorded yet',
                        'message' => 'Add your first transaction to start tracking daily spending and income.',
                        'actionLabel' => 'Create Transaction',
                        'actionHref' => route('transactions.create'),
                    ])
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
@endsection
