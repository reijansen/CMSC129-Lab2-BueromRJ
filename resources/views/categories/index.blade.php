@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="app-card">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Categories</h1>
                <p class="text-sm text-slate-500">Manage your income and expense category types.</p>
            </div>
            <a href="{{ route('categories.create') }}" class="btn-primary">
                New Category
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Type</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Color</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Budgets</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Transactions</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Description</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $category->name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                    {{ ucfirst($category->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($category->color)
                                    <span class="inline-flex items-center gap-2">
                                        <span class="h-3 w-3 rounded-full border border-slate-200" style="background-color: {{ $category->color }}"></span>
                                        <span class="text-slate-600">{{ $category->color }}</span>
                                    </span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-700">{{ $category->budgets_count }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $category->transactions_count }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $category->description ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('categories.show', $category) }}" class="btn-secondary px-3 py-1.5 text-xs">
                                        View
                                    </a>
                                    <a href="{{ route('categories.edit', $category) }}" class="btn-secondary px-3 py-1.5 text-xs">
                                        Edit
                                    </a>
                                    <form method="post" action="{{ route('categories.destroy', $category) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-red-300 px-3 py-1.5 text-red-700 hover:bg-red-50" onclick="return confirm('Delete this category?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center">
                                <p class="text-sm font-medium text-slate-700">No categories yet.</p>
                                <p class="mt-1 text-xs text-slate-500">Create your first category to organize budgets and transactions.</p>
                                <a href="{{ route('categories.create') }}" class="mt-3 inline-flex btn-primary px-3 py-1.5 text-xs">Create Category</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    </div>
@endsection

