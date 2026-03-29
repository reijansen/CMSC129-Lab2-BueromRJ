<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Finko')</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('finko-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const theme = savedTheme === 'dark' || savedTheme === 'light'
                ? savedTheme
                : (prefersDark ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', theme === 'dark');
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 text-slate-800">
    <header class="border-b border-emerald-100 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('landing') }}" class="text-xl font-semibold tracking-tight text-emerald-700">finko</a>
            <nav class="flex items-center gap-3 text-sm">
                <a href="{{ route('login') }}" class="btn-secondary border-emerald-200 text-emerald-700 hover:bg-emerald-50">Login</a>
                <a href="{{ route('register') }}" class="btn-primary">Sign Up</a>
                <button type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50/70 text-emerald-700 transition hover:bg-emerald-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300"
                    data-theme-toggle
                    aria-label="Switch to dark mode"
                    title="Toggle theme">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" aria-hidden="true" data-theme-icon="sun">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v1.5m0 15V21m8.485-8.485H19m-14 0H3.515M18.364 5.636l-1.06 1.06M6.697 17.303l-1.06 1.06m0-12.727 1.06 1.06m10.607 10.607 1.06 1.06M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                    <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2" aria-hidden="true" data-theme-icon="moon">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 12.79A9 9 0 1 1 11.21 3a7.5 7.5 0 0 0 9.79 9.79Z" />
                    </svg>
                </button>
            </nav>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-6 py-10">
        @include('partials.flash-messages')
        @yield('content')
    </main>

    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-3 px-6 py-5 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <p>&copy; {{ now()->year }} Finko. Student Budget &amp; Finance Tracker.</p>
            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="hover:text-emerald-700">Login</a>
                <a href="{{ route('register') }}" class="hover:text-emerald-700">Sign Up</a>
            </div>
        </div>
    </footer>
</body>
</html>
