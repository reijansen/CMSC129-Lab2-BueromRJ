@extends('layouts.app')

@section('title', 'Edit Budget')

@section('content')
    <div class="app-card">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-slate-900">Edit Budget</h1>
            <p class="text-sm text-slate-500">Update your budget details.</p>
        </div>

        <form method="post" action="{{ route('budgets.update', $budget) }}" class="space-y-6">
            @csrf
            @method('PUT')
            @include('budgets._form', ['budget' => $budget])

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">
                    Update Budget
                </button>
                <a href="{{ route('budgets.index') }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

