<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Finko')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <header class="sticky top-0 z-20 border-b border-emerald-100 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-lg font-semibold text-slate-900">
                <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-emerald-600 text-xs font-bold text-white">F</span>
                Finko
            </a>
            <div class="flex items-center gap-3">
                @if (session()->has('app_user_id'))
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button type="submit" class="btn-secondary">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary">
                        Login
                    </a>
                @endif
            </div>
        </div>
    </header>

    <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-6 py-6 lg:grid-cols-[250px_minmax(0,1fr)]">
        <aside class="app-card h-fit p-4">
            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">Navigation</p>
            <nav class="space-y-1 text-sm">
                <a href="{{ route('dashboard') }}"
                    class="{{ request()->routeIs('dashboard') ? 'bg-emerald-100 text-emerald-900' : 'text-slate-700 hover:bg-slate-100' }} block rounded-lg px-3 py-2 font-medium">
                    Dashboard
                </a>
                <a href="{{ route('categories.index') }}"
                    class="{{ request()->routeIs('categories.*') ? 'bg-emerald-100 text-emerald-900' : 'text-slate-700 hover:bg-slate-100' }} block rounded-lg px-3 py-2 font-medium">
                    Categories
                </a>
                <a href="{{ route('budgets.index') }}"
                    class="{{ request()->routeIs('budgets.*') ? 'bg-emerald-100 text-emerald-900' : 'text-slate-700 hover:bg-slate-100' }} block rounded-lg px-3 py-2 font-medium">
                    Budgets
                </a>
                <a href="{{ route('transactions.index') }}"
                    class="{{ request()->routeIs('transactions.*') ? 'bg-emerald-100 text-emerald-900' : 'text-slate-700 hover:bg-slate-100' }} block rounded-lg px-3 py-2 font-medium">
                    Transactions
                </a>
            </nav>
            <div class="mt-4 border-t border-slate-200 pt-3 text-xs text-slate-500">
                Student Budget & Finance Tracker
            </div>
        </aside>

        <main class="space-y-4">
            @include('partials.flash-messages')
            @yield('content')
        </main>
    </div>
</body>
</html>
