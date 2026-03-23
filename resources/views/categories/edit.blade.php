@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h1 class="text-2xl font-semibold text-slate-900">Edit Category</h1>
            <p class="text-sm text-slate-500">Update your category details.</p>
        </div>

        <form action="{{ route('categories.update', $category) }}" method="post" class="space-y-6">
            @csrf
            @method('PUT')
            @include('categories._form', ['category' => $category])

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                    Update Category
                </button>
                <a href="{{ route('categories.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
@endsection

