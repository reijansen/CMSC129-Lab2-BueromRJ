@extends('layouts.app')

@section('content')
    <div class="card mb-3">
        <h1 class="mb-3">Categories</h1>
        <form method="post" action="{{ route('categories.store') }}" class="row">
            @csrf
            <input type="text" name="name" placeholder="Category name" required>
            <select name="type" required>
                <option value="expense">Expense</option>
                <option value="income">Income</option>
            </select>
            <button type="submit">Add Category</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td>
                    <td>{{ ucfirst($category->type) }}</td>
                    <td>
                        <form method="post" action="{{ route('categories.destroy', $category) }}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Delete category?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No categories yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top: 12px;">
            {{ $categories->links() }}
        </div>
    </div>
@endsection

