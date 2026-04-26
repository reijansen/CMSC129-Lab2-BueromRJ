<?php

namespace App\Services;

class AIContextState
{
    public const SESSION_KEY = 'ai_context_state';

    public static function default(): array
    {
        return [
            'last_entity_type' => null,
            'last_entity_id' => null,
            'last_list' => null,
            'last_date_range' => null,
            'preferences' => [
                'currency' => 'PHP',
                'verbosity' => 'short',
            ],
        ];
    }

    public static function normalize(mixed $value): array
    {
        $state = is_array($value) ? $value : [];
        $defaults = self::default();

        $normalized = [
            'last_entity_type' => self::normalizeEntityType($state['last_entity_type'] ?? null),
            'last_entity_id' => self::normalizeId($state['last_entity_id'] ?? null),
            'last_list' => self::normalizeLastList($state['last_list'] ?? null),
            'last_date_range' => self::normalizeDateRange($state['last_date_range'] ?? null),
            'preferences' => is_array($state['preferences'] ?? null) ? array_merge($defaults['preferences'], $state['preferences']) : $defaults['preferences'],
        ];

        $normalized['preferences']['currency'] = is_string($normalized['preferences']['currency'] ?? null)
            ? $normalized['preferences']['currency']
            : $defaults['preferences']['currency'];
        $normalized['preferences']['verbosity'] = is_string($normalized['preferences']['verbosity'] ?? null)
            ? $normalized['preferences']['verbosity']
            : $defaults['preferences']['verbosity'];

        return $normalized;
    }

    public static function merge(array $state, array $update): array
    {
        $merged = $state;

        foreach (['last_entity_type', 'last_entity_id', 'last_list', 'last_date_range', 'preferences'] as $key) {
            if (array_key_exists($key, $update)) {
                $merged[$key] = $update[$key];
            }
        }

        return self::normalize($merged);
    }

    private static function normalizeEntityType(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $v = strtolower(trim($value));
        if (in_array($v, ['category', 'budget', 'transaction'], true)) {
            return $v;
        }

        return null;
    }

    private static function normalizeId(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_string($value) && ctype_digit($value)) {
            $int = (int) $value;

            return $int > 0 ? $int : null;
        }

        return null;
    }

    private static function normalizeLastList(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $resource = self::normalizeEntityType($value['resource'] ?? null);
        $ids = $value['ids'] ?? null;
        $filters = is_array($value['filters'] ?? null) ? $value['filters'] : [];

        if (! $resource || ! is_array($ids)) {
            return null;
        }

        $normalizedIds = [];
        foreach ($ids as $id) {
            $int = self::normalizeId($id);
            if ($int) {
                $normalizedIds[] = $int;
            }
        }

        if ($normalizedIds === []) {
            return null;
        }

        return [
            'resource' => $resource,
            'ids' => array_values(array_unique($normalizedIds)),
            'filters' => $filters,
        ];
    }

    private static function normalizeDateRange(mixed $value): ?array
    {
        if (! is_array($value)) {
            return null;
        }

        $from = $value['date_from'] ?? null;
        $to = $value['date_to'] ?? null;

        $from = is_string($from) && $from !== '' ? $from : null;
        $to = is_string($to) && $to !== '' ? $to : null;

        if (! $from && ! $to) {
            return null;
        }

        return [
            'date_from' => $from,
            'date_to' => $to,
        ];
    }
}

