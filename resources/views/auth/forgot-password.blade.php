@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
    <div class="mx-auto max-w-md rounded-2xl border border-emerald-100 bg-white p-6 shadow-sm">
        <h1 class="mb-1 text-2xl font-semibold text-slate-900">Forgot Password</h1>
        <p class="mb-6 text-sm text-slate-600">
            Enter your account email and we will send a password reset link.
        </p>

        <form method="post" action="{{ route('password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>

            <button type="submit" class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700">
                Send Reset Link
            </button>
        </form>

        <p class="mt-4 text-sm text-slate-600">
            <a href="{{ route('login') }}" class="font-medium text-emerald-700 hover:text-emerald-800">Back to login</a>
        </p>
    </div>
@endsection

