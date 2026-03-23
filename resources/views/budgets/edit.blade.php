@extends('layouts.app')

@section('title', 'Edit Budget')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-slate-900">Edit Budget</h1>
            <p class="text-sm text-slate-500">Update your budget details.</p>
        </div>

        <form method="post" action="{{ route('budgets.update', $budget) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('budgets._form', ['budget' => $budget])

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    Update Budget
                </button>
                <a href="{{ route('budgets.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

