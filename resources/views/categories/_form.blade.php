@php
    $category = $category ?? null;
@endphp

<div class="space-y-5">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $category?->name) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
            required
        >
    </div>

    <div>
        <label for="type" class="mb-1 block text-sm font-medium text-slate-700">Type</label>
        <select
            id="type"
            name="type"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
            required
        >
            @foreach (['income' => 'Income', 'expense' => 'Expense', 'both' => 'Both'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $category?->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="color" class="mb-1 block text-sm font-medium text-slate-700">Color</label>
        <input
            id="color"
            name="color"
            type="text"
            value="{{ old('color', $category?->color) }}"
            placeholder="e.g. #0ea5e9 or sky"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
        >
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
        >{{ old('description', $category?->description) }}</textarea>
    </div>
</div>

