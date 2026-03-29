@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    <div class="app-card glow-green-card mx-auto max-w-md rounded-2xl">
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
                <div class="relative" data-password-toggle>
                    <input id="password" name="password" type="password" required
                        class="input-control pr-12">
                    <button type="button"
                        class="absolute inset-y-0 right-3 my-auto inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-500 transition hover:bg-slate-100 hover:text-slate-700"
                        data-password-toggle-button
                        aria-controls="password" aria-label="Show password">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" data-icon-show viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" data-icon-hide viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"
                            stroke-linejoin="round" aria-hidden="true">
                            <path d="m3 3 18 18" />
                            <path d="M10.478 10.492a2 2 0 0 0 2.83 2.83" />
                            <path d="M9.88 5.09A10.94 10.94 0 0 1 12 4.875c4.697 0 8.714 2.872 10.372 6.938a1 1 0 0 1 0 .75 11.4 11.4 0 0 1-2.516 3.677" />
                            <path d="M6.61 6.609A11.282 11.282 0 0 0 1.628 11.813a1 1 0 0 0 0 .75C3.286 16.628 7.303 19.5 12 19.5c1.74 0 3.398-.394 4.879-1.102" />
                        </svg>
                    </button>
                </div>
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

