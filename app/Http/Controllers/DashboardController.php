<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Show the main dashboard page.
     */
    public function index(Request $request)
    {
        try {
            $forceRefresh = $request->boolean('refresh', false);
            $stats = CacheService::getDashboardStats($forceRefresh);
            Log::info('Dashboard stats retrieved', ['cached_at' => $stats['cached_at'] ?? 'Not cached']);

            return view('home', compact('stats'));
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            return view('home');
        }
    }

    /**
     * Fetch stats via AJAX.
     */
    public function getStats(Request $request)
    {
        try {
            $forceRefresh = $request->boolean('refresh', false);
            $stats = CacheService::getDashboardStats($forceRefresh);

            return response()->json([
                'success' => true,
                'data' => $stats,
                'cached' => !$forceRefresh
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
     * Clear cache manually
     */
    public function clearCache()
    {
        try {
            CacheService::clearDashboardCache();
            return response()->json(['success' => true, 'message' => 'Cache cleared successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get cache stats
     */
    public function getCacheStats()
    {
        try {
            $stats = CacheService::getCacheStats();
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}
