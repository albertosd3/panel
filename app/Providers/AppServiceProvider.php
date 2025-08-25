<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        Schema::defaultStringLength(191);
        
        // Auto-cleanup: Remove test and debug files in production
        if (config('app.env') === 'production') {
            $this->cleanupTestFiles();
        }
        
        // Enable query logging in debug mode
        if (config('app.debug')) {
            \DB::listen(function ($query) {
                if ($query->time > 100) { // Log slow queries (>100ms)
                    \Log::info('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms'
                    ]);
                }
            });
        }
    }
    
    /**
     * Automatically remove test and debug files in production
     */
    private function cleanupTestFiles(): void
    {
        $basePath = base_path();
        $patterns = [
            '*test*.php', '*debug*.php', '*test*.html', '*debug*.html',
            '*test*.md', '*debug*.md', 'FORGE_*.md', 'frontend_*.html'
        ];
        
        foreach ($patterns as $pattern) {
            $files = glob($basePath . '/' . $pattern);
            foreach ($files as $file) {
                if (is_file($file) && !str_contains($file, 'tests/') && !str_contains($file, 'vendor/')) {
                    @unlink($file);
                }
            }
        }
    }
}
