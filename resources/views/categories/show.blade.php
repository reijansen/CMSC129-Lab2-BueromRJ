@extends('layouts.app')

@section('title', 'Category Details')

@section('content')
    <div class="space-y-4">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-900">{{ $category->name }}</h1>
                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                    {{ ucfirst($category->type) }}
                </span>
            </div>

            <dl class="grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-slate-500">Color</dt>
                    <dd class="mt-1 font-medium text-slate-800">
                        @if ($category->color)
                            <span class="inline-flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full border border-slate-200" style="background-color: {{ $category->color }}"></span>
                                <span>{{ $category->color }}</span>
                            </span>
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500">Description</dt>
                    <dd class="mt-1 font-medium text-slate-800">{{ $category->description ?: '-' }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('categories.edit', $category) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Edit Category
                </a>
                <a href="{{ route('categories.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Back
                </a>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Related Budgets</h2>
                <span class="text-xs text-slate-500">Latest 5</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Title</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Allocated</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Period</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($relatedBudgets as $budget)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $budget->title }}</td>
                                <td class="px-4 py-3 text-slate-700">PHP {{ number_format((float) $budget->allocated_amount, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $budget->period_start?->format('M d, Y') }} - {{ $budget->period_end?->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ ucfirst($budget->status) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('budgets.show', $budget) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-50">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">No budgets under this category yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Related Transactions</h2>
                <span class="text-xs text-slate-500">Latest 5</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Title</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Budget</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($relatedTransactions as $transaction)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800">{{ $transaction->title }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ ucfirst($transaction->type) }}</td>
                                <td class="px-4 py-3 text-slate-700">PHP {{ number_format((float) $transaction->amount, 2) }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $transaction->transaction_date?->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $transaction->budget?->title ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('transactions.show', $transaction) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-50">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-slate-500">No transactions under this category yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
