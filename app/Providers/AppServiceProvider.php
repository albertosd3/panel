<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\QueryExecuted;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Log database queries in development
        if (config('app.debug')) {
            DB::listen(function (QueryExecuted $query) {
                $sql = $query->sql;
                $bindings = $query->bindings;
                $time = $query->time;
                
                // Only log queries that take longer than 100ms or are related to our tables
                if ($time > 100 || 
                    str_contains($sql, 'shortlink_events') || 
                    str_contains($sql, 'shortlink_visitors') ||
                    str_contains($sql, 'shortlinks')) {
                    
                    Log::debug('Database Query', [
                        'sql' => $sql,
                        'bindings' => $bindings,
                        'time' => $time . 'ms',
                        'connection' => $query->connection->getName()
                    ]);
                }
            });
        }

        // Log application startup
        Log::info('=== APPLICATION STARTED ===', [
            'timestamp' => now()->toISOString(),
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'log_level' => config('logging.level', 'debug'),
            'log_channel' => config('logging.default', 'stack')
        ]);

        // Log configuration status
        Log::info('=== CONFIGURATION STATUS ===', [
            'panel_pin_set' => !empty(config('panel.pin')),
            'bot_detection_enabled' => config('panel.block_bots', true),
            'count_bots' => config('panel.count_bots', false),
            'geoip_enabled' => config('panel.geoip.enabled', true),
            'stopbot_enabled' => config('panel.stopbot.enabled', false),
            'database_connection' => config('database.default'),
            'database_database' => config('database.connections.sqlite.database')
        ]);
    }
}
