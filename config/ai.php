<?php

return [
    'provider' => env('AI_PROVIDER', 'ollama'),

    'request_timeout_seconds' => (int) env('AI_REQUEST_TIMEOUT_SECONDS', 30),

    'ollama' => [
        'base_url' => rtrim((string) env('OLLAMA_BASE_URL', 'http://127.0.0.1:11434'), '/'),
        'model' => (string) env('OLLAMA_MODEL', 'qwen2.5:3b'),
    ],
];

