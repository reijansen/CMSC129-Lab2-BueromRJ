<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Finko')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    @php
        $headerUser = null;
        if (session()->has('app_user_id')) {
            $headerUser = \App\Models\User::query()->find((int) session('app_user_id'));
        }
    @endphp
    <header class="sticky top-0 z-50 border-b border-emerald-100 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-6 py-4">
            <div class="flex items-center gap-3">
                <button type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50/70 text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
                    data-drawer-open aria-label="Open navigation menu">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-lg font-semibold text-slate-900">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-md bg-emerald-600 text-xs font-bold text-white">F</span>
                    Finko
                </a>
            </div>
            @if ($headerUser)
                <p class="text-sm font-medium text-slate-600">
                    Hello, {{ \Illuminate\Support\Str::before($headerUser->name, ' ') ?: $headerUser->name }}!
                </p>
            @endif
        </div>
    </header>

    <div class="fixed inset-x-0 bottom-0 top-[73px] z-30 hidden bg-slate-900/30" data-drawer-overlay></div>

    <aside
        class="fixed left-0 top-[73px] z-40 h-[calc(100vh-73px)] w-72 -translate-x-full border-r border-slate-200 bg-white p-4 shadow-lg transition-all duration-300 ease-in-out lg:translate-x-0 lg:w-16"
        data-drawer>
        <div class="drawer-inner flex h-full flex-col overflow-hidden">
            <div class="flex h-full flex-col px-2 py-1">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500 lg:hidden" data-drawer-title>Navigation</p>
                <nav class="space-y-1 text-sm">
                    <a href="{{ route('dashboard') }}"
                        class="drawer-item drawer-tooltip-target {{ request()->routeIs('dashboard') ? 'bg-emerald-100 text-emerald-900' : 'text-slate-700 hover:bg-slate-100' }} flex items-center gap-3 rounded-lg px-3 py-2 font-medium"
                        data-tooltip="Dashboard">
                        <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icon h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 12 12 4.75 20.25 12v7.25a.75.75 0 0 1-.75.75h-5.25v-5.25h-4.5V20h-5.25a.75.75 0 0 1-.75-.75V12Z" />
                        </svg>
                        <span class="truncate lg:hidden" data-drawer-label>Dashboard</span>
                    </a>
                    <a href="{{ route('categories.index') }}"
                        class="drawer-item drawer-tooltip-target {{ request()->routeIs('categories.*') ? 'bg-emerald-100 text-emerald-900' : 'text-slate-700 hover:bg-slate-100' }} flex items-center gap-3 rounded-lg px-3 py-2 font-medium"
                        data-tooltip="Categories">
                        <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icon h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.75 5.75A1.75 1.75 0 0 1 6.5 4h11A1.75 1.75 0 0 1 19.25 5.75v12.5A1.75 1.75 0 0 1 17.5 20h-11a1.75 1.75 0 0 1-1.75-1.75V5.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 8.5h8M8 12h8M8 15.5h5" />
                        </svg>
                        <span class="truncate lg:hidden" data-drawer-label>Categories</span>
                    </a>
                    <a href="{{ route('budgets.index') }}"
                        class="drawer-item drawer-tooltip-target {{ request()->routeIs('budgets.*') ? 'bg-emerald-100 text-emerald-900' : 'text-slate-700 hover:bg-slate-100' }} flex items-center gap-3 rounded-lg px-3 py-2 font-medium"
                        data-tooltip="Budgets">
                        <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icon h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25A2.25 2.25 0 0 1 4.5 6h15a2.25 2.25 0 0 1 2.25 2.25v7.5A2.25 2.25 0 0 1 19.5 18h-15a2.25 2.25 0 0 1-2.25-2.25v-7.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 10.5h19.5M6.75 14.25h3" />
                        </svg>
                        <span class="truncate lg:hidden" data-drawer-label>Budgets</span>
                    </a>
                    <a href="{{ route('transactions.index') }}"
                        class="drawer-item drawer-tooltip-target {{ request()->routeIs('transactions.*') ? 'bg-emerald-100 text-emerald-900' : 'text-slate-700 hover:bg-slate-100' }} flex items-center gap-3 rounded-lg px-3 py-2 font-medium"
                        data-tooltip="Transactions">
                        <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icon h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4.5 7.5h15m-15 4.5h15m-15 4.5h9" />
                        </svg>
                        <span class="truncate lg:hidden" data-drawer-label>Transactions</span>
                    </a>
                </nav>

                <div class="mt-auto border-t border-slate-200 pt-3">
                    <a href="{{ route('profile.show') }}"
                        class="drawer-item drawer-tooltip-target {{ request()->routeIs('profile.show') ? 'bg-emerald-100 text-emerald-900' : 'text-slate-700 hover:bg-slate-100' }} flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium"
                        data-tooltip="Profile">
                        <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icon h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75a17.933 17.933 0 0 1-7.5-1.632Z" />
                        </svg>
                        <span class="truncate lg:hidden" data-drawer-label>Profile</span>
                    </a>
                    @if (session()->has('app_user_id'))
                        <form action="{{ route('logout') }}" method="post" class="mt-1">
                            @csrf
                            <button type="submit"
                                class="drawer-item drawer-tooltip-target flex w-full items-center gap-3 rounded-lg px-3 py-2 text-left text-sm font-medium text-slate-700 transition hover:bg-slate-100"
                                data-tooltip="Logout">
                                <svg xmlns="http://www.w3.org/2000/svg" class="drawer-icon h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12h9m0 0-3-3m3 3-3 3" />
                                </svg>
                                <span class="truncate lg:hidden" data-drawer-label>Logout</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </aside>

    <div class="mx-auto max-w-7xl px-6 py-6 lg:pl-20">
        <main class="space-y-4 transition-[margin] duration-300 ease-in-out" data-main-content>
            @include('partials.flash-messages')
            @yield('content')
        </main>
    </div>
</body>
</html>
