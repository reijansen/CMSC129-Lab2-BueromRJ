@extends('layouts.app')

@section('title', 'Create Transaction')

@section('content')
    <div class="app-card">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-slate-900">Create Transaction</h1>
            <p class="text-sm text-slate-500">Add a new income or expense record.</p>
        </div>

        <form method="post" action="{{ route('transactions.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @include('transactions._form')

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">
                    Save Transaction
                </button>
                <a href="{{ route('transactions.index') }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
