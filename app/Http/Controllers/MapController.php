<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\restaurant_orders;
use App\Models\AppUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MapController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('map.index');
    }

    /**
     * Get live tracking data (in-transit orders + available drivers)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        try {
            $cacheKey = "sql_live_tracking_v1";

            // Cache for 5 seconds (since locations update frequently)
            $data = Cache::remember($cacheKey, 5, function () {
                $inTransitOrders = $this->getInTransitOrders();
                $availableDrivers = $this->getAvailableDrivers($inTransitOrders);

                return [
                    'in_transit' => $inTransitOrders,
                    'available_drivers' => $availableDrivers,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Live tracking data fetched successfully',
                'meta' => [
                    'in_transit_count' => count($data['in_transit']),
                    'available_drivers_count' => count($data['available_drivers']),
                    'total_count' => count($data['in_transit']) + count($data['available_drivers']),
                    'cache_ttl_seconds' => 5,
                ],
                'data' => array_merge($data['in_transit'], $data['available_drivers']),
            ]);

        } catch (\Exception $e) {
            \Log::error('Live Tracking Error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching live tracking data: ' . $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Get driver location by ID (for real-time updates)
     *
     * @param string $driverId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDriverLocation($driverId)
    {
        try {
            $driver = AppUser::where('firebase_id', $driverId)
                ->where('role', 'driver')
                ->where('isActive', true)
                ->whereNotNull('location')
                ->first();

            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found or not active',
                ], 404);
            }

            // Parse location JSON
            $location = $this->parseLocation($driver->location);

            if (!$location) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver location not available',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Driver location fetched successfully',
                'data' => [
                    'id' => $driver->firebase_id,
                    'location' => $location,
                    'firstName' => $driver->firstName,
                    'lastName' => $driver->lastName,
                    'phoneNumber' => $driver->phoneNumber,
                    'isActive' => $driver->isActive,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching driver location: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching driver location: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user details by ID
     *
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserDetail($userId)
    {
        try {
            $user = AppUser::where('firebase_id', $userId)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->firebase_id,
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'phoneNumber' => $user->phoneNumber,
                    'email' => $user->email,
                    'shippingAddress' => $this->parseJSON($user->shippingAddress),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching user details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get driver details by ID
     *
     * @param string $driverId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDriverDetail($driverId)
    {
        try {
            $driver = AppUser::where('firebase_id', $driverId)
                ->where('role', 'driver')
                ->where('isActive', true)
                ->first();

            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found',
                ], 404);
            }

            $location = $this->parseLocation($driver->location);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $driver->firebase_id,
                    'firstName' => $driver->firstName,
                    'lastName' => $driver->lastName,
                    'phoneNumber' => $driver->phoneNumber,
                    'location' => $location,
                    'isActive' => $driver->isActive,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching driver details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get in-transit orders with driver and customer details
     *
     * @return array
     */
    private function getInTransitOrders()
    {
        $orders = [];

        try {
            $inTransitOrders = DB::table('restaurant_orders as ro')
                ->leftJoin('vendors as v', 'v.id', '=', 'ro.vendorID')
                ->leftJoin('users as driver_user', 'driver_user.firebase_id', '=', 'ro.driverID')
                ->leftJoin('users as customer_user', 'customer_user.firebase_id', '=', 'ro.authorID')
                ->where('ro.status', 'In Transit')
                ->whereNotNull('ro.driverID')
                ->whereNotNull('driver_user.location')
                ->select(
                    'ro.id',
                    'ro.status',
                    'ro.takeAway',
                    'ro.vendorID',
                    'ro.driverID',
                    'ro.authorID',
                    'ro.author',
                    'ro.driver',
                    'v.title as vendor_title',
                    'v.location as vendor_location',
                    'driver_user.firstName as driver_firstName',
                    'driver_user.lastName as driver_lastName',
                    'driver_user.phoneNumber as driver_phoneNumber',
                    'driver_user.location as driver_location',
                    'customer_user.firstName as customer_firstName',
                    'customer_user.lastName as customer_lastName',
                    'customer_user.shippingAddress as customer_shippingAddress'
                )
                ->get();

            foreach ($inTransitOrders as $order) {
                $driverLocation = $this->parseLocation($order->driver_location);

                if (!$driverLocation) {
                    continue; // Skip if driver location is invalid
                }

                $author = $this->parseJSON($order->author);
                $driver = $this->parseJSON($order->driver);

                $orders[] = [
                    'id' => $order->id,
                    'flag' => 'in_transit',
                    'status' => $order->status,
                    'takeAway' => $order->takeAway,
                    'driver' => [
                        'id' => $order->driverID,
                        'firstName' => $order->driver_firstName,
                        'lastName' => $order->driver_lastName,
                        'phoneNumber' => $order->driver_phoneNumber,
                        'location' => $driverLocation,
                    ],
                    'author' => [
                        'id' => $order->authorID,
                        'firstName' => $order->customer_firstName,
                        'lastName' => $order->customer_lastName,
                        'shippingAddress' => $this->parseJSON($order->customer_shippingAddress),
                    ],
                    'vendor' => [
                        'id' => $order->vendorID,
                        'title' => $order->vendor_title,
                        'location' => $order->vendor_location,
                    ],
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching in-transit orders: ' . $e->getMessage());
        }

        return $orders;
    }

    /**
     * Get available drivers (not on a delivery)
     *
     * @param array $inTransitOrders
     * @return array
     */
    private function getAvailableDrivers($inTransitOrders)
    {
        $drivers = [];
        $busyDriverIds = [];

        // Collect driver IDs that are busy
        foreach ($inTransitOrders as $order) {
            if (isset($order['driver']['id'])) {
                $busyDriverIds[] = $order['driver']['id'];
            }
        }

        try {
            $availableDrivers = AppUser::where('role', 'driver')
                ->where('isActive', true)
                ->whereNotNull('location')
                ->whereNotIn('firebase_id', $busyDriverIds)
                ->get();

            foreach ($availableDrivers as $driver) {
                $location = $this->parseLocation($driver->location);

                // Skip if location is invalid
                if (!$location ||
                    !isset($location['latitude']) ||
                    !isset($location['longitude']) ||
                    $location['latitude'] === null ||
                    $location['longitude'] === null) {
                    continue;
                }

                $drivers[] = [
                    'id' => $driver->firebase_id,
                    'flag' => 'available',
                    'firstName' => $driver->firstName,
                    'lastName' => $driver->lastName,
                    'phoneNumber' => $driver->phoneNumber,
                    'location' => $location,
                    'isActive' => $driver->isActive,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching available drivers: ' . $e->getMessage());
        }

        return $drivers;
    }

    /**
     * Parse location JSON string to array
     *
     * @param string|null $locationString
     * @return array|null
     */
    private function parseLocation($locationString)
    {
        if (empty($locationString)) {
            return null;
        }

        try {
            $location = json_decode($locationString, true);

            if (json_last_error() === JSON_ERROR_NONE &&
                isset($location['latitude']) &&
                isset($location['longitude'])) {
                return [
                    'latitude' => (float) $location['latitude'],
                    'longitude' => (float) $location['longitude'],
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Error parsing location: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Parse JSON string to array
     *
     * @param string|null $jsonString
     * @return array|null
     */
    private function parseJSON($jsonString)
    {
        if (empty($jsonString)) {
            return null;
        }

        try {
            $data = json_decode($jsonString, true);
            return (json_last_error() === JSON_ERROR_NONE) ? $data : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
