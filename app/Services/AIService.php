<?php

namespace App\Services;

use App\Exceptions\AIConfigurationException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AIService
{
    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{content: string, raw: array<mixed>}
     */
    public function chat(array $messages, ?string $modelOverride = null): array
    {
        $timeoutSeconds = (int) config('ai.request_timeout_seconds', 30);
        $provider = (string) config('ai.provider', 'gemini');

        $this->extendExecutionTime($timeoutSeconds + 15);

        return match ($provider) {
            'ollama' => $this->chatWithOllama($messages, $modelOverride, $timeoutSeconds),
            'gemini' => $this->chatWithGemini($messages, $modelOverride, $timeoutSeconds),
            default => throw new AIConfigurationException('AI provider is not configured. Set AI_PROVIDER to "gemini" or "ollama".'),
        };
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{content: string, raw: array<mixed>}
     */
    private function chatWithOllama(array $messages, ?string $modelOverride, int $timeoutSeconds): array
    {
        $baseUrl = (string) config('ai.ollama.base_url');
        $model = $modelOverride ?: (string) config('ai.ollama.model');

        if ($baseUrl === '' || $model === '') {
            throw new AIConfigurationException('AI is not configured. Please set OLLAMA_BASE_URL and OLLAMA_MODEL.');
        }

        $url = rtrim($baseUrl, '/') . '/api/chat';

        try {
            $response = Http::connectTimeout(min(5, max(1, $timeoutSeconds)))
                ->timeout($timeoutSeconds)
                ->acceptJson()
                ->asJson()
                ->post($url, [
                    'model' => $model,
                    'messages' => $messages,
                    'stream' => false,
                ])
                ->throw();
        } catch (ConnectionException $e) {
            throw new RuntimeException('Unable to connect to the AI service (Ollama). Ensure Ollama is running locally.', 0, $e);
        } catch (RequestException $e) {
            $status = $e->response?->status();
            $statusText = $status ? "HTTP {$status}" : 'HTTP error';

            throw new RuntimeException("AI service request failed ({$statusText}).", 0, $e);
        }

        $raw = $response->json();
        if (! is_array($raw)) {
            throw new RuntimeException('AI service returned an unexpected response.');
        }

        $content = (string) data_get($raw, 'message.content', '');

        return [
            'content' => $content,
            'raw' => $raw,
        ];
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{content: string, raw: array<mixed>}
     */
    private function chatWithGemini(array $messages, ?string $modelOverride, int $timeoutSeconds): array
    {
        $apiKey = (string) config('ai.gemini.api_key');
        $baseUrl = (string) config('ai.gemini.base_url');
        $primaryModel = $modelOverride ?: (string) config('ai.gemini.model');
        $fallbacks = config('ai.gemini.fallback_models', []);
        $fallbackModels = is_array($fallbacks) ? $fallbacks : [];

        $models = array_values(array_unique(array_filter(
            array_merge([$primaryModel], array_map('strval', $fallbackModels)),
            static fn (string $v): bool => trim($v) !== ''
        )));

        if ($apiKey === '' || $baseUrl === '' || count($models) === 0) {
            throw new AIConfigurationException('AI is not configured. Please set GEMINI_API_KEY and GEMINI_MODEL.');
        }

        [$systemInstruction, $contents] = $this->toGeminiPayload($messages);

        $payload = [
            'contents' => $contents,
        ];

        if ($systemInstruction !== null) {
            $payload['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ];
        }

        $lastException = null;
        foreach ($models as $model) {
            $url = rtrim($baseUrl, '/') . "/v1beta/models/{$model}:generateContent";

            try {
                $response = Http::connectTimeout(min(5, max(1, $timeoutSeconds)))
                    ->timeout($timeoutSeconds)
                    ->acceptJson()
                    ->asJson()
                    ->withQueryParameters(['key' => $apiKey])
                    ->post($url, $payload)
                    ->throw();
            } catch (ConnectionException $e) {
                $lastException = new RuntimeException('Unable to connect to the AI service (Gemini). Check your internet connection.', 0, $e);
                break;
            } catch (RequestException $e) {
                $status = $e->response?->status();
                $statusText = $status ? "HTTP {$status}" : 'HTTP error';

                $lastException = new RuntimeException("AI service request failed ({$statusText}).", 0, $e);

                // Retry other fallback models for common transient issues or model access issues.
                if (in_array($status, [403, 408, 429, 500, 502, 503, 504], true)) {
                    continue;
                }

                break;
            }

            $raw = $response->json();
            if (! is_array($raw)) {
                $lastException = new RuntimeException('AI service returned an unexpected response.');
                continue;
            }

            $parts = data_get($raw, 'candidates.0.content.parts', []);
            if (! is_array($parts)) {
                $parts = [];
            }

            $text = '';
            foreach ($parts as $part) {
                if (is_array($part) && isset($part['text'])) {
                    $text .= (string) $part['text'];
                }
            }

            return [
                'content' => trim($text),
                'raw' => $raw,
            ];
        }

        throw $lastException ?: new RuntimeException('AI service request failed.');
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{0: string|null, 1: array<int, array{role: string, parts: array<int, array{text: string}>}>}
     */
    private function toGeminiPayload(array $messages): array
    {
        $systemParts = [];
        $contents = [];

        foreach ($messages as $message) {
            $role = (string) ($message['role'] ?? 'user');
            $content = (string) ($message['content'] ?? '');
            if ($content === '') {
                continue;
            }

            if ($role === 'system') {
                $systemParts[] = $content;
                continue;
            }

            $geminiRole = $role === 'assistant' ? 'model' : 'user';
            $contents[] = [
                'role' => $geminiRole,
                'parts' => [
                    ['text' => $content],
                ],
            ];
        }

        $systemInstruction = count($systemParts) ? implode("\n\n", $systemParts) : null;

        // Gemini requires at least one user message.
        if (count($contents) === 0) {
            $contents[] = [
                'role' => 'user',
                'parts' => [
                    ['text' => 'Hello'],
                ],
            ];
        }

        return [$systemInstruction, $contents];
    }

    private function extendExecutionTime(int $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        try {
            @set_time_limit($seconds);
        } catch (\Throwable) {
            // ignore
        }

        try {
            @ini_set('max_execution_time', (string) $seconds);
        } catch (\Throwable) {
            // ignore
        }
    }
}
