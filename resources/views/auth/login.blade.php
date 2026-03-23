@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-2xl font-semibold text-slate-900">Login</h1>
        <p class="mb-6 text-sm text-slate-600">Sign in to continue to your finance dashboard.</p>

        <form method="post" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700">
                Login
            </button>
        </form>

        <div class="mt-4 flex items-center justify-between text-sm">
            <a href="{{ route('password.request') }}" class="text-emerald-700 hover:text-emerald-800">Forgot password?</a>
            <a href="{{ route('register') }}" class="text-slate-600 hover:text-slate-900">No account? Sign up</a>
        </div>
    </div>
@endsection

