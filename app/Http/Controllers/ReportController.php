<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $search = $request->input('search.value');

        $query = DB::table('users as u')
            ->select(
                'u.id as user_id',
                'u.firebase_id',
                'u.firstName',
                'u.lastName',
                'u.email',
                'u.phoneNumber as phone',
                'u.shippingAddress',
                DB::raw('(SELECT COUNT(*)FROM restaurant_orders as ro WHERE ro.authorID = u.firebase_id  OR ro.authorID = u.id) as order_count'),
                DB::raw('(SELECT MAX(ro.createdAt) FROM restaurant_orders as ro WHERE ro.authorID = u.firebase_id OR ro.authorID = u.id) as last_order_date')
            )
            ->where('u.role', 'customer');

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

        if (!empty($zoneId)) {
            $query->where(function($q) use ($zoneId) {
                $q->whereRaw('JSON_SEARCH(shippingAddress, "one", ?, NULL, "$[*].zoneId") IS NOT NULL', [$zoneId])
                    ->orWhereRaw('JSON_EXTRACT(shippingAddress, "$[0].zoneId") = ?', [$zoneId])
                    ->orWhere('shippingAddress', 'like', '%"zoneId":"' . $zoneId . '"%');
            });
        }

        $recordsTotal = Cache::remember('customer_total_count', now()->addMinutes(10), fn () => DB::table('users')->where('role', 'customer')->count());
        $recordsFiltered = (clone $query)->count();

        $users = $query
            ->skip($start)
            ->take($length)
            ->get();

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

        $userReport = [];
        foreach ($users as $user) {
            $zoneIdFromAddress = $this->extractZoneFromShippingAddress($user->shippingAddress);
            $zoneName = !empty($zoneIdFromAddress) && isset($zones[$zoneIdFromAddress])
                ? $zones[$zoneIdFromAddress]
                : '-';
            $addressData = $this->extractAddressFields($user->shippingAddress);


            $userReport[] = (object)[
                'user_id' => $user->user_id,
                'name' => trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')),
                'email' => $user->email ?? '-',
                'phone' => $user->phone ?? '-',
                'zone' => $zoneName,
                'zoneId' => $zoneIdFromAddress,
                'address'  => $addressData['locality'],
                'count' => $user->order_count,
                'last_order_date' => $user->last_order_date
            ];
        }

        // If this is an AJAX request, return JSON
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $userReport,
                'count' => count($userReport),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'draw' => $draw,
            ]);
        }

        $userReport = [];
        return view('reports.user-report', compact('userReport'));
   }


    private function extractAddressFields($shippingAddress)
    {
        if (empty($shippingAddress)) {
            return [
                'label'     => '-',
                'address'   => '-',
                'addressAs' => '-',
                'landmark'  => '-',
                'city'      => '-',
                'pincode'   => '-',
                'locality'  => '-',
            ];
        }

        // If already an array
        if (is_array($shippingAddress)) {
            $addresses = $shippingAddress;
        }

        // If JSON string
        elseif (is_string($shippingAddress)) {

            $decoded = json_decode($shippingAddress, true);

            // ❌ Not JSON → plain string
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                return [
                    'label'     => '-',
                    'address'   => '-',
                    'addressAs' => '-',
                    'landmark'  => '-',
                    'city'      => '-',
                    'pincode'   => '-',
                    'locality'  => $shippingAddress, // keep string as locality
                ];
            }

            $addresses = $decoded;
        }

        else {
            return [
                'label'     => '-',
                'address'   => '-',
                'addressAs' => '-',
                'landmark'  => '-',
                'city'      => '-',
                'pincode'   => '-',
                'locality'  => '-',
            ];
        }

        // Ensure first address exists
        if (!isset($addresses[0]) || !is_array($addresses[0])) {
            return [
                'label'     => '-',
                'address'   => '-',
                'addressAs' => '-',
                'landmark'  => '-',
                'city'      => '-',
                'pincode'   => '-',
                'locality'  => '-',
            ];
        }

        $addr = $addresses[0];

        return [
            'label'     => $addr['label']     ?? '-',
            'address'   => $addr['address']   ?? '-',
            'addressAs' => $addr['addressAs'] ?? '-',
            'landmark'  => $addr['landmark']  ?? '-',
            'city'      => $addr['city']      ?? '-',
            'pincode'   => $addr['pincode']   ?? '-',
            'locality'  => $addr['locality']  ?? '-',
        ];
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
    private function exportCSV($users)
    {
        return new StreamedResponse(function () use ($users) {
            $handle = fopen('php://output', 'w');

            // CSV Header
            fputcsv($handle, [
                'user_id',
                'Name',
                'Email',
                'Phone',
                'Zone',
                'address',
                'count',
                'last_order_date'
            ]);

            foreach ($users as $user) {

                // Format createdAt with Asia/Kolkata timezone
                $createdAtFormatted = '';
                if ($user->createdAt) {
                    try {
                        $dateStr = is_string($user->createdAt) ? trim($user->createdAt, '"') : $user->createdAt;
                        $date = Carbon::parse($dateStr);
                        $date->setTimezone('Asia/Kolkata');
                        $createdAtFormatted = $date->format('M d, Y h:i A');
                    } catch (\Exception $e) {
                        $createdAtFormatted = '';
                    }
                }

                fputcsv($handle, [
                    $user->user_id,
                    trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')),
                    $user->email ?? '',
                    $user->phoneNumber ?? '',
                    $user->zone_name ?? '-',
                    $user->zone_id ?? '-',
                    $user->address ?? '-',
                    $user->order_count,
                    $user->last_order_date ?? 'Not Assigned',
                ]);

            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reportUsers.csv"',
        ]);
    }

    private function exportExcel($users)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ReportUserExport($users),
            'reportUsers.xlsx'
        );
    }

    public function export(Request $request)
    {
        $query = AppUser::query();

        // Zone filter - search in shippingAddress JSON column (same as index method)
        $zoneId = $request->input('zoneId', $request->query('zoneId'));
        if (!empty($zoneId) && $zoneId !== '') {
            $query->where(function($q) use ($zoneId) {
                // Use JSON_EXTRACT for better performance (MySQL 5.7+)
                $q->whereRaw('JSON_SEARCH(shippingAddress, "one", ?, NULL, "$[*].zoneId") IS NOT NULL', [$zoneId])
                    ->orWhereRaw('JSON_EXTRACT(shippingAddress, "$[0].zoneId") = ?', [$zoneId])
                    ->orWhere('shippingAddress', 'like', "%\"zoneId\":\"$zoneId\"%"); // Fallback for older MySQL
            });
        }


        // Search
        $search = trim((string) ($request->input('search', $request->query('search', ''))));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('firstName', 'like', "%$search%")
                    ->orWhere('lastName', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phoneNumber', 'like', "%$search%");
            });
        }

        // ORDER - Only fetch needed columns for export
        $users = $query->select(
            'id',
            'firebase_id',
            'firstName',
            'lastName',
            'email',
            'phoneNumber',
            'shippingAddress',
            'active',
            'isActive',
            'createdAt',
        )->orderByDesc('id')->get();
