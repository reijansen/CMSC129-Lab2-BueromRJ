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
                <a href="{{ route('transactions.create') }}" class="btn-primary">
                    New Transaction
                </a>
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
                    <label for="type" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Type</label>
                    <select
                        id="type"
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
@endsection


