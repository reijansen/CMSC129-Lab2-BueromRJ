@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="app-card">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Categories</h1>
                <p class="text-sm text-slate-500">Manage your income and expense category types.</p>
            </div>
            @if (($tab ?? 'active') === 'active')
                <button type="button" class="btn-primary" data-open-category-modal>
                    New Category
                </button>
            @endif
        </div>

        <div class="mb-4 flex items-center gap-2 border-b border-slate-200 pb-3">
            <a href="{{ route('categories.index', ['tab' => 'active']) }}"
                class="{{ ($tab ?? 'active') === 'active' ? 'bg-emerald-100 text-emerald-800' : 'text-slate-600 hover:bg-slate-100' }} rounded-lg px-3 py-1.5 text-sm font-medium">
                Active
            </a>
            <a href="{{ route('categories.index', ['tab' => 'deleted']) }}"
                class="{{ ($tab ?? 'active') === 'deleted' ? 'bg-amber-100 text-amber-800' : 'text-slate-600 hover:bg-slate-100' }} rounded-lg px-3 py-1.5 text-sm font-medium">
                Archived / Deleted
            </a>
        </div>

        <form method="get" action="{{ route('categories.index') }}" class="mb-4 grid gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3 sm:grid-cols-[1fr_180px_auto]">
            <input type="hidden" name="tab" value="{{ $tab ?? 'active' }}">
            <div>
                <label for="q" class="label-control mb-1">Search</label>
                <input id="q" name="q" type="text" value="{{ $search ?? '' }}" class="input-control"
                    placeholder="Search by name, description, or color">
            </div>
            <div>
                <label for="type_filter" class="label-control mb-1">Type</label>
                <select id="type_filter" name="type" class="input-control">
                    <option value="">All Types</option>
                    <option value="income" @selected(($type ?? '') === 'income')>Income</option>
                    <option value="expense" @selected(($type ?? '') === 'expense')>Expense</option>
                    <option value="both" @selected(($type ?? '') === 'both')>Both</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="btn-primary">Apply</button>
                <a href="{{ route('categories.index', ['tab' => $tab ?? 'active']) }}" class="btn-secondary">Clear</a>
            </div>
        </form>

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
                        @if (($tab ?? 'active') === 'deleted')
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Deleted At</th>
                        @endif
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
                            @if (($tab ?? 'active') === 'deleted')
                                <td class="px-4 py-3 text-slate-600">{{ $category->deleted_at?->format('M d, Y h:i A') }}</td>
                            @endif
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @if (($tab ?? 'active') === 'active')
                                        <a href="{{ route('categories.show', $category) }}" class="btn-secondary px-3 py-1.5 text-xs">
                                            View
                                        </a>
                                        <button type="button" class="btn-secondary px-3 py-1.5 text-xs" data-open-edit-category-modal
                                            data-id="{{ $category->id }}"
                                            data-name="{{ $category->name }}"
                                            data-type="{{ $category->type }}"
                                            data-color="{{ $category->color }}"
                                            data-description="{{ $category->description }}">
                                            Edit
                                        </button>
                                        <form method="post" action="{{ route('categories.destroy', $category) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-amber-300 px-3 py-1.5 text-amber-700 hover:bg-amber-50" onclick="return confirm('Archive this category?')">
                                                Archive
                                            </button>
                                        </form>
                                    @else
                                        <form method="post" action="{{ route('categories.restore', $category->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="rounded-md border border-emerald-300 px-3 py-1.5 text-emerald-700 hover:bg-emerald-50">
                                                Restore
                                            </button>
                                        </form>
                                        <form method="post" action="{{ route('categories.force-delete', $category->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-red-300 px-3 py-1.5 text-red-700 hover:bg-red-50"
                                                onclick="return confirm('Permanently delete this category? This cannot be undone.')">
                                                Permanently Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($tab ?? 'active') === 'deleted' ? 8 : 7 }}" class="px-4 py-8 text-center">
                                @if (($tab ?? 'active') === 'active')
                                    <p class="text-sm font-medium text-slate-700">No categories yet.</p>
                                    <p class="mt-1 text-xs text-slate-500">Create your first category to organize budgets and transactions.</p>
                                    <button type="button" data-open-category-modal class="mt-3 inline-flex btn-primary px-3 py-1.5 text-xs">
                                        Create Category
                                    </button>
                                @else
                                    <p class="text-sm font-medium text-slate-700">No archived/deleted categories.</p>
                                    <p class="mt-1 text-xs text-slate-500">Archived categories will appear here.</p>
                                @endif
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

    <div class="fixed inset-0 z-50 hidden items-center justify-center p-4" data-category-modal>
        <div class="absolute inset-0 bg-slate-900/45" data-close-category-modal></div>
        <div class="relative flex w-full max-w-lg max-h-[85vh] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Create Category</h2>
                    <p class="text-sm text-slate-500">Add a new category for budgets and transactions.</p>
                </div>
                <button type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                    data-close-category-modal aria-label="Close create category modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form action="{{ route('categories.store') }}" method="post" class="flex-1 space-y-4 overflow-y-auto px-5 py-4">
                @csrf
                @include('categories._form')
                <div class="sticky bottom-0 flex items-center justify-end gap-2 border-t border-slate-200 bg-white pt-3">
                    <button type="button" class="btn-secondary" data-close-category-modal>Cancel</button>
                    <button type="submit" class="btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>

    <div class="fixed inset-0 z-50 hidden items-center justify-center p-4" data-edit-category-modal>
        <div class="absolute inset-0 bg-slate-900/45" data-close-edit-category-modal></div>
        <div class="relative flex w-full max-w-lg max-h-[85vh] flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Edit Category</h2>
                    <p class="text-sm text-slate-500">Update this category details.</p>
                </div>
                <button type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                    data-close-edit-category-modal aria-label="Close edit category modal">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="post" class="flex-1 space-y-4 overflow-y-auto px-5 py-4" data-edit-category-form>
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label for="edit_name" class="label-control">Name</label>
                        <input id="edit_name" name="name" type="text" class="input-control" required>
                    </div>

                    <div>
                        <label for="edit_type" class="label-control">Type</label>
                        <select id="edit_type" name="type" class="input-control" required>
                            @foreach (['income' => 'Income', 'expense' => 'Expense', 'both' => 'Both'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit_color" class="label-control">Color</label>
                        <input id="edit_color" name="color" type="text" placeholder="e.g. #0ea5e9 or sky" class="input-control">
                    </div>

                    <div>
                        <label for="edit_description" class="label-control">Description</label>
                        <textarea id="edit_description" name="description" rows="3" class="input-control"></textarea>
                    </div>
                </div>

                <div class="sticky bottom-0 flex items-center justify-end gap-2 border-t border-slate-200 bg-white pt-3">
                    <button type="button" class="btn-secondary" data-close-edit-category-modal>Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (() => {
            const modal = document.querySelector('[data-category-modal]');
            const editModal = document.querySelector('[data-edit-category-modal]');
            const editForm = document.querySelector('[data-edit-category-form]');
            if (!modal || !editModal || !editForm) return;

            const openButtons = document.querySelectorAll('[data-open-category-modal]');
            const closeButtons = modal.querySelectorAll('[data-close-category-modal]');
            const form = modal.querySelector('form');
            const openEditButtons = document.querySelectorAll('[data-open-edit-category-modal]');
            const closeEditButtons = editModal.querySelectorAll('[data-close-edit-category-modal]');

            const resetCategoryForm = () => {
                if (!form) return;

                const name = form.querySelector('[name="name"]');
                const type = form.querySelector('[name="type"]');
                const color = form.querySelector('[name="color"]');
                const description = form.querySelector('[name="description"]');

                if (name) name.value = '';
                if (type) type.value = 'income';
                if (color) color.value = '';
                if (description) description.value = '';
            };

            const openModal = (reset = false) => {
                if (reset) {
                    resetCategoryForm();
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

            const openEditModal = (payload) => {
                editForm.action = `/categories/${payload.id}`;
                editForm.querySelector('[name="name"]').value = payload.name || '';
                editForm.querySelector('[name="type"]').value = payload.type || 'income';
                editForm.querySelector('[name="color"]').value = payload.color || '';
                editForm.querySelector('[name="description"]').value = payload.description || '';
                editModal.classList.remove('hidden');
                editModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            };

            const closeEditModal = () => {
                editModal.classList.add('hidden');
                editModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            };

            openButtons.forEach((button) => {
                button.addEventListener('click', () => openModal(true));
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            openEditButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    openEditModal({
                        id: button.dataset.id,
                        name: button.dataset.name,
                        type: button.dataset.type,
                        color: button.dataset.color,
                        description: button.dataset.description,
                    });
                });
            });

            closeEditButtons.forEach((button) => {
                button.addEventListener('click', closeEditModal);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                    closeEditModal();
                }
            });

            @if ($errors->any() && (old('name') !== null || old('type') !== null || old('color') !== null || old('description') !== null))
                openModal(false);
            @endif
        })();
    </script>
@endsection

