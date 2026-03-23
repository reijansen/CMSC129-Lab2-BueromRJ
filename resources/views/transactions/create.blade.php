@extends('layouts.app')

@section('title', 'Create Transaction')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-slate-900">Create Transaction</h1>
            <p class="text-sm text-slate-500">Add a new income or expense record.</p>
        </div>

        <form method="post" action="{{ route('transactions.store') }}" class="space-y-6">
            @csrf
            @include('transactions._form')

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Save Transaction
                </button>
                <a href="{{ route('transactions.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

