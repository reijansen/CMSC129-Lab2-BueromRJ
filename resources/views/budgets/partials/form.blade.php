<div class="mb-2">
    <label>Name</label><br>
    <input type="text" name="name" value="{{ old('name', $budget?->name) }}" required style="width: 100%;">
</div>
<div class="mb-2">
    <label>Category</label><br>
    <select name="category_id" style="width: 100%;">
        <option value="">No category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $budget?->category_id) === (string) $category->id)>
                {{ $category->name }} ({{ $category->type }})
            </option>
        @endforeach
    </select>
</div>
<div class="mb-2">
    <label>Amount Limit</label><br>
    <input type="number" step="0.01" min="0" name="amount_limit" value="{{ old('amount_limit', $budget?->amount_limit) }}" required style="width: 100%;">
</div>
<div class="mb-2">
    <label>Period Start</label><br>
    <input type="date" name="period_start" value="{{ old('period_start', optional($budget?->period_start)->format('Y-m-d')) }}" style="width: 100%;">
</div>
<div class="mb-3">
    <label>Period End</label><br>
    <input type="date" name="period_end" value="{{ old('period_end', optional($budget?->period_end)->format('Y-m-d')) }}" style="width: 100%;">
</div>

