@extends('layouts.app')

@section('title', 'Budget Details')

@section('content')
    <div class="space-y-4">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-900">{{ $budget->title }}</h1>
                <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700">
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
                <a href="{{ route('budgets.edit', $budget) }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Edit Budget
                </a>
                <a href="{{ route('budgets.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Back
                </a>
            </div>
        </div>

        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-6 text-sm text-emerald-800 shadow-sm">
            <h2 class="mb-1 font-semibold">Transactions Placeholder</h2>
            <p>Transactions under this budget will be shown here in a later phase.</p>
        </div>
    </div>
@endsection
