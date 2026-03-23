@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Categories</h1>
                <p class="text-sm text-slate-500">Manage your income and expense category types.</p>
            </div>
            <a href="{{ route('categories.create') }}" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
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
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Description</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($categories as $category)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $category->name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
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
                            <td class="px-4 py-3 text-slate-600">{{ $category->description ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('categories.edit', $category) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-50">
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
                            <td colspan="5" class="px-4 py-8 text-center text-slate-500">No categories found.</td>
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
