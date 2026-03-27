@extends('layouts.app')

@section('title', 'Budget Details')

@section('content')
    <div class="space-y-4">
        <div class="app-card">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-900">{{ $budget->title }}</h1>
                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                    {{ ucfirst($budget->status) }}
                </span>
            </div>

            <dl class="grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-slate-500">Category</dt>
                    <dd class="font-medium text-slate-800">{{ $budget->category?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Allocated Amount</dt>
                    <dd class="font-medium text-slate-800">PHP {{ number_format((float) $budget->allocated_amount, 2) }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Period Start</dt>
                    <dd class="font-medium text-slate-800">{{ $budget->period_start?->format('M d, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Period End</dt>
                    <dd class="font-medium text-slate-800">{{ $budget->period_end?->format('M d, Y') }}</dd>
                </div>
                <div class="md:col-span-2">
                    <dt class="text-slate-500">Notes</dt>
                    <dd class="font-medium text-slate-800">{{ $budget->notes ?: '-' }}</dd>
                </div>
            </dl>

            <div class="mt-6 flex items-center gap-3">
                <a href="{{ route('budgets.edit', $budget) }}" class="btn-primary">
                    Edit Budget
                </a>
                <a href="{{ route('budgets.index') }}" class="btn-secondary">
                    Back
                </a>
            </div>
        </div>

        <div class="app-card">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900">Related Transactions</h2>
                <span class="text-xs text-slate-500">Latest {{ $relatedTransactions->count() }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Title</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Type</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Date</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Category</th>
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
                                <td class="px-4 py-3 text-slate-600">{{ $transaction->category?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('transactions.show', $transaction) }}" class="btn-secondary px-3 py-1.5 text-xs">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-slate-500">No transactions under this budget yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

