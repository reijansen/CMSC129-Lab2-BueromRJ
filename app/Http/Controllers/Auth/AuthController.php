<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SupabaseAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(private readonly SupabaseAuthService $supabaseAuthService)
    {
    }

    public function showLogin(): View|RedirectResponse
    {
        if (session()->has('app_user_id')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $session = $this->supabaseAuthService->signInWithPassword($credentials['email'], $credentials['password']);
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors([
                'email' => $exception->getMessage(),
            ]);
        }

        $this->persistSession($request, $session);

        return redirect()->route('dashboard');
    }

    public function showRegister(): View|RedirectResponse
    {
        if (session()->has('app_user_id')) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        try {
            $response = $this->supabaseAuthService->signUp($credentials['email'], $credentials['password']);
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors([
                'email' => $exception->getMessage(),
            ]);
        }

        if (! isset($response['access_token'])) {
            return redirect()->route('login')->with('status', 'Account created. Check your email for confirmation, then sign in.');
        }

        $this->persistSession($request, $response, $credentials['name']);

        return redirect()->route('dashboard');
    }

    public function showForgotPassword(): View|RedirectResponse
    {
        if (session()->has('app_user_id')) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    public function sendForgotPassword(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'email' => ['required', 'email'],
        ]);

        try {
            $this->supabaseAuthService->sendPasswordResetLink($payload['email']);
        } catch (RuntimeException $exception) {
            return back()->withInput()->withErrors([
                'email' => $exception->getMessage(),
            ]);
        }

        return back()->with('success', 'If an account exists, a reset email has been sent.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $accessToken = (string) $request->session()->get('sb_access_token', '');

        if ($accessToken !== '') {
            $this->supabaseAuthService->logout($accessToken);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function persistSession(Request $request, array $sessionPayload, ?string $name = null): void
    {
        $supabaseUser = $sessionPayload['user'] ?? [];
        $supabaseUserId = (string) ($supabaseUser['id'] ?? '');
        $email = (string) ($supabaseUser['email'] ?? '');

        $localUser = User::updateOrCreate(
            ['supabase_user_id' => $supabaseUserId],
            [
                'name' => $name ?? Str::before($email, '@') ?: 'Supabase User',
                'email' => $email,
                'password' => Hash::make(Str::random(40)),
            ]
        );

        $request->session()->regenerate();
        $request->session()->put([
            'app_user_id' => $localUser->id,
            'supabase_user_id' => $supabaseUserId,
            'sb_access_token' => $sessionPayload['access_token'],
            'sb_refresh_token' => $sessionPayload['refresh_token'],
            'sb_expires_at' => now()->timestamp + (int) $sessionPayload['expires_in'],
        ]);
    }
}
