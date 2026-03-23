<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Finko')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('dashboard') }}" class="text-lg font-semibold text-slate-900">Finko</a>
            <nav class="flex items-center gap-4 text-sm">
                <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-slate-900">Dashboard</a>
                <a href="{{ route('categories.index') }}" class="text-slate-600 hover:text-slate-900">Categories</a>
                <a href="{{ route('budgets.index') }}" class="text-slate-600 hover:text-slate-900">Budgets</a>
                <a href="{{ route('transactions.index') }}" class="text-slate-600 hover:text-slate-900">Transactions</a>
                @if (session()->has('app_user_id'))
                    <form action="{{ route('logout') }}" method="post">
                        @csrf
                        <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-50">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-50">
                        Login
                    </a>
                @endif
            </nav>
        </div>
    </header>

    <div class="mx-auto grid max-w-6xl grid-cols-1 gap-6 px-6 py-6 md:grid-cols-[220px_minmax(0,1fr)]">
        <aside class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Menu</p>
            <div class="space-y-2 text-sm">
                <a href="{{ route('dashboard') }}" class="block rounded-md px-3 py-2 hover:bg-slate-100">Overview</a>
                <a href="{{ route('categories.index') }}" class="block rounded-md px-3 py-2 hover:bg-slate-100">Categories</a>
            </div>
        </aside>

        <main class="space-y-4">
            @if (session('success'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
