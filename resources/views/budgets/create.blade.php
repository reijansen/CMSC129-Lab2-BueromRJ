@extends('layouts.app')

@section('content')
    <div class="card">
        <h1 class="mb-3">Create Budget</h1>
        <form method="post" action="{{ route('budgets.store') }}">
            @csrf
            @include('budgets.partials.form', ['budget' => null])
            <div class="row">
                <button type="submit">Save</button>
                <a class="btn btn-secondary" href="{{ route('budgets.index') }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection

