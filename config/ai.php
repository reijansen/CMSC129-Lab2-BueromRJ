<?php

return [
    // Supported providers: "ollama", "gemini"
    'provider' => env('AI_PROVIDER', 'gemini'),

    'request_timeout_seconds' => (int) env('AI_REQUEST_TIMEOUT_SECONDS', 30),

    'ollama' => [
        'base_url' => rtrim((string) env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'), '/'),
        'model' => (string) env('OLLAMA_MODEL', 'qwen2.5:3b'),
        'router_model' => (string) env('OLLAMA_ROUTER_MODEL', env('OLLAMA_MODEL', 'qwen2.5:3b')),
    ],

    // Google Gemini API (Google AI Studio)
    // Docs: https://ai.google.dev
    'gemini' => [
        'api_key' => (string) env('GEMINI_API_KEY', ''),
        'base_url' => rtrim((string) env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'), '/'),
        // Example models: "gemini-1.5-flash", "gemini-1.5-pro"
        'model' => (string) env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'router_model' => (string) env('GEMINI_ROUTER_MODEL', env('GEMINI_MODEL', 'gemini-1.5-flash')),
        // Comma-separated list of model fallbacks (tried in order after GEMINI_MODEL).
        // Example: "gemini-1.5-flash,gemini-1.5-pro"
        'fallback_models' => array_values(array_filter(array_map(
            static fn (string $v): string => trim($v),
            explode(',', (string) env('GEMINI_FALLBACK_MODELS', ''))
        ), static fn (string $v): bool => $v !== '')),
    ],
];
