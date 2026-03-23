@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
                <p class="text-sm text-slate-600">Welcome back. Here is your latest finance overview.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('budgets.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">New Budget</a>
                <a href="{{ route('transactions.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">New Transaction</a>
                <a href="{{ route('categories.index') }}" class="rounded-lg border border-emerald-200 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-50">View Categories</a>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Allocated</p>
                <p class="mt-2 text-xl font-semibold text-slate-900">PHP {{ number_format($totalAllocatedBudget, 2) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Expenses</p>
                <p class="mt-2 text-xl font-semibold text-amber-700">PHP {{ number_format($totalExpenses, 2) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Total Income</p>
                <p class="mt-2 text-xl font-semibold text-emerald-700">PHP {{ number_format($totalIncome, 2) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Remaining Balance</p>
                <p class="mt-2 text-xl font-semibold {{ $remainingBalance >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                    PHP {{ number_format($remainingBalance, 2) }}
                </p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-slate-500">Active Budgets</p>
                <p class="mt-2 text-xl font-semibold text-slate-900">{{ $activeBudgetsCount }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Recent Transactions</h2>

                @if ($recentTransactions->isEmpty())
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        <p class="mb-2 font-medium">No transactions recorded yet.</p>
                        <a href="{{ route('transactions.create') }}" class="text-emerald-700 underline hover:text-emerald-800">Create your first transaction</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Title</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Category</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Budget</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Type</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Amount</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($recentTransactions as $transaction)
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-slate-800">{{ $transaction->title }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $transaction->category?->name ?? '-' }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $transaction->budget?->title ?? '-' }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs {{ $transaction->type === 'income' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700' }}">
                                                {{ ucfirst($transaction->type) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-slate-700">PHP {{ number_format((float) $transaction->amount, 2) }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $transaction->transaction_date?->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-slate-900">Active Budgets</h2>

                @if ($activeBudgets->isEmpty())
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                        <p class="mb-2 font-medium">No budgets created yet.</p>
                        <a href="{{ route('budgets.create') }}" class="text-emerald-700 underline hover:text-emerald-800">Create your first budget</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Title</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Category</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Allocated</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Status</th>
                                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Period</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($activeBudgets as $budget)
                                    <tr>
                                        <td class="px-3 py-2 font-medium text-slate-800">{{ $budget->title }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $budget->category?->name ?? '-' }}</td>
                                        <td class="px-3 py-2 text-slate-700">PHP {{ number_format((float) $budget->allocated_amount, 2) }}</td>
                                        <td class="px-3 py-2">
                                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                                                {{ ucfirst($budget->status) }}
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-slate-600">
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
