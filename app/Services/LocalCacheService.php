<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LocalCacheService
{
    /**
     * Enhanced file-based caching with intelligent invalidation
     */
    public static function rememberOptimized($key, $callback, $ttl = 300, $tags = [])
    {
        $cacheKey = self::generateCacheKey($key, $tags);
        
        return Cache::remember($cacheKey, $ttl, function () use ($callback, $key) {
            try {
                $result = $callback();
                
                // Store cache metadata for intelligent invalidation
                self::storeCacheMetadata($key, $tags);
                
                return $result;
            } catch (\Exception $e) {
                Log::error("Cache callback error for key: {$key}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Return fallback or null
                return null;
            }
        });
    }

    /**
     * Cache dashboard statistics with file-based storage
     */
    public static function getDashboardStats($forceRefresh = false)
    {
        $cacheKey = 'dashboard_stats_v2';
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, 600, function () { // 10 minutes cache
            return [
                'total_marts' => self::getTotalMarts(),
                'active_marts' => self::getActiveMarts(),
                'total_orders' => self::getTotalOrders(),
                'total_clients' => self::getTotalClients(),
                'total_drivers' => self::getTotalDrivers(),
                'total_earnings' => self::getTotalEarnings(),
                'admin_commission' => self::getAdminCommission(),
                'cached_at' => now()->toISOString(),
                'cache_type' => 'file_optimized'
            ];
        });
    }

    /**
     * Get total marts count with caching
     */
    private static function getTotalMarts()
    {
        return Cache::remember('total_marts_v2', 1200, function () { // 20 minutes
            try {
                // Use your existing database connection
                $count = DB::table('vendors')->count();
                return $count;
            } catch (\Exception $e) {
                Log::error('Error getting total marts: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get active marts count with caching
     */
    private static function getActiveMarts()
    {
        return Cache::remember('active_marts_v2', 1200, function () { // 20 minutes
            try {
                $count = DB::table('vendors')->where('isActive', true)->count();
                return $count;
            } catch (\Exception $e) {
                Log::error('Error getting active marts: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get total orders count with caching
     */
    private static function getTotalOrders()
    {
        return Cache::remember('total_orders_v2', 600, function () { // 10 minutes
            try {
                $count = DB::table('restaurant_orders')->count();
                return $count;
            } catch (\Exception $e) {
                Log::error('Error getting total orders: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get total clients count with caching
     */
    private static function getTotalClients()
    {
        return Cache::remember('total_clients_v2', 1200, function () { // 20 minutes
            try {
                $count = DB::table('users')->where('role', 'client')->count();
                return $count;
            } catch (\Exception $e) {
                Log::error('Error getting total clients: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get total drivers count with caching
     */
    private static function getTotalDrivers()
    {
        return Cache::remember('total_drivers_v2', 1200, function () { // 20 minutes
            try {
                $count = DB::table('users')->where('role', 'driver')->count();
                return $count;
            } catch (\Exception $e) {
                Log::error('Error getting total drivers: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get total earnings with caching
     */
    private static function getTotalEarnings()
    {
        return Cache::remember('total_earnings_v2', 600, function () { // 10 minutes
            try {
                $total = DB::table('restaurant_orders')
                    ->whereIn('status', ['completed', 'delivered'])
                    ->sum('toPayAmount');
                
                return round($total, 2);
            } catch (\Exception $e) {
                Log::error('Error getting total earnings: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Get admin commission with caching
     */
    private static function getAdminCommission()
    {
        return Cache::remember('admin_commission_v2', 600, function () { // 10 minutes
            try {
                $total = DB::table('restaurant_orders')
                    ->whereIn('status', ['completed', 'delivered'])
                    ->sum('adminCommission');
                
                return round($total, 2);
            } catch (\Exception $e) {
                Log::error('Error getting admin commission: ' . $e->getMessage());
                return 0;
            }
        });
    }

    /**
     * Generate optimized cache keys
     */
    private static function generateCacheKey($key, $tags = [])
    {
        $prefix = 'local_optimized';
        $tagString = !empty($tags) ? '_' . implode('_', $tags) : '';
        return "{$prefix}_{$key}{$tagString}";
    }

    /**
     * Store cache metadata for intelligent invalidation
     */
    private static function storeCacheMetadata($key, $tags = [])
    {
        try {
            $metadata = [
                'key' => $key,
                'tags' => $tags,
                'created_at' => now()->toISOString(),
                'expires_at' => now()->addMinutes(10)->toISOString()
            ];
            
            Cache::put("metadata_{$key}", $metadata, 600);
        } catch (\Exception $e) {
            Log::error("Error storing cache metadata: " . $e->getMessage());
        }
    }

    /**
     * Clear specific cache categories
     */
    public static function clearCacheByCategory($category)
    {
        $keys = [
            'dashboard_stats_v2',
            'total_marts_v2',
            'active_marts_v2',
            'total_orders_v2',
            'total_clients_v2',
            'total_drivers_v2',
            'total_earnings_v2',
            'admin_commission_v2'
        ];
        
        foreach ($keys as $key) {
            if (strpos($key, $category) !== false) {
                Cache::forget($key);
            }
        }
        
        Log::info("Cache cleared for category: {$category}");
    }

    /**
     * Clear all dashboard caches
     */
    public static function clearDashboardCache()
    {
        $keys = [
            'dashboard_stats_v2',
            'total_marts_v2',
            'active_marts_v2',
            'total_orders_v2',
            'total_clients_v2',
            'total_drivers_v2',
            'total_earnings_v2',
            'admin_commission_v2'
        ];
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
        
        Log::info('All dashboard caches cleared');
    }

    /**
     * Get cache performance statistics
     */
    public static function getCacheStats()
    {
        return [
            'cache_driver' => config('cache.default'),
            'total_cached_items' => self::countCachedItems(),
            'cache_size' => self::getCacheDirectorySize(),
            'last_cleared' => Cache::get('cache_last_cleared', 'Never'),
            'performance_score' => self::calculatePerformanceScore()
        ];
    }

    /**
     * Count total cached items
     */
    private static function countCachedItems()
    {
        try {
            $cachePath = storage_path('framework/cache/data');
            if (is_dir($cachePath)) {
                $files = glob($cachePath . '/*');
                return count($files);
            }
            return 0;
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get cache directory size
     */
    private static function getCacheDirectorySize()
    {
        try {
            $cachePath = storage_path('framework/cache/data');
            if (is_dir($cachePath)) {
                $size = 0;
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($cachePath, \RecursiveDirectoryIterator::SKIP_DOTS)
                );
                
                foreach ($files as $file) {
                    $size += $file->getSize();
                }
                
                return round($size / 1024 / 1024, 2) . ' MB';
            }
            return '0 MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Calculate performance score based on cache efficiency
     */
    private static function calculatePerformanceScore()
    {
        try {
            $score = 0;
            
            // Check if caching is working
            if (Cache::has('dashboard_stats_v2')) {
                $score += 30;
            }
            
            // Check cache directory size (smaller is better)
            $size = self::getCacheDirectorySize();
            if (strpos($size, 'MB') !== false) {
                $sizeMB = (float) str_replace(' MB', '', $size);
                if ($sizeMB < 10) $score += 25;
                elseif ($sizeMB < 50) $score += 20;
                elseif ($sizeMB < 100) $score += 15;
                else $score += 10;
            }
            
            // Check cache hit rate (if available)
            $hitRate = Cache::get('cache_hit_rate', 0);
            $score += min(25, $hitRate);
            
            // Check if route caching is enabled
            if (file_exists(storage_path('framework/cache/routes.php'))) {
                $score += 20;
            }
            
            return min(100, $score);
        } catch (\Exception $e) {
            return 0;
        }
    }
}

