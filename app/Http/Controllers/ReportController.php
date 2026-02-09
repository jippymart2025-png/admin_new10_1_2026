<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($type)
    {
        if ($type == "sales") {
            return view('reports.sales-report');
        }
    }

    // Return options for filters (vendors, drivers, customers, categories, currency)
    public function salesOptions()
    {
        $vendors = DB::table('vendors')->select('id', 'title')->orderBy('title')->get();
        $drivers = DB::table('users')->select('id', 'firstName', 'lastName')
            ->where('role', 'driver')->orderBy('firstName')->get();
        $customers = DB::table('users')->select('id', 'firstName', 'lastName')
            ->where('role', 'customer')->orderBy('firstName')->get();
        // Categories derived from vendors.categoryTitle (JSON array) - flatten uniques
        $rawCategories = DB::table('vendors')->select('categoryID', 'categoryTitle')->get();
        $categories = [];
        foreach ($rawCategories as $row) {
            $titlesRaw = $row->categoryTitle;
            $idsRaw = $row->categoryID;

            // Normalize IDs to array
            $ids = [];
            if (is_array($idsRaw)) {
                $ids = $idsRaw;
            } else if (is_string($idsRaw)) {
                $trim = trim($idsRaw);
                if ($trim !== '' && ($trim[0] === '[' || str_contains($trim, ',') || str_contains($trim, '"'))) {
                    $decoded = json_decode($idsRaw, true);
                    if (is_array($decoded)) {
                        $ids = $decoded;
                    }
                }
                if (empty($ids) && $trim !== '') {
                    $ids = [$idsRaw];
                }
            } else if (is_numeric($idsRaw)) {
                $ids = [(string)$idsRaw];
            }

            // Normalize titles to array (or scalar)
            $titles = [];
            if (is_array($titlesRaw)) {
                $titles = $titlesRaw;
            } else if (is_string($titlesRaw)) {
                $ttrim = trim($titlesRaw);
                $maybeJson = ($ttrim !== '' && $ttrim[0] === '[');
                if ($maybeJson) {
                    $decodedT = json_decode($titlesRaw, true);
                    if (is_array($decodedT)) {
                        $titles = $decodedT;
                    }
                }
                if (empty($titles) && $ttrim !== '') {
                    $titles = [$titlesRaw];
                }
            }

            foreach ($ids as $idx => $cid) {
                $cidStr = is_scalar($cid) ? (string)$cid : '';
                $ctitle = is_array($titles) ? ($titles[$idx] ?? $cidStr) : ($titles[0] ?? $cidStr);
                if ($cidStr !== '') {
                    $categories[$cidStr] = $ctitle;
                }
            }
        }
        $categoriesOut = [];
        foreach ($categories as $cid => $ctitle) {
            $categoriesOut[] = ['id' => $cid, 'title' => $ctitle];
        }
        // Fetch currency with flexible column handling; fall back safely to defaults
        try {
            $currency = DB::table('currencies')->where('isActive', 1)
                ->select('symbol', 'symbolAtRight', 'decimal_degits')->first();
            if (!$currency) {
                // Try alternative column naming conventions if needed
                $currency = DB::table('currencies')->where('isActive', 1)
                    ->select('symbol', 'symbolAtRight', 'decimal_digits as decimal_degits')->first();
            }
        } catch (\Throwable $e) {
            $currency = null;
        }
        if (!$currency) {
            $currency = (object)['symbol' => '₹', 'symbolAtRight' => 0, 'decimal_degits' => 2];
        }
        return response()->json([
            'vendors' => $vendors,
            'drivers' => $drivers,
            'customers' => $customers,
            'categories' => $categoriesOut,
            'currency' => $currency,
        ]);
    }

    // Return sales report rows based on filters
    public function salesData(Request $request)
    {
        $vendorId = $request->input('vendor_id');
        $driverId = $request->input('driver_id');
        $customerId = $request->input('customer_id');
        $categoryId = $request->input('category_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $dateExpr = "CASE
            WHEN o.createdAt REGEXP '^[0-9]+$' THEN FROM_UNIXTIME(CASE WHEN LENGTH(o.createdAt)>10 THEN o.createdAt/1000 ELSE o.createdAt END)
            WHEN o.createdAt LIKE '\"%\"' THEN STR_TO_DATE(REPLACE(REPLACE(REPLACE(o.createdAt,'\"',''),'Z',''), 'T',' '), '%Y-%m-%d %H:%i:%s.%f')
            WHEN o.createdAt LIKE '%T%' THEN STR_TO_DATE(REPLACE(REPLACE(o.createdAt,'Z',''),'T',' '), '%Y-%m-%d %H:%i:%s.%f')
            WHEN STR_TO_DATE(o.createdAt, '%Y-%m-%d %H:%i:%s') IS NOT NULL THEN STR_TO_DATE(o.createdAt, '%Y-%m-%d %H:%i:%s')
            ELSE NULL END";

        // Build query with proper joins that handle multiple ID formats (id, firebase_id, _id)
        $q = DB::table('restaurant_orders as o')
            ->leftJoin('vendors as v', 'v.id', '=', 'o.vendorID')
            ->leftJoin('users as d', function ($join) {
                $join->on('d.id', '=', 'o.driverID')
                    ->orOn('d.firebase_id', '=', 'o.driverID')
                    ->orOn('d._id', '=', 'o.driverID');
            })
            ->leftJoin('users as u', function ($join) {
                $join->on('u.id', '=', 'o.authorID')
                    ->orOn('u.firebase_id', '=', 'o.authorID')
                    ->orOn('u._id', '=', 'o.authorID');
            })
            ->select(
                'o.*',
                'v.title as vendor_title',
                'v.categoryTitle as vendor_categoryTitle',
                'd.firstName as d_first',
                'd.lastName as d_last',
                'd.email as d_email',
                'd.phoneNumber as d_phone',
                'u.firstName as u_first',
                'u.lastName as u_last',
                'u.email as u_email',
                'u.phoneNumber as u_phone'
            )
            ->addSelect(DB::raw("$dateExpr as parsed_created_at"));

        // Include common variants of completed statuses
        $completedStatuses = [
            'restaurantorders Completed',
            'Order Completed',
            'Completed',
            'Driver Completed',
        ];
        $q->whereIn('o.status', $completedStatuses);
        if ($vendorId) $q->where('o.vendorID', $vendorId);
        if ($driverId) $q->where('o.driverID', $driverId);
        if ($customerId) $q->where('o.authorID', $customerId);
        if ($categoryId) {
            $q->where(function ($qq) use ($categoryId) {
                $qq->where('v.categoryID', $categoryId)
                    ->orWhere('v.categoryID', 'like', '%"' . $categoryId . '"%')
                    ->orWhere('v.categoryID', 'like', '%' . $categoryId . '%');
            });
        }
        if ($start && $end) {
            // Robust date filtering: handle datetime strings and unix timestamps (ms or s)
            $start = (string)$start;
            $end = (string)$end;
            $q->whereBetween(DB::raw("($dateExpr)"), [$start, $end]);
        }

        $rows = $q->orderBy(DB::raw("($dateExpr)"), 'desc')->limit(5000)->get();

        try {
            $currency = DB::table('currencies')->where('isActive', 1)
                ->select('symbol', 'symbolAtRight', 'decimal_degits')->first();
            if (!$currency) {
                $currency = DB::table('currencies')->where('isActive', 1)
                    ->select('symbol', 'symbolAtRight', 'decimal_digits as decimal_degits')->first();
            }
        } catch (\Throwable $e) {
            $currency = null;
        }
        if (!$currency) {
            $currency = (object)['symbol' => '₹', 'symbolAtRight' => 0, 'decimal_degits' => 2];
        }

        $normalizeNumeric = function ($value) {
            if (is_null($value)) {
                return 0.0;
            }
            if (is_numeric($value)) {
                return (float)$value;
            }
            if (is_string($value)) {
                $trimmed = trim($value, "\"' ");
                if ($trimmed === '') {
                    return 0.0;
                }
                if (is_numeric($trimmed)) {
                    return (float)$trimmed;
                }
                $decoded = json_decode($value, true);
                if (is_numeric($decoded)) {
                    return (float)$decoded;
                }
            }
            return 0.0;
        };

        $out = [];
        foreach ($rows as $r) {
            $driverName = trim(
                ($r->d_first ?: '') . ' ' .
                ($r->d_last ?: '')
            );

            $userName = trim(
                ($r->u_first ?: '') . ' ' .
                ($r->u_last ?: '')
            );

            $rawDate = $r->parsed_created_at ?? $r->createdAt ?? '';
            $dateTxt = '';
            if (!empty($rawDate)) {
                $cleanDate = is_string($rawDate) ? trim($rawDate, "\"") : $rawDate;
                try {
                    $dateObj = Carbon::parse($cleanDate);
                    $dateTxt = $dateObj->format('M d, Y h:i A');
                } catch (\Throwable $e) {
                    $dateTxt = (string)$cleanDate;
                }
            }
            // category title: join array -> string
            $catTitles = json_decode($r->vendor_categoryTitle ?? '[]', true) ?: [];
            $categoryTxt = is_array($catTitles) ? implode(', ', $catTitles) : (string)($r->vendor_categoryTitle ?? '');

            // Total: use ToPay if exists, else 0 (heavy calc skipped)
            $total = $normalizeNumeric($r->ToPay ?? 0);
            $adminCommission = $normalizeNumeric($r->adminCommission ?? 0);
            // currency formatting moved to frontend if needed; we keep raw numbers here
            $out[] = [
                'order_id' => $r->id,
                'restaurant' => $r->vendor_title ?? '',
                'driver_name' => $driverName,
                'driver_email' => $r->d_email ?? '',
                'driver_phone' => $r->d_phone ?? '',
                'user_name' => $userName,
                'user_email' => $r->u_email ?? '',
                'user_phone' => $r->u_phone ?? '',
                'date' => $dateTxt,
                'category' => $categoryTxt,
                'payment_method' => $r->payment_method ?? '',
                'total' => $total,
                'admin_commission' => $adminCommission,
            ];
        }

        return response()->json([
            'rows' => $out,
            'currency' => $currency,
        ]);
    }

    public function userData(Request $request)
    {
        $draw   = intval($request->draw);
        $start  = intval($request->start,0);
        $length = intval($request->length,10);
        $zoneId = $request->input('zone_id', '');
        $search = $request->input('search', '');

        // Build query
        $query = DB::table('users as u')
            ->select(
                'u.id as user_id',
                'u.firebase_id',
                'u.firstName',
                'u.lastName',
                'u.email',
                'u.phoneNumber as phone',
                'u.shippingAddress',
                DB::raw('(SELECT MAX(ro.createdAt) FROM restaurant_orders as ro WHERE ro.authorID = u.firebase_id OR ro.authorID = u.id) as last_order_date')
            )
            ->where('u.role', 'customer');

        // Apply search filter in SQL (more efficient)
        if (!empty($search)) {
            $searchTerm = '%' . $search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('u.firstName', 'like', $searchTerm)
                  ->orWhere('u.lastName', 'like', $searchTerm)
                  ->orWhere('u.email', 'like', $searchTerm)
                  ->orWhere('u.phoneNumber', 'like', $searchTerm)
                  ->orWhere('u.id', 'like', $searchTerm);
            });
        }

        // Apply zone filter in SQL using JSON search (if zone is specified)
        if (!empty($zoneId)) {
            $query->where(function($q) use ($zoneId) {
                $q->whereRaw('JSON_SEARCH(shippingAddress, "one", ?, NULL, "$[*].zoneId") IS NOT NULL', [$zoneId])
                  ->orWhereRaw('JSON_EXTRACT(shippingAddress, "$[0].zoneId") = ?', [$zoneId])
                  ->orWhere('shippingAddress', 'like', '%"zoneId":"' . $zoneId . '"%');
            });
        }

        $users = $query
            ->skip($start)
            ->take($length)
            ->get();
        // Get all zone names at once for efficiency
        $allZoneIds = [];
        foreach ($users as $user) {
            $zoneIdFromAddress = $this->extractZoneFromShippingAddress($user->shippingAddress);
            if (!empty($zoneIdFromAddress)) {
                $allZoneIds[] = $zoneIdFromAddress;
            }
        }
        $allZoneIds = array_unique($allZoneIds);
        $zones = [];
        if (!empty($allZoneIds)) {
            $zonesData = DB::table('zone')->whereIn('id', $allZoneIds)->get();
            foreach ($zonesData as $zone) {
                $zones[$zone->id] = $zone->name;
            }
        }

        // Process users to extract zone information and format data
        $userReport = [];
        foreach ($users as $user) {
            $zoneIdFromAddress = $this->extractZoneFromShippingAddress($user->shippingAddress);
            $zoneName = !empty($zoneIdFromAddress) && isset($zones[$zoneIdFromAddress])
                ? $zones[$zoneIdFromAddress]
                : '-';

            $userReport[] = (object)[
                'user_id' => $user->user_id,
                'name' => trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')),
                'email' => $user->email ?? '-',
                'phone' => $user->phone ?? '-',
                'zone' => $zoneName,
                'zoneId' => $zoneIdFromAddress,
                'last_order_date' => $user->last_order_date
            ];
        }

        // Sort by last_order_date descending
        usort($userReport, function($a, $b) {
            $dateA = $a->last_order_date ? strtotime($a->last_order_date) : 0;
            $dateB = $b->last_order_date ? strtotime($b->last_order_date) : 0;
            return $dateB - $dateA;
        });

        // Get total count for statistics
        $totalCount = DB::table('users')->where('role', 'customer')->count();

        // If this is an AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $userReport,
                'count' => count($userReport),
                'total' => $totalCount,
                        'draw' => $draw

            ]);
        }

        // Otherwise return view (for initial page load - empty data, will load via AJAX)
        $userReport = [];
        return view('reports.user-report', compact('userReport'));
    }

    /**
     * Extract zoneId from shippingAddress JSON
     * @param string|null $shippingAddress JSON string
     * @return string zoneId or empty string
     */
    private function extractZoneFromShippingAddress($shippingAddress)
    {
        $zoneId = '';
        if (!$shippingAddress) {
            return $zoneId;
        }

        try {
            $addresses = json_decode($shippingAddress, true);
            if (is_array($addresses) && count($addresses) > 0) {
                // Find default address first
                foreach ($addresses as $address) {
                    if (isset($address['isDefault']) && $address['isDefault'] == 1) {
                        if (isset($address['zoneId'])) {
                            $zoneId = $address['zoneId'];
                            break;
                        }
                    }
                }

                // If no default found, get zoneId from first address
                if (empty($zoneId) && isset($addresses[0]['zoneId'])) {
                    $zoneId = $addresses[0]['zoneId'];
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Error extracting zoneId from shippingAddress: ' . $e->getMessage());
        }

        return $zoneId;
    }
}

?>
