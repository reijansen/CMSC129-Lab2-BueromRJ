@extends('layouts.app')

@section('content')
    <div class="card" style="max-width: 460px; margin: 0 auto;">
        <h1 class="mb-3">Register</h1>
        <form method="post" action="{{ route('register.attempt') }}">
            @csrf
            <div class="mb-2">
                <label>Name</label><br>
                <input type="text" name="name" value="{{ old('name') }}" required style="width: 100%;">
            </div>
            <div class="mb-2">
                <label>Email</label><br>
                <input type="email" name="email" value="{{ old('email') }}" required style="width: 100%;">
            </div>
            <div class="mb-2">
                <label>Password</label><br>
                <input type="password" name="password" required style="width: 100%;">
            </div>
            <div class="mb-3">
                <label>Confirm Password</label><br>
                <input type="password" name="password_confirmation" required style="width: 100%;">
            </div>
            <div class="row">
                <button type="submit">Register</button>
                <a class="btn btn-secondary" href="{{ route('login') }}">Back to Login</a>
            </div>
        </form>
    </div>
@endsection

