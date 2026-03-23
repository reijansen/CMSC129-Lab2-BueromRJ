@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Transactions</h1>
                <p class="text-sm text-slate-500">Track all income and expense records.</p>
            </div>
            <a href="{{ route('transactions.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                New Transaction
            </a>
        </div>

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
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('transactions.show', $transaction) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-50">
                                        View
                                    </a>
                                    <a href="{{ route('transactions.edit', $transaction) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-50">
                                        Edit
                                    </a>
                                    <form method="post" action="{{ route('transactions.destroy', $transaction) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-red-300 px-3 py-1.5 text-red-700 hover:bg-red-50" onclick="return confirm('Delete this transaction?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">No transactions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection
