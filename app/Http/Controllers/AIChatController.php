<?php

namespace App\Http\Controllers;

use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AIChatController extends Controller
{
    private const SESSION_HISTORY_KEY = 'ai_chat_history';
    private const MAX_MESSAGES = 10;

    public function __construct(
        private readonly AIService $aiService
    ) {}

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $history = $this->loadHistory($request);
        $history[] = [
            'role' => 'user',
            'content' => $validated['message'],
        ];

        $history = $this->trimHistory($history);

        try {
            $result = $this->aiService->chat($history);
        } catch (RuntimeException $e) {
            return response()->json([
                'error' => 'AI service is currently unavailable. Please try again.',
            ], 502);
        }

        $reply = trim((string) ($result['content'] ?? ''));
        if ($reply === '') {
            $reply = $this->fallbackReply();
        }

        $history[] = [
            'role' => 'assistant',
            'content' => $reply,
        ];

        $history = $this->trimHistory($history);
        $request->session()->put(self::SESSION_HISTORY_KEY, $history);

        return response()->json([
            'reply' => $reply,
            'history' => $history,
        ]);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget(self::SESSION_HISTORY_KEY);

        return response()->json([
            'reply' => 'Chat history cleared.',
            'history' => [],
        ]);
    }

    /**
     * @return array<int, array{role: string, content: string}>
     */
    private function loadHistory(Request $request): array
    {
        $history = $request->session()->get(self::SESSION_HISTORY_KEY, []);
        if (! is_array($history)) {
            $history = [];
        }

        $normalized = [];
        foreach ($history as $message) {
            if (! is_array($message)) {
                continue;
            }

            $role = $message['role'] ?? null;
            $content = $message['content'] ?? null;

            if (! is_string($role) || ! is_string($content) || $content === '') {
                continue;
            }

            $normalized[] = [
                'role' => $role,
                'content' => $content,
            ];
        }

        if ($normalized === [] || ($normalized[0]['role'] ?? null) !== 'system') {
            array_unshift($normalized, [
                'role' => 'system',
                'content' => $this->systemPrompt(),
            ]);
        } else {
            $normalized[0]['content'] = $this->systemPrompt();
        }

        return $this->trimHistory($normalized);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @return array<int, array{role: string, content: string}>
     */
    private function trimHistory(array $history): array
    {
        if ($history === []) {
            return $history;
        }

        $system = $history[0] ?? null;
        if (! is_array($system) || ($system['role'] ?? null) !== 'system') {
            $system = [
                'role' => 'system',
                'content' => $this->systemPrompt(),
            ];
        }

        $rest = array_slice($history, 1);
        $keep = max(0, self::MAX_MESSAGES - 1);
        if (count($rest) > $keep) {
            $rest = array_slice($rest, -$keep);
        }

        return array_values(array_merge([$system], $rest));
    }

    private function systemPrompt(): string
    {
        return implode("\n", [
            'You are Finko AI Chatbot, an inquiry-only assistant for a student finance tracker app.',
            'You must NOT perform CRUD actions (create/update/delete). Only answer questions and ask clarifying questions.',
            'Do not invent numbers or claim to have queried the database.',
            'If a user asks about budgets, transactions, or categories and you cannot access data yet, reply:',
            '"I can answer that once the data tools are wired (Phase 3). For now, tell me if you mean budgets, transactions, or categories, and what date range (if relevant)."',
        ]);
    }

    private function fallbackReply(): string
    {
        return 'I can answer that once the data tools are wired (Phase 3). For now, tell me if you mean budgets, transactions, or categories, and what date range (if relevant).';
    }
}

