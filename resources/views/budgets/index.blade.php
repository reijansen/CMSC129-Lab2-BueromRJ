@extends('layouts.app')

@section('title', 'Budgets')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Budgets</h1>
                <p class="text-sm text-slate-500">Create and manage your budget allocations.</p>
            </div>
            <a href="{{ route('budgets.create') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                New Budget
            </a>
        </div>

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
                                    <a href="{{ route('budgets.show', $budget) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-50">View</a>
                                    <a href="{{ route('budgets.edit', $budget) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-50">Edit</a>
                                    <form method="post" action="{{ route('budgets.destroy', $budget) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-red-300 px-3 py-1.5 text-red-700 hover:bg-red-50" onclick="return confirm('Delete this budget?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-slate-500">No budgets found.</td>
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
