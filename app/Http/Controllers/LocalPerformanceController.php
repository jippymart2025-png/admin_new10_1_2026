<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LocalCacheService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LocalPerformanceController extends Controller
{
    /**
     * Show performance dashboard
     */
    public function index()
    {
        $cacheStats = LocalCacheService::getCacheStats();
        $performanceMetrics = $this->getPerformanceMetrics();
        
        return view('performance.index', compact('cacheStats', 'performanceMetrics'));
    }

    /**
     * Get dashboard stats with local caching
     */
    public function getDashboardStats(Request $request)
    {
        try {
            $forceRefresh = $request->boolean('refresh', false);
            $stats = LocalCacheService::getDashboardStats($forceRefresh);
            
            return response()->json([
                'success' => true,
                'data' => $stats,
                'cached' => !$forceRefresh,
                'cache_type' => 'local_file_optimized'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Dashboard stats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving dashboard stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear specific cache categories
     */
    public function clearCacheByCategory(Request $request)
    {
        try {
            $category = $request->input('category', 'all');
            
            if ($category === 'all') {
                LocalCacheService::clearDashboardCache();
                $message = 'All dashboard caches cleared successfully';
            } else {
                LocalCacheService::clearCacheByCategory($category);
                $message = "Cache cleared for category: {$category}";
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cache clear error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize Laravel application
     */
    public function optimizeApplication()
    {
        try {
            $results = [];
            
            // Clear all caches first
            $results['cache_clear'] = Artisan::call('cache:clear');
            $results['config_clear'] = Artisan::call('config:clear');
            $results['view_clear'] = Artisan::call('view:clear');
            $results['route_clear'] = Artisan::call('route:clear');
            
            // Optimize autoloader
            $results['composer_optimize'] = shell_exec('cd ' . base_path() . ' && composer install --optimize-autoloader --no-dev 2>&1');
            
            // Cache routes and config
            $results['route_cache'] = Artisan::call('route:cache');
            $results['config_cache'] = Artisan::call('config:cache');
            $results['view_cache'] = Artisan::call('view:cache');
            
            // Set proper permissions
            $this->setOptimizedPermissions();
            
            Log::info('Application optimization completed', $results);
            
            return response()->json([
                'success' => true,
                'message' => 'Application optimized successfully',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Application optimization error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error optimizing application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance metrics
     */
    public function getPerformanceMetrics()
    {
        try {
            $metrics = [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'cache_driver' => config('cache.default'),
                'session_driver' => config('session.driver'),
                'queue_connection' => config('queue.default'),
                'database_connection' => config('database.default'),
                'app_debug' => config('app.debug'),
                'app_environment' => config('app.env'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
                'fallback_locale' => config('app.fallback_locale'),
                'cache_prefix' => config('cache.prefix'),
                'session_lifetime' => config('session.lifetime'),
                'session_expire_on_close' => config('session.expire_on_close'),
                'session_secure' => config('session.secure'),
                'session_http_only' => config('session.http_only'),
                'session_same_site' => config('session.same_site'),
            ];
            
            return $metrics;
            
        } catch (\Exception $e) {
            Log::error('Error getting performance metrics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats()
    {
        try {
            $stats = LocalCacheService::getCacheStats();
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cache stats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving cache stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set optimized permissions for cache directories
     */
    private function setOptimizedPermissions()
    {
        try {
            $directories = [
                storage_path('framework/cache'),
                storage_path('framework/views'),
                storage_path('framework/sessions'),
                storage_path('logs'),
                storage_path('app/public'),
            ];
            
            foreach ($directories as $directory) {
                if (is_dir($directory)) {
                    // Set ownership to web server user
                    if (function_exists('posix_getpwuid')) {
                        $webUser = posix_getpwuid(posix_geteuid());
                        if ($webUser) {
                            chown($directory, $webUser['name']);
                        }
                    }
                    
                    // Set permissions
                    chmod($directory, 0755);
                    
                    // Set recursive permissions for subdirectories
                    $this->setRecursivePermissions($directory);
                }
            }
            
            Log::info('Optimized permissions set for cache directories');
            
        } catch (\Exception $e) {
            Log::error('Error setting optimized permissions: ' . $e->getMessage());
        }
    }

    /**
     * Set recursive permissions for directories
     */
    private function setRecursivePermissions($directory)
    {
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    chmod($file->getPathname(), 0755);
                } else {
                    chmod($file->getPathname(), 0644);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error setting recursive permissions: ' . $e->getMessage());
        }
    }

    /**
     * Test cache performance
     */
    public function testCachePerformance()
    {
        try {
            $startTime = microtime(true);
            
            // Test cache write
            Cache::put('performance_test', 'test_value', 60);
            $writeTime = microtime(true) - $startTime;
            
            // Test cache read
            $readStart = microtime(true);
            $value = Cache::get('performance_test');
            $readTime = microtime(true) - $readStart;
            
            // Test cache delete
            $deleteStart = microtime(true);
            Cache::forget('performance_test');
            $deleteTime = microtime(true) - $deleteStart;
            
            $results = [
                'write_time' => round($writeTime * 1000, 3) . 'ms',
                'read_time' => round($readTime * 1000, 3) . 'ms',
                'delete_time' => round($deleteTime * 1000, 3) . 'ms',
                'total_time' => round(($writeTime + $readTime + $deleteTime) * 1000, 3) . 'ms',
                'cache_driver' => config('cache.default'),
                'test_successful' => $value === 'test_value'
            ];
            
            return response()->json([
                'success' => true,
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cache performance test error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error testing cache performance',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

