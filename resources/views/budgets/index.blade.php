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
                <button type="button" class="btn-primary" data-open-budget-modal>
                    New Budget
                </button>
            </div>
        </div>

        <div class="mb-4 flex items-center gap-2 border-b border-slate-200 pb-3">
            <a href="{{ route('budgets.index') }}"
                class="rounded-lg px-3 py-1.5 text-sm font-medium bg-emerald-100 text-emerald-800">
                Active
            </a>
            <a href="{{ route('budgets.trash') }}"
                class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100">
                Archived / Deleted
            </a>
        </div>

        <form method="GET" action="{{ route('budgets.index') }}" class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-3">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
            <div>
                <label for="search" class="label-control mb-1">Search</label>
                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ $filters['search'] ?? '' }}"
                    placeholder="Title or notes"
                    class="input-control"
                >
            </div>

            <div>
                <label for="budget_filter_category_id" class="label-control mb-1">Category</label>
                <select
                    id="budget_filter_category_id"
                    name="category_id"
                    class="input-control"
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
                <label for="budget_filter_status" class="label-control mb-1">Status</label>
                <select
                    id="budget_filter_status"
                    name="status"
                    class="input-control"
                >
                    <option value="">All statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="completed" @selected(($filters['status'] ?? '') === 'completed')>Completed</option>
                    <option value="exceeded" @selected(($filters['status'] ?? '') === 'exceeded')>Exceeded</option>
                    <option value="archived" @selected(($filters['status'] ?? '') === 'archived')>Archived</option>
                </select>
            </div>

            <div>
                <label for="period_start_from" class="label-control mb-1">Period Start From</label>
                <input
                    id="period_start_from"
                    name="period_start_from"
                    type="date"
                    value="{{ $filters['period_start_from'] ?? '' }}"
                    class="input-control"
                >
            </div>

            <div>
                <label for="period_end_to" class="label-control mb-1">Period End To</label>
                <input
                    id="period_end_to"
                    name="period_end_to"
                    type="date"
                    value="{{ $filters['period_end_to'] ?? '' }}"
                    class="input-control"
                >
            </div>
            </div>

            <div class="mt-3 flex flex-wrap items-end gap-2">
                <button type="submit" class="btn-primary">Apply</button>
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
                                    <form method="post" action="{{ route('budgets.destroy', $budget) }}" data-confirm-message="Archive this budget?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-md border border-amber-300 px-3 py-1.5 text-amber-700 hover:bg-amber-50">
                                            Archive
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
                                <button type="button" data-open-budget-modal class="mt-3 inline-flex btn-primary px-3 py-1.5 text-xs">Create Budget</button>
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

    <div class="fixed inset-0 z-50 hidden items-center justify-center p-4" data-budget-modal>
        <div class="absolute inset-0 bg-slate-900/45" data-close-budget-modal></div>
        <div class="relative flex w-full max-w-2xl max-h-[90vh] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Create Budget</h2>
                    <p class="text-sm text-slate-500">Set up a new budget plan.</p>
                </div>
                <button type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                    data-close-budget-modal aria-label="Close create budget modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="post" action="{{ route('budgets.store') }}" class="flex-1 space-y-5 overflow-y-auto px-5 py-4" data-budget-create-form>
                @csrf
                @include('budgets._form')
                <div class="sticky bottom-0 flex items-center justify-end gap-2 border-t border-slate-200 bg-white pt-3">
                    <button type="button" class="btn-secondary" data-close-budget-modal>Cancel</button>
                    <button type="submit" class="btn-primary">Save Budget</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.querySelector('[data-budget-modal]');
            if (!modal) return;

            const openButtons = document.querySelectorAll('[data-open-budget-modal]');
            const closeButtons = modal.querySelectorAll('[data-close-budget-modal]');
            const form = modal.querySelector('[data-budget-create-form]');

            const setValue = (name, value = '') => {
                const el = form?.querySelector(`[name="${name}"]`);
                if (el) {
                    el.value = value;
                }
            };

            const resetBudgetForm = () => {
                if (!form) return;
                setValue('category_id', '');
                setValue('title', '');
                setValue('allocated_amount', '');
                setValue('period_start', '');
                setValue('period_end', '');
                setValue('status', 'active');
                setValue('notes', '');
            };

            const openModal = (reset = false) => {
                if (reset) {
                    resetBudgetForm();
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', () => openModal(true));
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });

            @if ($errors->any() && (old('title') !== null || old('allocated_amount') !== null || old('period_start') !== null || old('period_end') !== null || old('category_id') !== null))
                openModal(false);
            @endif
        })();
    </script>
@endsection


