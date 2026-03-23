<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f7fb; color: #111827; }
        nav { background: #111827; color: #fff; padding: 12px 24px; display: flex; gap: 16px; align-items: center; }
        nav a { color: #fff; text-decoration: none; }
        nav .grow { flex: 1; }
        main { max-width: 980px; margin: 24px auto; padding: 0 16px; }
        .card { background: #fff; border-radius: 8px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .row { display: flex; gap: 12px; flex-wrap: wrap; }
        input, select { padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; }
        button, .btn { background: #2563eb; color: #fff; border: 0; border-radius: 6px; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-secondary { background: #475569; }
        .btn-danger { background: #dc2626; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e5e7eb; text-align: left; padding: 8px; }
        .alert { padding: 10px 12px; border-radius: 6px; margin-bottom: 12px; }
        .alert-ok { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .mb-2 { margin-bottom: 12px; }
        .mb-3 { margin-bottom: 16px; }
    </style>
</head>
<body>
<nav>
    <a href="{{ route('budgets.index') }}">Budgets</a>
    <a href="{{ route('categories.index') }}">Categories</a>
    <div class="grow"></div>
    @if(session('app_user_id'))
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-secondary">Logout</button>
        </form>
    @else
        <a href="{{ route('login') }}">Login</a>
    @endif
</nav>
<main>
    @if(session('status'))
        <div class="alert alert-ok">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</main>
</body>
</html>

