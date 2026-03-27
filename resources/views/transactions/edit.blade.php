@extends('layouts.app')

@section('title', 'Edit Transaction')

@section('content')
    <div class="app-card">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-slate-900">Edit Transaction</h1>
            <p class="text-sm text-slate-500">Update transaction details.</p>
        </div>

        <form method="post" action="{{ route('transactions.update', $transaction) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            @include('transactions._form', ['transaction' => $transaction])

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">
                    Update Transaction
                </button>
                <a href="{{ route('transactions.index') }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection
