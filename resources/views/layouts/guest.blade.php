<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Finko')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-800">
    <header class="border-b border-emerald-100 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
            <a href="{{ route('landing') }}" class="text-xl font-semibold tracking-tight text-emerald-700">finko</a>
            <nav class="flex items-center gap-3 text-sm">
                <a href="{{ route('login') }}" class="btn-secondary border-emerald-200 text-emerald-700 hover:bg-emerald-50">Login</a>
                <a href="{{ route('register') }}" class="btn-primary">Sign Up</a>
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-6 py-10">
        @include('partials.flash-messages')
        @yield('content')
    </main>
</body>
</html>
