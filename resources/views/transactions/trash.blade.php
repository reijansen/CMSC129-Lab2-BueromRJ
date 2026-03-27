@extends('layouts.app')

@section('title', 'Transaction Trash')

@section('content')
    <div class="app-card">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Transaction Trash</h1>
                <p class="text-sm text-slate-500">Restore deleted transactions or permanently remove them.</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="btn-secondary">
                Back to Transactions
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
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Deleted At</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $transaction->title }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->budget?->title ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->category?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ ucfirst($transaction->type) }}</td>
                            <td class="px-4 py-3 text-slate-700">PHP {{ number_format((float) $transaction->amount, 2) }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->transaction_date?->format('M d, Y') }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->deleted_at?->format('M d, Y h:i A') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <form method="post" action="{{ route('transactions.restore', $transaction->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="rounded-md border border-emerald-300 px-3 py-1.5 text-emerald-700 hover:bg-emerald-50">
                                            Restore
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('transactions.force-delete', $transaction->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-red-300 px-3 py-1.5 text-red-700 hover:bg-red-50" onclick="return confirm('Permanently delete this transaction? This cannot be undone.')">
                                            Permanently Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-slate-500">Trash is empty. No deleted transactions found.</td>
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


