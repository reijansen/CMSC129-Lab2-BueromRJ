<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;

class SupabasePostgresConnector extends PostgresConnector
{
    /**
     * Create a DSN string from a configuration.
     *
     * Supabase connectivity can be flaky on some networks. Adding a connect timeout
     * prevents requests (like /login) from hanging until PHP's max_execution_time.
     */
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        $connectTimeout = (int) ($config['connect_timeout'] ?? 0);
        if ($connectTimeout > 0 && ! str_contains($dsn, 'connect_timeout=')) {
            $dsn .= ";connect_timeout={$connectTimeout}";
        }

        return $dsn;
    }
}

