@extends('layouts.app')

@section('title', 'Create Category')

@section('content')
    <div class="app-card">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-slate-900">Create Category</h1>
            <p class="text-sm text-slate-500">Add a new category for your budgets and transactions.</p>
        </div>

        <form action="{{ route('categories.store') }}" method="post" class="space-y-6">
            @csrf
            @include('categories._form')

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">
                    Save Category
                </button>
                <a href="{{ route('categories.index') }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

