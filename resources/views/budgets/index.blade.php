@extends('layouts.app')

@section('content')
    <div class="card mb-3">
        <h1>Budgets</h1>
        <form class="row" method="get" action="{{ route('budgets.index') }}">
            <input type="text" name="q" placeholder="Search budget name" value="{{ $search }}">
            <select name="category_id">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected($categoryId === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit">Filter</button>
            <a href="{{ route('budgets.create') }}" class="btn">Create Budget</a>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Limit</th>
                <th>Period</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($budgets as $budget)
                <tr>
                    <td>{{ $budget->name }}</td>
                    <td>{{ $budget->category?->name ?? '-' }}</td>
                    <td>{{ number_format((float) $budget->amount_limit, 2) }}</td>
                    <td>
                        {{ $budget->period_start?->format('Y-m-d') ?? '-' }}
                        to
                        {{ $budget->period_end?->format('Y-m-d') ?? '-' }}
                    </td>
                    <td class="row">
                        <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-secondary">Edit</a>
                        <form method="post" action="{{ route('budgets.destroy', $budget) }}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Delete this budget?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No budgets yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top: 12px;">
            {{ $budgets->links() }}
        </div>
    </div>
@endsection

