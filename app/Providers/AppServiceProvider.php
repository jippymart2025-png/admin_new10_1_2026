<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Production-ready cache driver with smart fallback
        $this->configureCacheDriver();

        // Performance: Optimize database cache table if using database cache
        $this->optimizeDatabaseCache();

        // Force HTTPS if behind a proxy (like AWS, Cloudflare)
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $this->app['request']->server->set('HTTPS', 'on');
            URL::forceScheme('https');
        }

        // Load environment-specific impersonation config
        $environment = app()->environment();
        $configFile = "impersonation.{$environment}";

        if (file_exists(config_path("{$configFile}.php"))) {
            config([$configFile => require config_path("{$configFile}.php")]);
        }

        // --------------------------------------------------
        // 🚀 HOSTINGER STORAGE FIX (NO SYMLINK ALLOWED)
        // --------------------------------------------------

        $publicStorage = public_path('storage');
        $storagePath   = storage_path('app/public');

        // Create public/storage folder if not exists
        if (! File::exists($publicStorage)) {
            File::makeDirectory($publicStorage, 0755, true);
        }

        // Only copy if public/storage is empty and writable
        if (File::isDirectory($storagePath) && is_writable($publicStorage) && count(File::files($publicStorage)) === 0) {
            try {
                File::copyDirectory($storagePath, $publicStorage);
            } catch (\Exception $e) {
                // Log the error but do not break the site
                Log::error('Storage copy failed: ' . $e->getMessage());
            }
        }
    }

    /**
     * Configure cache driver with smart fallback for production
     */
    private function configureCacheDriver()
    {
        $cacheDriver = config('cache.default');
        $isProduction = app()->environment('production');

        // Only perform fallback if Redis is configured but not available
        if ($cacheDriver === 'redis' && !class_exists('Redis')) {
            // Try database cache as fallback, then file
            if (config('database.default') !== null) {
                try {
                    // Test database connection
                    DB::connection()->getPdo();
                    config(['cache.default' => 'database']);
                    if ($isProduction) {
                        Log::warning('Redis not available, falling back to database cache. Performance may be slower. Consider setting up Redis for better performance.');
                    }
                } catch (\Exception $e) {
                    // Database not available, fallback to file
                    config(['cache.default' => 'file']);
                    if ($isProduction) {
                        Log::error('Redis and database not available, falling back to file cache. Performance will be significantly slower.');
                    }
                }
            } else {
                config(['cache.default' => 'file']);
                if ($isProduction) {
                    Log::warning('Redis not available, falling back to file cache. Performance will be slower.');
                }
            }
        }

        // Production warning if using non-optimal cache driver
        if ($isProduction && in_array(config('cache.default'), ['file', 'database'])) {
            Log::info('Using ' . config('cache.default') . ' cache driver. For optimal performance, use Redis.');
        }
    }

    /**
     * Optimize database cache table for better performance
     */
    private function optimizeDatabaseCache()
    {
        if (config('cache.default') !== 'database') {
            return;
        }

        try {
            // Check if cache table exists
            if (!Schema::hasTable('cache')) {
                return;
            }

            // Ensure cache table has proper indexes for faster queries
            // Using try-catch as indexes may already exist
            try {
                DB::statement('CREATE INDEX IF NOT EXISTS idx_cache_key ON cache(`key`(191))');
            } catch (\Exception $e) {
                // Index may already exist or table structure different
            }

            try {
                DB::statement('CREATE INDEX IF NOT EXISTS idx_cache_expiration ON cache(expiration)');
            } catch (\Exception $e) {
                // Index may already exist or table structure different
            }

            // Clean expired cache entries periodically (only in production)
            if (app()->environment('production') && rand(1, 100) === 1) {
                // 1% chance to clean expired cache on each request (lightweight cleanup)
                DB::table('cache')->where('expiration', '<', now()->timestamp)->delete();
            }
        } catch (\Exception $e) {
            // Silently fail - don't break the application if cache optimization fails
            if (app()->environment('production')) {
                Log::debug('Cache optimization skipped: ' . $e->getMessage());
            }
        }
    }
}
