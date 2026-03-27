@extends('layouts.app')

@section('title', 'Transaction Details')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-slate-900">{{ $transaction->title }}</h1>
            @if ($transaction->type === 'income')
                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                    Income
                </span>
            @else
                <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                    Expense
                </span>
            @endif
        </div>

        <dl class="grid gap-4 text-sm md:grid-cols-2">
            <div>
                <dt class="text-slate-500">Related Budget</dt>
                <dd class="font-medium text-slate-800">{{ $transaction->budget?->title ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Related Category</dt>
                <dd class="font-medium text-slate-800">{{ $transaction->category?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Amount</dt>
                <dd class="font-medium text-slate-800">PHP {{ number_format((float) $transaction->amount, 2) }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Transaction Date</dt>
                <dd class="font-medium text-slate-800">{{ $transaction->transaction_date?->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Payment Method</dt>
                <dd class="font-medium text-slate-800">{{ $transaction->payment_method ?: '-' }}</dd>
            </div>
            <div>
                <dt class="text-slate-500">Attachment</dt>
                <dd class="font-medium text-slate-800">{{ $transaction->attachment_path ?: '-' }}</dd>
            </div>
            <div class="md:col-span-2">
                <dt class="text-slate-500">Notes</dt>
                <dd class="font-medium text-slate-800">{{ $transaction->notes ?: '-' }}</dd>
            </div>
        </dl>

        <div class="mt-6 flex items-center gap-3">
            <a href="{{ route('transactions.edit', $transaction) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                Edit Transaction
            </a>
            <a href="{{ route('transactions.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Back
            </a>
        </div>
    </div>
@endsection
