@php
    $category = $category ?? null;
@endphp

<div class="space-y-5">
    <div>
        <label for="name" class="label-control">Name</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $category?->name) }}"
            class="input-control"
            required
        >
    </div>

    <div>
        <label for="type" class="label-control">Type</label>
        <select
            id="type"
            name="type"
            class="input-control"
            required
        >
            @foreach (['income' => 'Income', 'expense' => 'Expense', 'both' => 'Both'] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $category?->type) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label for="color" class="label-control">Color</label>
        <input
            id="color"
            name="color"
            type="text"
            value="{{ old('color', $category?->color) }}"
            placeholder="e.g. #0ea5e9 or sky"
            class="input-control"
        >
    </div>

    <div>
        <label for="description" class="label-control">Description</label>
        <textarea
            id="description"
            name="description"
            rows="4"
            class="input-control"
        >{{ old('description', $category?->description) }}</textarea>
    </div>
</div>


