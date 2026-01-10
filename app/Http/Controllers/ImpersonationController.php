<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MySQLImpersonationService;
use Illuminate\Support\Facades\Cache;

class ImpersonationController extends Controller
{
    private $service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new MySQLImpersonationService();
    }

    public function generateToken(Request $request)
    {
        $request->validate([
            'restaurant_id' => 'required'
        ]);

        $admin = Auth::user();
        $restaurantId = $request->restaurant_id;

        // Generate mysql-based impersonation token
        $result = $this->service->generateToken($restaurantId, $admin->id);

        if (!$result['success']) {
            return response()->json($result, 400);
        }

//        $restaurantPanel = config('app.restaurant_panel_url', 'http://127.0.0.1:8001');
        $restaurantPanel = env('RESTAURANT_PANEL_URL', 'http://127.0.0.1:8001');
        $url = $restaurantPanel . '/login/impersonate?key=' . $result['key'];

        return response()->json([
            'success' => true,
            'impersonation_url' => $url,
            'message' => "Redirecting to restaurant owner " . $result['owner_name']
        ]);
    }
}
