<?php

namespace App\Services;

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
        $baseUrl = (string) config('ai.ollama.base_url');
        $model = $modelOverride ?: (string) config('ai.ollama.model');
        $timeoutSeconds = (int) config('ai.request_timeout_seconds', 30);

        $this->extendExecutionTime($timeoutSeconds + 15);

        if ($baseUrl === '' || $model === '') {
            throw new RuntimeException('AI is not configured. Please set OLLAMA_BASE_URL and OLLAMA_MODEL.');
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
