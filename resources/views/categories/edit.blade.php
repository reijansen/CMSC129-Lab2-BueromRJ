@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
    <div class="app-card">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-slate-900">Edit Category</h1>
            <p class="text-sm text-slate-500">Update your category details.</p>
        </div>

        <form action="{{ route('categories.update', $category) }}" method="post" class="space-y-6">
            @csrf
            @method('PUT')
            @include('categories._form', ['category' => $category])

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-primary">
                    Update Category
                </button>
                <a href="{{ route('categories.index') }}" class="btn-secondary">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

