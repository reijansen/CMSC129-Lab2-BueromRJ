<?php

namespace App\Providers;

use App\Database\Connectors\SupabasePostgresConnector;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Add a connect timeout to Postgres DSN to avoid hanging requests when
        // the Supabase database is unreachable / resets connections.
        $this->app->bind('db.connector.pgsql', fn () => new SupabasePostgresConnector());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
