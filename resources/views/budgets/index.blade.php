@extends('layouts.app')

@section('title', 'Budgets')

@section('content')
    <div class="app-card">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Budgets</h1>
                <p class="text-sm text-slate-500">Create and manage your budget allocations.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('budgets.trash') }}" class="btn-secondary">
                    View Trash
                </a>
                <a href="{{ route('budgets.create') }}" class="btn-primary">
                    New Budget
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('budgets.index') }}" class="mb-6 rounded-lg border border-emerald-100 bg-emerald-50/40 p-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Search</label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Title or notes"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                </div>

                <div>
                    <label for="category_id" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Category</label>
                    <select
                        id="category_id"
                        name="category_id"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == (string) $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Status</label>
                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                        <option value="">All statuses</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                        <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                        <option value="exceeded" @selected(($filters['status'] ?? '') === 'exceeded')>Exceeded</option>
                        <option value="archived" @selected(($filters['status'] ?? '') === 'archived')>Archived</option>
                    </select>
                </div>

                <div>
                    <label for="period_start_from" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Period Start From</label>
                    <input
                        id="period_start_from"
                        name="period_start_from"
                        type="date"
                        value="{{ $filters['period_start_from'] ?? '' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                </div>

                <div>
                    <label for="period_end_to" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">Period End To</label>
                    <input
                        id="period_end_to"
                        name="period_end_to"
                        type="date"
                        value="{{ $filters['period_end_to'] ?? '' }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200"
                    >
                </div>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <button type="submit" class="btn-primary">
                    Apply Filters
                </button>
                <a href="{{ route('budgets.index') }}" class="btn-secondary">
                    Clear
                </a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Title</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Category</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Allocated</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Period</th>
                        <th class="px-4 py-3 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($budgets as $budget)
                        <tr>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $budget->title }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $budget->category?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-700">PHP {{ number_format((float) $budget->allocated_amount, 2) }}</td>
                            <td class="px-4 py-3 text-slate-600">
                                {{ $budget->period_start?->format('M d, Y') }} - {{ $budget->period_end?->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                    {{ ucfirst($budget->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('budgets.show', $budget) }}" class="btn-secondary px-3 py-1.5 text-xs">View</a>
                                    <a href="{{ route('budgets.edit', $budget) }}" class="btn-secondary px-3 py-1.5 text-xs">Edit</a>
                                    <form method="post" action="{{ route('budgets.destroy', $budget) }}" data-confirm-message="Delete this budget?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-red-300 px-3 py-1.5 text-red-700 hover:bg-red-50">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center">
                                <p class="text-sm font-medium text-slate-700">No budgets found.</p>
                                <p class="mt-1 text-xs text-slate-500">Set up a budget to start tracking spending limits.</p>
                                <a href="{{ route('budgets.create') }}" class="mt-3 inline-flex btn-primary px-3 py-1.5 text-xs">Create Budget</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $budgets->links() }}
        </div>
    </div>
@endsection


