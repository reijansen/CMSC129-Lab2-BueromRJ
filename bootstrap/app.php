<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'supabase.auth' => \App\Http\Middleware\EnsureSupabaseAuthenticated::class,
            'supabase.guest' => \App\Http\Middleware\EnsureSupabaseGuest::class,
        ]);

        if (env('APP_ENV') === 'local') {
            $middleware->validateCsrfTokens([
                'api/ai/*',
            ]);
        }
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(function (QueryException $exception, Request $request) {
            $message = (string) $exception->getMessage();

            // Common Supabase/Postgres connectivity errors (network reset, pooler misconfig, paused project, etc.).
            $isConnectionError = str_contains($message, 'SQLSTATE[08006]')
                || str_contains($message, 'Connection reset by peer')
                || str_contains($message, 'server closed the connection unexpectedly');

            if (! $isConnectionError) {
                return null;
            }

            $safeMessage = 'Database is unavailable. Check your Supabase DB connection settings in .env (host/port/username/sslmode) and try again.';

            if ($request->expectsJson()) {
                return response()->json(['error' => $safeMessage], 503);
            }

            return response($safeMessage, 503);
        });
    })->create();
