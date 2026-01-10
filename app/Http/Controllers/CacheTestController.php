<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CacheTestController extends Controller
{
    /**
     * Test database caching
     */
    public function testDatabaseCache()
    {
        try {
            $results = [];
            
            // Test 1: Basic cache operations
            $start = microtime(true);
            Cache::put('test_key', 'test_value', 60);
            $writeTime = microtime(true) - $start;
            
            $start = microtime(true);
            $value = Cache::get('test_key');
            $readTime = microtime(true) - $start;
            
            $start = microtime(true);
            Cache::forget('test_key');
            $deleteTime = microtime(true) - $start;
            
            $results['basic_cache'] = [
                'write_time' => round($writeTime * 1000, 3) . 'ms',
                'read_time' => round($readTime * 1000, 3) . 'ms',
                'delete_time' => round($deleteTime * 1000, 3) . 'ms',
                'value_retrieved' => $value === 'test_value'
            ];
            
            // Test 2: Database query caching - use existing tables
            $start = microtime(true);
            $users = Cache::remember('users_test', 60, function () {
                return DB::table('users')->select('id', 'name')->limit(5)->get();
            });
            $queryCacheTime = microtime(true) - $start;
            
            $results['query_cache'] = [
                'time' => round($queryCacheTime * 1000, 3) . 'ms',
                'users_count' => $users->count(),
                'cached' => Cache::has('users_test')
            ];
            
            // Test 3: Cache statistics
            $results['cache_stats'] = [
                'driver' => config('cache.default'),
                'connection' => config('cache.stores.database.connection'),
                'table' => config('cache.stores.database.table'),
                'prefix' => config('cache.prefix')
            ];
            
            // Test 4: Session storage
            $results['session_info'] = [
                'driver' => config('session.driver'),
                'table' => config('session.table'),
                'lifetime' => config('session.lifetime')
            ];
            
            // Test 5: Performance comparison - use existing tables
            $start = microtime(true);
            $directQuery = DB::table('users')->select('id', 'name')->limit(5)->get();
            $directQueryTime = microtime(true) - $start;
            
            $start = microtime(true);
            $cachedQuery = Cache::remember('users_comparison', 60, function () {
                return DB::table('users')->select('id', 'name')->limit(5)->get();
            });
            $cachedQueryTime = microtime(true) - $start;
            
            $results['performance_comparison'] = [
                'direct_query' => round($directQueryTime * 1000, 3) . 'ms',
                'cached_query' => round($cachedQueryTime * 1000, 3) . 'ms',
                'improvement' => round((($directQueryTime - $cachedQueryTime) / $directQueryTime) * 100, 1) . '%'
            ];
            
            // Clean up test data
            Cache::forget('users_test');
            Cache::forget('users_comparison');
            
            return response()->json([
                'success' => true,
                'message' => 'Database cache test completed successfully',
                'results' => $results,
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Database cache test error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Database cache test failed',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    
    /**
     * Test session storage
     */
    public function testSessionStorage(Request $request)
    {
        try {
            // Store test session data
            $request->session()->put('test_user_id', 123);
            $request->session()->put('test_user_name', 'Test User');
            $request->session()->put('test_timestamp', now()->toISOString());
            
            // Retrieve session data
            $sessionData = [
                'user_id' => $request->session()->get('test_user_id'),
                'user_name' => $request->session()->get('test_user_name'),
                'timestamp' => $request->session()->get('test_timestamp'),
                'session_id' => $request->session()->getId()
            ];
            
            // Check if session exists in database
            $sessionExists = DB::table('sessions')
                ->where('id', $request->session()->getId())
                ->exists();
            
            $results = [
                'session_data' => $sessionData,
                'stored_in_database' => $sessionExists,
                'session_driver' => config('session.driver'),
                'session_table' => config('session.table')
            ];
            
            // Clean up test session
            $request->session()->forget(['test_user_id', 'test_user_name', 'test_timestamp']);
            
            return response()->json([
                'success' => true,
                'message' => 'Session storage test completed',
                'results' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Session storage test error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Session storage test failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get cache configuration status
     */
    public function getCacheConfig()
    {
        try {
            $config = [
                'cache' => [
                    'default' => config('cache.default'),
                    'stores' => [
                        'database' => [
                            'driver' => config('cache.stores.database.driver'),
                            'table' => config('cache.stores.database.table'),
                            'connection' => config('cache.stores.database.connection')
                        ],
                        'file' => [
                            'driver' => config('cache.stores.file.driver'),
                            'path' => storage_path('framework/cache/data')
                        ]
                    ]
                ],
                'session' => [
                    'driver' => config('session.driver'),
                    'table' => config('session.table'),
                    'lifetime' => config('session.lifetime'),
                    'expire_on_close' => config('session.expire_on_close')
                ],
                'database' => [
                    'default' => config('database.default'),
                    'connections' => [
                        'mysql' => [
                            'host' => config('database.connections.mysql.host'),
                            'database' => config('database.connections.mysql.database'),
                            'username' => config('database.connections.mysql.username')
                        ]
                    ]
                ]
            ];
            
            return response()->json([
                'success' => true,
                'config' => $config
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cache config error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache configuration',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
