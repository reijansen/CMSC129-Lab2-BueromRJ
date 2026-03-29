@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="app-card">
        <div class="mb-4 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Categories</h1>
                <p class="text-sm text-slate-500">Manage your income and expense category types.</p>
            </div>
            <button type="button" class="btn-primary" data-open-category-modal>
                New Category
            </button>
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
                                <button type="button" data-open-category-modal class="mt-3 inline-flex btn-primary px-3 py-1.5 text-xs">
                                    Create Category
                                </button>
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

    <script>
        (() => {
            const modal = document.querySelector('[data-category-modal]');
            if (!modal) return;

            const openButtons = document.querySelectorAll('[data-open-category-modal]');
            const closeButtons = modal.querySelectorAll('[data-close-category-modal]');

            const openModal = () => {
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
                button.addEventListener('click', openModal);
            });

            closeButtons.forEach((button) => {
                button.addEventListener('click', closeModal);
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeModal();
                }
            });

            @if ($errors->any() && (old('name') !== null || old('type') !== null || old('color') !== null || old('description') !== null))
                openModal();
            @endif
        })();
    </script>
@endsection

