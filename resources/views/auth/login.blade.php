@extends('layouts.app')

@section('content')
    <div class="card" style="max-width: 460px; margin: 0 auto;">
        <h1 class="mb-3">Login</h1>
        <form method="post" action="{{ route('login.attempt') }}">
            @csrf
            <div class="mb-2">
                <label>Email</label><br>
                <input type="email" name="email" value="{{ old('email') }}" required style="width: 100%;">
            </div>
            <div class="mb-3">
                <label>Password</label><br>
                <input type="password" name="password" required style="width: 100%;">
            </div>
            <div class="row">
                <button type="submit">Login</button>
                <a class="btn btn-secondary" href="{{ route('register') }}">Create Account</a>
            </div>
        </form>
    </div>
@endsection

