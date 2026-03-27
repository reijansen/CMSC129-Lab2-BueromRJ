@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
    <div class="app-card mx-auto max-w-md rounded-2xl">
        <h1 class="mb-1 text-2xl font-semibold text-slate-900">Forgot Password</h1>
        <p class="mb-6 text-sm text-slate-600">
            Enter your account email and we will send a password reset link.
        </p>

        <form method="post" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    class="input-control">
            </div>

            <button type="submit" class="btn-primary w-full py-2.5">
                Send Reset Link
            </button>
        </form>

        <p class="mt-4 text-sm text-slate-600">
            <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:text-emerald-800">Back to login</a>
        </p>
    </div>
@endsection

