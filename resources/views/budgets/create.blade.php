@extends('layouts.app')

@section('title', 'Create Budget')

@section('content')
    <div class="app-card">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-slate-900">Create Budget</h1>
            <p class="text-sm text-slate-500">Set up a new budget plan.</p>
        </div>

        <form method="post" action="{{ route('budgets.store') }}" class="space-y-6">
            @csrf
            @include('budgets._form')

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">
                    Save Budget
                </button>
                <a href="{{ route('budgets.index') }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

