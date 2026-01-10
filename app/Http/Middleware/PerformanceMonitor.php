<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();
        
        // Process the request
        $response = $next($request);
        
        // Calculate performance metrics
        $executionTime = microtime(true) - $startTime;
        $memoryUsage = memory_get_usage() - $startMemory;
        $peakMemory = memory_get_peak_usage(true);
        
        // Log slow requests (over 1 second)
        if ($executionTime > 1.0) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => round($executionTime, 3) . 's',
                'memory_usage' => round($memoryUsage / 1024 / 1024, 2) . 'MB',
                'peak_memory' => round($peakMemory / 1024 / 1024, 2) . 'MB',
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
        }
        
        // Log very slow requests (over 5 seconds)
        if ($executionTime > 5.0) {
            Log::error('Very slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => round($executionTime, 3) . 's',
                'memory_usage' => round($memoryUsage / 1024 / 1024, 2) . 'MB',
                'peak_memory' => round($peakMemory / 1024 / 1024, 2) . 'MB',
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
        }
        
        // Add performance headers to response
        $response->headers->set('X-Execution-Time', round($executionTime * 1000, 2) . 'ms');
        $response->headers->set('X-Memory-Usage', round($memoryUsage / 1024 / 1024, 2) . 'MB');
        
        // Update cache statistics
        $this->updateCacheStats($executionTime, $memoryUsage);
        
        return $response;
    }
    
    /**
     * Update cache statistics
     */
    private function updateCacheStats($executionTime, $memoryUsage)
    {
        try {
            $totalRequests = Cache::get('performance_total_requests', 0) + 1;
            $totalTime = Cache::get('performance_total_time', 0) + $executionTime;
            $totalMemory = Cache::get('performance_total_memory', 0) + $memoryUsage;
            $slowRequests = Cache::get('performance_slow_requests', 0);
            
            if ($executionTime > 1.0) {
                $slowRequests++;
            }
            
            // Calculate averages
            $avgTime = $totalTime / $totalRequests;
            $avgMemory = $totalMemory / $totalRequests;
            
            // Store updated stats
            Cache::put('performance_total_requests', $totalRequests, 3600);
            Cache::put('performance_total_time', $totalTime, 3600);
            Cache::put('performance_total_memory', $totalMemory, 3600);
            Cache::put('performance_slow_requests', $slowRequests, 3600);
            Cache::put('performance_avg_time', $avgTime, 3600);
            Cache::put('performance_avg_memory', $avgMemory, 3600);
            Cache::put('performance_last_updated', now()->toISOString(), 3600);
            
        } catch (\Exception $e) {
            Log::error('Error updating performance stats: ' . $e->getMessage());
        }
    }
}

