@extends('layouts.app')

@section('content')
    <div class="card">
        <h1 class="mb-3">Edit Budget</h1>
        <form method="post" action="{{ route('budgets.update', $budget) }}">
            @csrf
            @method('put')
            @include('budgets.partials.form', ['budget' => $budget])
            <div class="row">
                <button type="submit">Update</button>
                <a class="btn btn-secondary" href="{{ route('budgets.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection

