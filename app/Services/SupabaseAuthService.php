<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SupabaseAuthService
{
    public function signInWithPassword(string $email, string $password): array
    {
        $response = $this->baseRequest()
            ->post($this->authUrl('/token?grant_type=password'), [
                'email' => $email,
                'password' => $password,
            ]);

        return $this->parseResponse($response->json(), $response->successful());
    }

    public function signUp(string $email, string $password): array
    {
        $response = $this->baseRequest()
            ->post($this->authUrl('/signup'), [
                'email' => $email,
                'password' => $password,
            ]);

        return $this->parseResponse($response->json(), $response->successful());
    }

    public function refresh(string $refreshToken): array
    {
        $response = $this->baseRequest()
            ->post($this->authUrl('/token?grant_type=refresh_token'), [
                'refresh_token' => $refreshToken,
            ]);

        return $this->parseResponse($response->json(), $response->successful());
    }

    public function logout(string $accessToken): void
    {
        $this->baseRequest()
            ->withToken($accessToken)
            ->post($this->authUrl('/logout'));
    }

    private function baseRequest()
    {
        $anonKey = (string) config('services.supabase.anon_key');

        return Http::withHeaders([
            'apikey' => $anonKey,
            'Accept' => 'application/json',
        ]);
    }

    private function authUrl(string $path): string
    {
        return rtrim((string) config('services.supabase.url'), '/').'/auth/v1'.$path;
    }

    private function parseResponse(array $payload, bool $successful): array
    {
        if (! $successful) {
            $message = (string) ($payload['msg'] ?? $payload['error_description'] ?? $payload['error'] ?? 'Supabase auth request failed.');
            throw new RuntimeException($message);
        }

        return $payload;
    }
}

