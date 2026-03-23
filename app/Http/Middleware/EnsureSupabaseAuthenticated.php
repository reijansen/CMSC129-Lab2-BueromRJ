<?php

namespace App\Http\Middleware;

use App\Services\SupabaseAuthService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnsureSupabaseAuthenticated
{
    public function __construct(private readonly SupabaseAuthService $supabaseAuthService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $accessToken = (string) $request->session()->get('sb_access_token', '');
        $refreshToken = (string) $request->session()->get('sb_refresh_token', '');
        $expiresAt = (int) $request->session()->get('sb_expires_at', 0);
        $appUserId = (int) $request->session()->get('app_user_id', 0);

        if ($accessToken === '' || $refreshToken === '' || $appUserId === 0) {
            return redirect()->route('login');
        }

        if ($expiresAt <= now()->addMinute()->timestamp) {
            try {
                $session = $this->supabaseAuthService->refresh($refreshToken);
            } catch (Throwable) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Your session expired. Please log in again.',
                ]);
            }

            $request->session()->put([
                'sb_access_token' => $session['access_token'],
                'sb_refresh_token' => $session['refresh_token'],
                'sb_expires_at' => now()->timestamp + (int) $session['expires_in'],
            ]);
        }

        $request->attributes->set('app_user_id', $appUserId);

        return $next($request);
    }
}

