@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="app-card mx-auto max-w-md rounded-2xl">
        <h1 class="mb-1 text-2xl font-semibold text-slate-900">Login</h1>
        <p class="mb-6 text-sm text-slate-600">Sign in to continue to your finance dashboard.</p>

        <form method="post" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    class="input-control">
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                <input id="password" name="password" type="password" required
                    class="input-control">
            </div>

            <button type="submit" class="btn-primary w-full py-2.5">
                Login
            </button>
        </form>

        <div class="mt-4 flex items-center justify-between text-sm">
            <a href="{{ route('password.request') }}" class="text-emerald-700 hover:text-emerald-800">Forgot password?</a>
            <a href="{{ route('register') }}" class="text-slate-600 hover:text-slate-900">No account? Sign up</a>
        </div>
    </div>
@endsection

