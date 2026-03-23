@php
    $budget = $budget ?? null;
@endphp

<div class="space-y-5">
    <div>
        <label for="category_id" class="mb-1 block text-sm font-medium text-slate-700">Category</label>
        <select
            id="category_id"
            name="category_id"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
            <option value="">Select a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $budget?->category_id) === (string) $category->id)>
                    {{ $category->name }} ({{ ucfirst($category->type) }})
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="title" class="mb-1 block text-sm font-medium text-slate-700">Title</label>
        <input
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $budget?->title) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
    </div>

    <div>
        <label for="allocated_amount" class="mb-1 block text-sm font-medium text-slate-700">Allocated Amount</label>
        <input
            id="allocated_amount"
            name="allocated_amount"
            type="number"
            min="0"
            step="0.01"
            value="{{ old('allocated_amount', $budget?->allocated_amount) }}"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="period_start" class="mb-1 block text-sm font-medium text-slate-700">Period Start</label>
            <input
                id="period_start"
                name="period_start"
                type="date"
                value="{{ old('period_start', optional($budget?->period_start)->format('Y-m-d')) }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                required
            >
        </div>
        <div>
            <label for="period_end" class="mb-1 block text-sm font-medium text-slate-700">Period End</label>
            <input
                id="period_end"
                name="period_end"
                type="date"
                value="{{ old('period_end', optional($budget?->period_end)->format('Y-m-d')) }}"
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                required
            >
        </div>
    </div>

    <div>
        <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status</label>
        <select
            id="status"
            name="status"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
            required
        >
            @foreach (['active', 'completed', 'exceeded', 'archived'] as $status)
                <option value="{{ $status }}" @selected(old('status', $budget?->status) === $status)>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="notes" class="mb-1 block text-sm font-medium text-slate-700">Notes</label>
        <textarea
            id="notes"
            name="notes"
            rows="4"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
        >{{ old('notes', $budget?->notes) }}</textarea>
    </div>
</div>

