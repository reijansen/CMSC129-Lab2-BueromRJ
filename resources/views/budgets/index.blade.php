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
                                    <button type="button" class="btn-secondary px-3 py-1.5 text-xs" data-open-view-budget-modal="{{ $budget->id }}">View</button>
                                    <button type="button" class="btn-secondary px-3 py-1.5 text-xs" data-open-edit-budget-modal="{{ $budget->id }}">Edit</button>
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

    @foreach ($budgets as $budget)
        @php
            $isEditingOld = (string) old('edit_budget_id') === (string) $budget->id;
            $selectedCategoryId = $isEditingOld ? old('category_id', $budget->category_id) : $budget->category_id;
            $selectedStatus = $isEditingOld ? old('status', $budget->status) : $budget->status;
        @endphp

        <div class="fixed inset-0 z-50 hidden items-center justify-center p-4" data-view-budget-modal="{{ $budget->id }}">
            <div class="absolute inset-0 bg-slate-900/45" data-close-budget-generic></div>
            <div class="relative w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">Budget Details</h2>
                    <button type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        data-close-budget-generic aria-label="Close budget details modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="grid gap-4 px-5 py-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Title</p>
                        <p class="mt-1 text-sm font-medium text-slate-900">{{ $budget->title }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Category</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $budget->category?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Allocated Amount</p>
                        <p class="mt-1 text-sm text-slate-700">PHP {{ number_format((float) $budget->allocated_amount, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</p>
                        <p class="mt-1 text-sm text-slate-700">{{ ucfirst($budget->status) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Period Start</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $budget->period_start?->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Period End</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $budget->period_end?->format('M d, Y') }}</p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Notes</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $budget->notes ?: '-' }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-end border-t border-slate-200 px-5 py-3">
                    <button type="button" class="btn-secondary" data-close-budget-generic>Close</button>
                </div>
            </div>
        </div>

        <div class="fixed inset-0 z-50 hidden items-center justify-center p-4" data-edit-budget-modal="{{ $budget->id }}">
            <div class="absolute inset-0 bg-slate-900/45" data-close-budget-generic></div>
            <div class="relative flex w-full max-w-2xl max-h-[90vh] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Edit Budget</h2>
                        <p class="text-sm text-slate-500">Update this budget details.</p>
                    </div>
                    <button type="button"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        data-close-budget-generic aria-label="Close edit budget modal">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="post" action="{{ route('budgets.update', $budget) }}" class="flex-1 space-y-5 overflow-y-auto px-5 py-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="edit_budget_id" value="{{ $budget->id }}">

                    <div class="space-y-5">
                        <div>
                            <label for="edit_budget_category_{{ $budget->id }}" class="label-control">Category</label>
                            <select id="edit_budget_category_{{ $budget->id }}" name="category_id" class="input-control" required>
                                <option value="">Select a category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>
                                        {{ $category->name }} ({{ ucfirst($category->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="edit_budget_title_{{ $budget->id }}" class="label-control">Title</label>
                            <input id="edit_budget_title_{{ $budget->id }}" name="title" type="text"
                                value="{{ $isEditingOld ? old('title', $budget->title) : $budget->title }}"
                                class="input-control" required>
                        </div>

                        <div>
                            <label for="edit_budget_allocated_{{ $budget->id }}" class="label-control">Allocated Amount</label>
                            <input id="edit_budget_allocated_{{ $budget->id }}" name="allocated_amount" type="number" min="0" step="0.01"
                                value="{{ $isEditingOld ? old('allocated_amount', $budget->allocated_amount) : $budget->allocated_amount }}"
                                class="input-control" required>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label for="edit_budget_period_start_{{ $budget->id }}" class="label-control">Period Start</label>
                                <input id="edit_budget_period_start_{{ $budget->id }}" name="period_start" type="date"
                                    value="{{ $isEditingOld ? old('period_start', optional($budget->period_start)->format('Y-m-d')) : optional($budget->period_start)->format('Y-m-d') }}"
                                    class="input-control" required>
                            </div>
                            <div>
                                <label for="edit_budget_period_end_{{ $budget->id }}" class="label-control">Period End</label>
                                <input id="edit_budget_period_end_{{ $budget->id }}" name="period_end" type="date"
                                    value="{{ $isEditingOld ? old('period_end', optional($budget->period_end)->format('Y-m-d')) : optional($budget->period_end)->format('Y-m-d') }}"
                                    class="input-control" required>
                            </div>
                        </div>

                        <div>
                            <label for="edit_budget_status_{{ $budget->id }}" class="label-control">Status</label>
                            <select id="edit_budget_status_{{ $budget->id }}" name="status" class="input-control" required>
                                @foreach (['active', 'completed', 'exceeded', 'archived'] as $status)
                                    <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ ucfirst($status) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="edit_budget_notes_{{ $budget->id }}" class="label-control">Notes</label>
                            <textarea id="edit_budget_notes_{{ $budget->id }}" name="notes" rows="4" class="input-control">{{ $isEditingOld ? old('notes', $budget->notes) : $budget->notes }}</textarea>
                        </div>
                    </div>

                    <div class="sticky bottom-0 flex items-center justify-end gap-2 border-t border-slate-200 bg-white pt-3">
                        <button type="button" class="btn-secondary" data-close-budget-generic>Cancel</button>
                        <button type="submit" class="btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

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

            document.querySelectorAll('[data-open-view-budget-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-open-view-budget-modal');
                    const targetModal = document.querySelector(`[data-view-budget-modal="${id}"]`);
                    if (!targetModal) return;
                    targetModal.classList.remove('hidden');
                    targetModal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                });
            });

            document.querySelectorAll('[data-open-edit-budget-modal]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.getAttribute('data-open-edit-budget-modal');
                    const targetModal = document.querySelector(`[data-edit-budget-modal="${id}"]`);
                    if (!targetModal) return;
                    targetModal.classList.remove('hidden');
                    targetModal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                });
            });

            const closeGenericBudgetModal = (modalEl) => {
                if (!modalEl) return;
                modalEl.classList.add('hidden');
                modalEl.classList.remove('flex');
                const hasAnyOpenModal = document.querySelector('[data-budget-modal].flex, [data-view-budget-modal].flex, [data-edit-budget-modal].flex');
                if (!hasAnyOpenModal) {
                    document.body.classList.remove('overflow-hidden');
                }
            };

            document.querySelectorAll('[data-close-budget-generic]').forEach((button) => {
                button.addEventListener('click', () => {
                    const modalEl = button.closest('[data-view-budget-modal], [data-edit-budget-modal]');
                    closeGenericBudgetModal(modalEl);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                    document.querySelectorAll('[data-view-budget-modal].flex, [data-edit-budget-modal].flex').forEach((modalEl) => {
                        closeGenericBudgetModal(modalEl);
                    });
                }
            });

            @if ($errors->any() && old('edit_budget_id'))
                const editModal = document.querySelector('[data-edit-budget-modal="{{ old('edit_budget_id') }}"]');
                if (editModal) {
                    editModal.classList.remove('hidden');
                    editModal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }
            @elseif ($errors->any() && (old('title') !== null || old('allocated_amount') !== null || old('period_start') !== null || old('period_end') !== null || old('category_id') !== null))
                openModal(false);
            @endif
        })();
    </script>
@endsection


