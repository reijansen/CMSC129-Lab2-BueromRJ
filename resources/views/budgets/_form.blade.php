@php
    $budget = $budget ?? null;
@endphp

<div class="space-y-5">
    <div>
        <label for="category_id" class="label-control">Category</label>
        <select
            id="category_id"
            name="category_id"
            class="input-control"
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
        <label for="title" class="label-control">Title</label>
        <input
            id="title"
            name="title"
            type="text"
            value="{{ old('title', $budget?->title) }}"
            class="input-control"
            required
        >
    </div>

    <div>
        <label for="allocated_amount" class="label-control">Allocated Amount</label>
        <input
            id="allocated_amount"
            name="allocated_amount"
            type="number"
            min="0"
            step="0.01"
            value="{{ old('allocated_amount', $budget?->allocated_amount) }}"
            class="input-control"
            required
        >
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <label for="period_start" class="label-control">Period Start</label>
            <input
                id="period_start"
                name="period_start"
                type="date"
                value="{{ old('period_start', optional($budget?->period_start)->format('Y-m-d')) }}"
                class="input-control"
                required
            >
        </div>
        <div>
            <label for="period_end" class="label-control">Period End</label>
            <input
                id="period_end"
                name="period_end"
                type="date"
                value="{{ old('period_end', optional($budget?->period_end)->format('Y-m-d')) }}"
                class="input-control"
                required
            >
        </div>
    </div>

    <div>
        <label for="status" class="label-control">Status</label>
        <select
            id="status"
            name="status"
            class="input-control"
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
        <label for="notes" class="label-control">Notes</label>
        <textarea
            id="notes"
            name="notes"
            rows="4"
            class="input-control"
        >{{ old('notes', $budget?->notes) }}</textarea>
    </div>
</div>


