@extends('layouts.guest')

@section('title', 'Sign Up')

@section('content')
    <div class="app-card mx-auto max-w-md rounded-2xl">
        <h1 class="mb-1 text-2xl font-semibold text-slate-900">Create Account</h1>
        <p class="mb-6 text-sm text-slate-600">Start organizing your student finances with Finko.</p>

        <form method="post" action="{{ route('register.attempt') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                    class="input-control">
            </div>

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

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirm Password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="input-control">
            </div>

            <button type="submit" class="btn-primary w-full py-2.5">
                Create Account
            </button>
        </form>

        <p class="mt-4 text-sm text-slate-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:text-emerald-800">Login</a>
        </p>
    </div>
@endsection