//
//        // Extract zoneId from shippingAddress and fetch zone names
        $zoneIds = [];
        foreach ($users as $user) {
            $zoneId = \App\Http\Controllers\UserController::extractZoneFromShippingAddress($user->shippingAddress);
            if (!empty($zoneId)) {
                $zoneIds[] = $zoneId;
            }
        }
//
//        // Fetch zone names for all zoneIds
        $zones = [];
        if (!empty($zoneIds)) {
            $zoneRecords = DB::table('zone')
                ->whereIn('id', array_unique($zoneIds))
                ->pluck('name', 'id')
                ->toArray();
            $zones = $zoneRecords;
        }


        $users = $users->map(function ($user) use ($zones) {

            // user_id
            $user->user_id = $user->firebase_id ?: $user->id;

            // zone
            $zoneId = \App\Http\Controllers\UserController::extractZoneFromShippingAddress($user->shippingAddress);
//            $user->zone_id = $zoneId;
            $user->zone_name = !empty($zoneId) && isset($zones[$zoneId])
                ? $zones[$zoneId]
                : '-';

            // address (safe helper)
            $addressData = $this->extractAddressFields($user->shippingAddress);
            $user->address = $addressData['address'] . ', ' . $addressData['locality'];

            // order data (MUST exist in query or preloaded)
            $user->order_count = $user->order_count ?? 0;
            $user->last_order_date = $user->last_order_date ?? '-';

            return $user;
        });


        if ($request->type === 'pdf' && $users->count() > 500) {
            return response()->json([
                'success' => false,
                'message' => 'PDF export is limited to 200 records. Use Excel or CSV for full data.'
            ], 422);
        }

        return match ($request->type) {
            'csv'   => $this->exportCSV($users),
            'excel' => $this->exportExcel($users),
            default => abort(404),
        };
    }
}

?>
