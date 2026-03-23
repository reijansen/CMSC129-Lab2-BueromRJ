<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Finko')</title>
</head>
<body>
    <header>
        <nav>
            <strong>Finko</strong>
            <span>Navbar Placeholder</span>
        </nav>
    </header>

    <div style="display: flex; gap: 16px;">
        <aside style="min-width: 200px;">
            <p>Sidebar Placeholder</p>
        </aside>

        <main style="flex: 1;">
            @yield('content')
        </main>
    </div>
</body>
</html>
