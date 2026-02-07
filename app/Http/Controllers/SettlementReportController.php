<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MerchantSettlementExport;
use App\Models\VendorSettlement;
use App\Models\DriverSettlement;
class SettlementReportController extends Controller
{
    /**
     * Normalize incoming UI dates to a safe SQL datetime range.
     */
    private function normalizeDateRange(string $startInput, string $endInput): array
    {
        $parse = function (string $value): Carbon {
            $v = trim($value, "\"' \t\n\r\0\x0B");

            if ($v === '') {
                return Carbon::now();
            }

            if (is_numeric($v)) {
                $ts = (int) $v;
                if (strlen($v) > 10) {
                    $ts = (int) floor($ts / 1000);
                }
                return Carbon::createFromTimestamp($ts);
            }

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                return Carbon::createFromFormat('Y-m-d', $v);
            }

            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $v)) {
                return Carbon::createFromFormat('d-m-Y', $v);
            }

            if (preg_match('/^\d{2}-\d{2}-\d{2}$/', $v)) {
                return Carbon::createFromFormat('d-m-y', $v);
            }

            return Carbon::parse($v);
        };

        $from = $parse($startInput)->startOfDay();
        $to = $parse($endInput)->endOfDay();

        return [
            $from->format('Y-m-d H:i:s'),
            $to->format('Y-m-d H:i:s'),
            $from->format('Y-m-d'),
            $to->format('Y-m-d'),
        ];
    }

    /**
     * Get the date expression for flexible createdAt parsing
     */
    private function getDateExpression(string $alias = 'ro'): string
    {
        // NOTE: restaurant_orders.createdAt is stored as TEXT in multiple formats:
        // - unix seconds / milliseconds (numeric string)
        // - ISO8601 (e.g. 2025-11-23T14:21:30.422489Z)
        // - SQL datetime string (e.g. 2025-11-23 14:21:30)
        // This CASE normalizes it to a DATETIME for whereBetween filtering.
        $col = $alias . '.createdAt';

        return "
CASE
    WHEN {$col} REGEXP '^[0-9]+$'
        THEN FROM_UNIXTIME(
            CASE WHEN LENGTH({$col}) > 10
                THEN {$col} / 1000
                ELSE {$col}
            END
        )
    WHEN {$col} LIKE '%T%'
        THEN STR_TO_DATE(
        REPLACE(REPLACE({$col},'Z',''),'T',' '),
        '%Y-%m-%d %H:%i:%s.%f'
    )
    WHEN STR_TO_DATE({$col}, '%Y-%m-%d %H:%i:%s') IS NOT NULL
        THEN STR_TO_DATE({$col}, '%Y-%m-%d %H:%i:%s')
    WHEN STR_TO_DATE({$col}, '%Y-%m-%d') IS NOT NULL
        THEN STR_TO_DATE({$col}, '%Y-%m-%d')
    ELSE NULL
END
";

    }

    private function getWeeksForYear(int $year): array
    {
        $weeks = [];

        // First Monday of the year
        $date = Carbon::create($year, 1, 1)->startOfWeek(Carbon::MONDAY);

        // Ensure first week belongs to this year
        if ($date->year < $year) {
            $date->addWeek();
        }

        $weekNumber = 1;

        while ($date->year === $year) {
            $start = $date->copy();
            $end   = $date->copy()->endOfWeek(Carbon::SUNDAY);

            $weeks[] = [
                'label' => sprintf(
                    '%d-W%02d (%s - %s)',
                    $year,
                    $weekNumber,
                    $start->format('d M'),
                    $end->format('d M')
                ),
                'week_code' => $year . '-W' . str_pad($weekNumber, 2, '0', STR_PAD_LEFT) . '-V',
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ];

            $date->addWeek();
            $weekNumber++;
        }

        return $weeks;
    }

    /**
     * Get completed order statuses
     */
    private function getCompletedStatuses(): array
    {
        return [
            'restaurantorders Completed',
            'Order Completed',
            'Completed',
            'completed',
            'Driver Completed',
        ];
    }

    /**
     * Check if GST is enabled for a vendor
     * GST is ON if vendor's gst column is 1 (from vendors table)
     */
    private function isGstEnabled($vendor): bool
    {
        if (!$vendor) {
            return false;
        }

        // Check vendor's gst column (1 = GST accepted/ON, 0 = GST unaccepted/OFF)
        $gst = is_object($vendor) ? ($vendor->gst ?? 0) : ($vendor['gst'] ?? 0);
        return (int)$gst === 1;
    }

    /**
     * Get product price considering promotions
     *
     * FINAL SETTLEMENT RULE:
     * GOLDEN RULE: restaurant_orders.promotion = 1 is the source of truth
     *
     * Step 1: Check restaurant_orders.promotion first (order is source of truth)
     *   - If order.promotion = 1 → use special_price (regardless of promotions.promo)
     *   - If order.promotion = 0 → check promotions.promo
     *     - If promotions.promo = 1 → use special_price
     *     - If promotions.promo = 0 or no promotion → use merchant_price
     *
     * Note: restaurant_orders.products is JSON, so product_id comes from JSON array item['id']
     *
     * @param string $productId Product ID (from JSON products array item['id'])
     * @param string $vendorId Restaurant/Vendor ID
     * @param string $orderDate Order date (for checking promotion date range)
     * @param float $merchantPrice Base merchant price from vendor_products table
     * @param array|null $promotionsCache Optional cache for faster lookups
     * @param int|null $orderPromotion Order-level promotion flag (restaurant_orders.promotion) - source of truth
     * @return float Settlement base price (special_price if order.promotion=1 or promo=1, else merchant_price)
     */
    private function getProductPrice($productId, $vendorId, $orderDate, $merchantPrice, $promotionsCache = null, $orderPromotion = null): float
    {
        // GOLDEN RULE: restaurant_orders.promotion = 1 is the source of truth
        $orderHasPromotion = (int)($orderPromotion ?? 0) === 1;

        // Match: promotions.restaurant_id = restaurant_orders.vendorID
        // Match: promotions.product_id = products JSON item['id']

        // If promotions lookup array is provided, use it; otherwise fetch fresh from DB
        if ($promotionsCache === null) {
            // Fetch fresh promotion data directly from database (NO CACHE)
            $promotion = DB::table('promotions')
                ->where('product_id', $productId)        // promotions.product_id = products JSON item['id']
                ->where('restaurant_id', $vendorId)      // promotions.restaurant_id = restaurant_orders.vendorID
                ->first(); // Fresh query - always gets latest data from database
        } else {
            // Use in-memory lookup array (built fresh from database in current request)
            $lookupKey = $vendorId . '_' . $productId;
            $promotion = $promotionsCache[$lookupKey] ?? null;
        }

        // FINAL RULE: Use promo price ONLY when BOTH are true:
        // promotions.promo = 1 AND restaurant_orders.promotion = 1
        // Otherwise, always use merchant_price
        $restaurantAcceptsPromo = $promotion && (int)($promotion->promo ?? 0) === 1;

        if ($orderHasPromotion && $restaurantAcceptsPromo) {
            // BOTH conditions true: promotions.promo = 1 AND order.promotion = 1
            // Note: This method is called with merchantPrice, but the actual promo price
            // should come from the order JSON (item['price']). This method is a fallback.
            // The actual logic is in calculateOrderSettlement which uses item['price'] from JSON.
            return (float)$merchantPrice; // Fallback - actual promo price comes from JSON in calculateOrderSettlement
        }

        // In all other cases, use merchant_price
        return (float)$merchantPrice;
    }

    /**
     * Calculate settlement amount for an order with correct promotion and commission logic
     * GOLDEN RULE: Commission NEVER applies to promotional items
     *
     * COMMISSION PLAN RULE: If plan_name = "Commission Plan" → DO NOT deduct commission
     * Commission Plan means commission is handled elsewhere (subscription/fixed), not per-order deduction
     *
     * FINAL RULE: restaurant_orders.promotion = 1 is the source of truth
     * If order.promotion = 1 → treat as promotional, use special_price, skip commission
     *
     * @param array $items Order items from JSON
     * @param object $vendor Vendor object
     * @param object $vendorProducts Collection of vendor products
     * @param string $orderDate Order date
     * @param array $promotionsCache Promotions lookup array
     * @param bool $hasPlan Whether vendor has subscription plan
     * @param float $commission Commission percentage
     * @param bool $isGstOn Whether GST is enabled
     * @param int|null $orderPromotion Order-level promotion flag (restaurant_orders.promotion) - source of truth
     * @param string|null $planName Plan name/type (e.g., "Commission Plan", "Subscription")
     * @return array ['merchant_total' => float, 'promotion_total' => float, 'settlement' => float]
     */
    private function calculateOrderSettlement($items, $vendor, $vendorProducts, $orderDate, $promotionsCache, $hasPlan, $commission, $isGstOn, $orderPromotion = null, $planName = null): array
    {
        $merchantTotal = 0;      // Non-promotional items (merchant_price)
        $promotionTotal = 0;      // Promotional items (special_price)

        // GOLDEN RULE: restaurant_orders.promotion = 1 is the source of truth
        // If order.promotion = 1 → treat ALL items in this order as promotional
        $orderHasPromotion = (int)($orderPromotion ?? 0) === 1;

        foreach ($items as $item) {
            $product = $vendorProducts->firstWhere('id', $item['id'] ?? null);
            if (!$product) continue;

            $qty = $item['quantity'] ?? 1;
            $merchantPrice = $product->merchant_price ?? 0;

            // FINAL RULE: Use promo price ONLY when BOTH are true:
            // promotions.promo = 1 AND restaurant_orders.promotion = 1
            // Otherwise, always use merchant_price
            $cacheKey = $vendor->id . '_' . $product->id;
            $promotion = $promotionsCache[$cacheKey] ?? null;
            $restaurantAcceptsPromo = $promotion && (int)($promotion->promo ?? 0) === 1;

            if ($orderHasPromotion && $restaurantAcceptsPromo) {
                // BOTH conditions true: promotions.promo = 1 AND order.promotion = 1
                // Use the price from order JSON (frozen at order time)
                // This is the promo price that was stored when the order was placed
                $promoPrice = (float)($item['price'] ?? $merchantPrice);
                $promotionTotal += ($promoPrice * $qty);
            } else {
                // In all other cases, use merchant_price
                $merchantTotal += ($merchantPrice * $qty);
            }
        }

        // Calculate settlement
        // FINAL RULE: Commission logic based on promotions.promo when order.promotion = 1
        // If order.promotion = 1 AND promotions.promo = 1 → NO commission (use special_price, promotionTotal > 0)
        // If order.promotion = 1 AND promotions.promo = 0 → APPLY commission (use merchant_price, promotionTotal = 0)
        // If order.promotion = 0 → APPLY commission (use merchant_price)
        $settlementBase = $merchantTotal + $promotionTotal;
        $settlementAmount = $settlementBase;

        // Determine if commission should be applied
        // If orderHasPromotion and promotionTotal > 0 → promotions.promo = 1 (NO commission)
        // If orderHasPromotion and promotionTotal = 0 → promotions.promo = 0 (APPLY commission)
        // If !orderHasPromotion → APPLY commission
        $shouldApplyCommission = !$orderHasPromotion || $promotionTotal === 0;

        // COMMISSION PLAN RULE: If plan_name = "Commission Plan" (case-insensitive) → DO NOT deduct commission
        // Commission Plan means commission is handled elsewhere (subscription/fixed), not per-order deduction
        // Handles all case variations: "Commission Plan", "COMMISSION PLAN", "commission plan", etc.
        $isCommissionPlan = !empty($planName) && strtolower(trim((string)$planName)) === 'commission plan';

        // Apply commission if needed (skip if Commission Plan)
        if ($hasPlan && $commission > 0 && $shouldApplyCommission && !$isCommissionPlan) {
            // Apply commission to merchantTotal (non-promotional items)
            $settlementAmount -= $merchantTotal * ($commission / 100);
        }

        // GST applies to everything (both promotional and non-promotional items)
        if ($isGstOn) {
            $settlementAmount -= $settlementBase * 0.05;
        }

        // Round final value
        $settlementAmount = round($settlementAmount);

        return [
            'merchant_total' => $merchantTotal,
            'promotion_total' => $promotionTotal,
            'settlement' => $settlementAmount
        ];
    }

    /**
     * Build promotions lookup array for faster lookups during request
     * Returns array with key "vendorId_productId" => promotion object
     * Always fetches fresh data from database (NO CACHING - always fresh)
     */
    private function buildPromotionsCache($vendorIds, $productIds, $startDate, $endDate): array
    {
        if (empty($vendorIds) || empty($productIds)) {
            return [];
        }

        // IMPORTANT: Always fetch fresh data - NO caching mechanism
        // This ensures we always get the latest promo status (0 or 1) and special_price
        // Match: promotions.restaurant_id = restaurant_orders.vendorID
        // Match: promotions.product_id = products JSON item['id']
        // Fetch ALL promotions (both promo = 0 and promo = 1) so we can check promo value

        // Use fresh query - no Laravel Cache, no query cache, always from database
        $promotions = DB::table('promotions')
            ->whereIn('restaurant_id', $vendorIds) // promotions.restaurant_id = restaurant_orders.vendorID
            ->whereIn('product_id', $productIds)   // promotions.product_id = products JSON item['id']
            ->get(); // Fresh database query - always gets latest data (includes both promo = 0 and promo = 1)

        // Build in-memory lookup array (not persistent cache - only for current request)
        $lookup = [];
        foreach ($promotions as $promo) {
            // Lookup key: "restaurant_id_product_id" (e.g., "6rTOtZwP950irmDiqLpF_08ZCxheEWvQsS1uEKSD6")
            $lookupKey = $promo->restaurant_id . '_' . $promo->product_id;
            $lookup[$lookupKey] = $promo; // Store promotion with promo value (0 or 1) and special_price
        }

        return $lookup; // Returns in-memory array, not cached data
    }

    /*
    |--------------------------------------------------------------------------
    | SETTLEMENT WEEKS LIST
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $query = DB::table('settlement_weeks');

//        if ($request->has('start_date') && $request->has('end_date')) {
//            $startDate = $request->input('start_date');
//            $endDate = $request->input('end_date');
//
//            [, , $startDateOnly, $endDateOnly] = $this->normalizeDateRange((string)$startDate, (string)$endDate);
//            $startDate = $startDateOnly;
//            $endDate = $endDateOnly;
//
//            $query->where(function($q) use ($startDate, $endDate) {
//                $q->where(function($subQ) use ($startDate, $endDate) {
//                    $subQ->whereDate('week_start_date', '>=', $startDate)
//                        ->whereDate('week_start_date', '<=', $endDate);
//                })->orWhere(function($subQ) use ($startDate, $endDate) {
//                    $subQ->whereDate('week_end_date', '>=', $startDate)
//                        ->whereDate('week_end_date', '<=', $endDate);
//                })->orWhere(function($subQ) use ($startDate, $endDate) {
//                    $subQ->whereDate('week_start_date', '<=', $startDate)
//                        ->whereDate('week_end_date', '>=', $endDate);
//                });
//            });
//        }

        $weeks = $query->orderBy('week_start_date', 'desc')->get();

        $year = (int) request('year', now()->year);
        $weeks2026 = $this->getWeeksForYear($year);


        return view('reports.merchantSettlement', compact('weeks','weeks2026'));
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER WEEKS BY DATE RANGE (AJAX)
    |--------------------------------------------------------------------------
    */
    public function filterWeeks(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Start date and end date are required'], 400);
        }

        [, , $startDateOnly, $endDateOnly] = $this->normalizeDateRange((string)$startDate, (string)$endDate);
        $startDate = $startDateOnly;
        $endDate = $endDateOnly;

        $weeks = DB::table('settlement_weeks')
            ->where(function($q) use ($startDate, $endDate) {
                $q->where(function($subQ) use ($startDate, $endDate) {
                    $subQ->whereDate('week_start_date', '>=', $startDate)
                        ->whereDate('week_start_date', '<=', $endDate);
                })->orWhere(function($subQ) use ($startDate, $endDate) {
                    $subQ->whereDate('week_end_date', '>=', $startDate)
                        ->whereDate('week_end_date', '<=', $endDate);
                })->orWhere(function($subQ) use ($startDate, $endDate) {
                    $subQ->whereDate('week_start_date', '<=', $startDate)
                        ->whereDate('week_end_date', '>=', $endDate);
                });
            })
            ->orderBy('week_start_date', 'desc')
            ->get();

        return response()->json($weeks);
    }

    /*
    |--------------------------------------------------------------------------
    | GET VENDORS BY DATE RANGE (Fixed version)
    |--------------------------------------------------------------------------
    */
    public function getVendorsByDateRange(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Start date and end date are required'], 400);
        }

        // Normalize dates
        [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$startDate, (string)$endDate);
        if ($request->boolean('debug')) {
            Log::info('Settlement vendors-by-date', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'normalized' => [$startDateTime, $endDateTime],
            ]);
        }

        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        // Get vendors with orders in date range - join with subscription_plans and zone tables
        $vendorsQuery = DB::table('restaurant_orders as ro')
            ->join('vendors as v', 'v.id', '=', 'ro.vendorID')
            ->leftJoin('subscription_plans as sp', 'sp.id', '=', 'v.subscriptionPlanId')
            ->leftJoin('zone as z', 'z.id', '=', 'v.zoneId')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);

        // Apply zone filter if provided
        if ($request->has('zone_id') && !empty($request->zone_id)) {
            $vendorsQuery->where('v.zoneId', $request->zone_id);
        }

        $vendors = $vendorsQuery
            ->groupBy('v.id', 'v.title', 'v.phonenumber', 'v.subscriptionPlanId', 'v.gst', 'v.zoneId', 'sp.name', 'sp.place', 'z.name')
            ->select('v.id', 'v.title', 'v.phonenumber', 'v.subscriptionPlanId', 'v.gst', 'v.zoneId', 'sp.name as plan_name', 'sp.place as commission', 'z.name as zone')
            ->get();

        // Get all orders in date range
        $ordersDateExpr = $this->getDateExpression('o');
        $orders = DB::table('restaurant_orders as o')
            ->whereIn('o.status', $completedStatuses)
            ->whereBetween(DB::raw("($ordersDateExpr)"), [$startDateTime, $endDateTime])
            ->select('o.*')
            ->get()
            ->groupBy('vendorID');

        // Get vendor products
        $products = DB::table('vendor_products')
            ->select('id', 'vendorID', 'price', 'merchant_price', 'name')
            ->get()
            ->groupBy('vendorID');

        // Build promotions cache for all vendors and products
        $allVendorIds = $vendors->pluck('id')->toArray();
        $allProductIds = $products->pluck('id')->unique()->toArray();
        $promotionsCache = $this->buildPromotionsCache($allVendorIds, $allProductIds, $startDateTime, $endDateTime);

        $final = [];

        foreach ($vendors as $vendor) {
            $vendorOrders = $orders[$vendor->id] ?? collect();
            $vendorProducts = $products[$vendor->id] ?? collect();

            $merchantTotal = 0;
            $settlementTotal = 0; // Sum of settlement amounts from all orders
            $customerPaid = 0;

            // Check GST status for this vendor (vendor-level setting)
            $isGstOn = $this->isGstEnabled($vendor);

            // Commission calculation - fetch directly from subscription_plans table
            $hasPlan = !empty($vendor->subscriptionPlanId) && !empty($vendor->plan_name);
            if ($hasPlan) {
                $commission = (float) ($vendor->commission ?? 0);
                $planName = $vendor->plan_name ?? 'Subscription';
            } else {
                $commission = 0; // No commission deduction if no plan
                $planName = 'Commission Plan';
            }

            $totalPromotionPrice = 0; // Track total promotion price for display

            foreach ($vendorOrders as $order) {
                $items = json_decode($order->products, true) ?? [];
                $orderDate = $order->createdAt ?? now();

                // Use helper method to calculate settlement with correct promotion/commission logic
                // GOLDEN RULE: Commission NEVER applies to promotional items
                // COMMISSION PLAN RULE: If plan_name = "Commission Plan" → DO NOT deduct commission
                // FINAL RULE: restaurant_orders.promotion = 1 is the source of truth
                $orderCalc = $this->calculateOrderSettlement(
                    $items,
                    $vendor,
                    $vendorProducts,
                    $orderDate,
                    $promotionsCache,
                    $hasPlan,
                    $commission,
                    $isGstOn,
                    $order->promotion ?? null, // Pass order-level promotion flag (source of truth)
                    $planName // Pass plan name to check if it's "Commission Plan"
                );

                $merchantTotal += $orderCalc['merchant_total'];
                $totalPromotionPrice += $orderCalc['promotion_total'];
                $settlementTotal += $orderCalc['settlement'];
                $customerPaid += $order->toPayAmount;
            }

            // Commission for profit calculation (use 30% default if no plan for profit calculation only)
            $commissionForProfit = $hasPlan ? $commission : 30;

            // Calculate profit (commission on merchant total only, not on promotion total)
            $jippyProfit = round($merchantTotal * ($commission / 100), 2);
            // Settlement amount is the sum of settlement amounts from all orders (with correct promotion/commission/GST logic)
            // This is calculated using calculateOrderSettlement which handles:
            // - order.promotion = 1 AND promotions.promo = 1 → special_price, NO commission
            // - order.promotion = 1 AND promotions.promo = 0 → merchant_price, APPLY commission
            // - order.promotion = 0 → merchant_price, APPLY commission
            // - GST deduction (5% if gst = 1)
            $settlementAmount = round($settlementTotal, 2);

            // Get saved vendor settlement data if exists
            // First, try to find settlement_week for this date range
            $startDateCarbon = Carbon::parse($startDate);
            $endDateCarbon = Carbon::parse($endDate);

            $settlementWeek = DB::table('settlement_weeks')
                ->where('week_start_date', $startDateCarbon->toDateString())
                ->where('week_end_date', $endDateCarbon->toDateString())
                ->first();

            $savedSettlement = null;
            if ($settlementWeek) {
                $savedSettlement = DB::table('vendor_settlements')
                    ->where('settlement_week_id', $settlementWeek->id)
                    ->where('vendor_id', $vendor->id)
                    ->select('transaction_id', 'payment_status', 'payment_comments', 'payment_date')
                    ->first();
            }

            $final[] = [
                'vendor_id'         => $vendor->id,
                'vendor_name'       => $vendor->title,
                'phone'             => $vendor->phonenumber,
                'plan_name'         => $planName,
                'commission'        => $commission,
                'orders_count'      => $vendorOrders->count(),
                'merchant_price'    => round($merchantTotal, 2), // Non-promotional items total
                'promotion_price'   => round($totalPromotionPrice, 2), // Promotional items total (special_price)
                'total_price'       => round($merchantTotal + $totalPromotionPrice, 2), // Total price (merchant + promotion)
                'customer_paid'     => $customerPaid,
                'jippy_profit'      => $jippyProfit,
                'settlement_amount' => $settlementAmount, // Sum of all order settlements (calculated with correct promotion logic)
                'gst'               => (int)($vendor->gst ?? 0), // 1 = accepted, 0 = unaccepted
                'zone'              => $vendor->zone ?? 'N/A', // Zone name from zone table
                'saved_settlement' => $savedSettlement ? [
                    'transaction_id' => $savedSettlement->transaction_id,
                    'payment_status' => ucfirst($savedSettlement->payment_status ?? 'pending'),
                    'payment_comments' => $savedSettlement->payment_comments,
                    'payment_date' => $savedSettlement->payment_date,
                ] : null,
            ];
        }

        // Apply status filter if provided
        if ($request->has('status') && !empty($request->status)) {
            $statusFilter = strtolower($request->status);
            $final = array_filter($final, function($vendor) use ($statusFilter) {
                $paymentStatus = null;
                if ($vendor['saved_settlement'] && isset($vendor['saved_settlement']['payment_status'])) {
                    $paymentStatus = strtolower($vendor['saved_settlement']['payment_status']);
                }

                if ($statusFilter === 'pending') {
                    // Show vendors with no settlement or payment_status = 'pending'
                    return $paymentStatus === null || $paymentStatus === 'pending';
                } elseif ($statusFilter === 'settled') {
                    // Show vendors with payment_status = 'settled'
                    return $paymentStatus === 'settled';
                }

                return true;
            });
            // Re-index array after filtering
            $final = array_values($final);
        }

        return response()->json($final);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SUMMARY BY DATE RANGE (Fixed version)
    |--------------------------------------------------------------------------
    */
    public function getSummaryByDateRange(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Start date and end date are required'], 400);
        }

        // Normalize dates
        [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$startDate, (string)$endDate);
        if ($request->boolean('debug')) {
            Log::info('Settlement summary-by-date', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'normalized' => [$startDateTime, $endDateTime],
            ]);
        }

        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        // Get vendors - join with subscription_plans and zone tables
        $vendorsQuery = DB::table('restaurant_orders as ro')
            ->join('vendors as v', 'v.id', '=', 'ro.vendorID')
            ->leftJoin('subscription_plans as sp', 'sp.id', '=', 'v.subscriptionPlanId')
            ->leftJoin('zone as z', 'z.id', '=', 'v.zoneId')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);

        // Apply zone filter if provided
        if ($request->has('zone_id') && !empty($request->zone_id)) {
            $vendorsQuery->where('v.zoneId', $request->zone_id);
        }

        $vendors = $vendorsQuery
            ->groupBy('v.id', 'v.title', 'v.subscriptionPlanId', 'v.gst', 'v.zoneId', 'sp.name', 'sp.place', 'z.name')
            ->select('v.id', 'v.title', 'v.subscriptionPlanId', 'v.gst', 'v.zoneId', 'sp.name as plan_name', 'sp.place as commission', 'z.name as zone')
            ->get();

        // Get orders
        $ordersDateExpr = $this->getDateExpression('o');
        $orders = DB::table('restaurant_orders as o')
            ->whereIn('o.status', $completedStatuses)
            ->whereBetween(DB::raw("($ordersDateExpr)"), [$startDateTime, $endDateTime])
            ->select('o.*')
            ->get()
            ->groupBy('vendorID');

        // Get products
        $products = DB::table('vendor_products')
            ->select('id', 'vendorID', 'price', 'merchant_price')
            ->get()
            ->groupBy('vendorID');

        // Build promotions cache
        $allVendorIds = $vendors->pluck('id')->toArray();
        $allProductIds = $products->pluck('id')->unique()->toArray();
        $promotionsCache = $this->buildPromotionsCache($allVendorIds, $allProductIds, $startDateTime, $endDateTime);

        $totalSettlement = 0;
        $totalProfit = 0;
        $totalOrders = 0;

        foreach ($vendors as $vendor) {
            $vendorOrders = $orders[$vendor->id] ?? collect();
            $vendorProducts = $products[$vendor->id] ?? collect();

            // Check GST status for this vendor (vendor-level setting)
            $isGstOn = $this->isGstEnabled($vendor);

            // Commission calculation - fetch directly from subscription_plans table
            $hasPlan = !empty($vendor->subscriptionPlanId) && !empty($vendor->plan_name);
            if ($hasPlan) {
                $commission = (float) ($vendor->commission ?? 0);
                $planName = $vendor->plan_name ?? 'Subscription';
            } else {
                $commission = 0; // No commission deduction if no plan
                $planName = 'Commission Plan';
            }

            // COMMISSION PLAN RULE: If plan_name = "Commission Plan" (case-insensitive) → DO NOT deduct commission
            // Handles all case variations: "Commission Plan", "COMMISSION PLAN", "commission plan", etc.
            $isCommissionPlan = !empty($planName) && strtolower(trim((string)$planName)) === 'commission plan';

            $merchantTotal = 0;
            $settlementTotal = 0; // Sum of settlement amounts from all orders
            foreach ($vendorOrders as $order) {
                $totalOrders++;
                // GOLDEN RULE: restaurant_orders.promotion = 1 is the source of truth
                $orderHasPromotion = (int)($order->promotion ?? 0) === 1;
                $orderMerchantTotal = 0;
                $orderPromotionTotal = 0;
                $items = json_decode($order->products, true) ?? [];
                $orderDate = $order->createdAt ?? now();

                foreach ($items as $item) {
                    $product = $vendorProducts->firstWhere('id', $item['id'] ?? null);
                    if (!$product) continue;
                    $qty = $item['quantity'] ?? 1;
                    $merchantPrice = $product->merchant_price ?? 0;

                    // STEP 1: Promotion check (FIRST) - use special_price if order.promotion=1 or promo=1, else merchant_price
                    // GOLDEN RULE: restaurant_orders.promotion = 1 is the source of truth
                    $price = $this->getProductPrice($product->id, $vendor->id, $orderDate, $merchantPrice, $promotionsCache, $order->promotion ?? null);

                    // Separate promotional and non-promotional items
                    if ($orderHasPromotion) {
                        // Order has promotion = 1 → treat as promotional
                        $cacheKey = $vendor->id . '_' . $product->id;
                        $promotion = $promotionsCache[$cacheKey] ?? null;
                        if ($promotion) {
                            $orderDateCarbon = Carbon::parse($orderDate);
                            $startTime = Carbon::parse($promotion->start_time);
                            $endTime = Carbon::parse($promotion->end_time);
                            if ($orderDateCarbon->between($startTime, $endTime)) {
                                $orderPromotionTotal += ($price * $qty);
                            } else {
                                $orderMerchantTotal += ($price * $qty);
                            }
                        } else {
                            $orderMerchantTotal += ($price * $qty);
                        }
                    } else {
                        // Check if item has promotion based on promotions.promo
                        $cacheKey = $vendor->id . '_' . $product->id;
                        $promotion = $promotionsCache[$cacheKey] ?? null;
                        if ($promotion && (int)($promotion->promo ?? 0) === 1) {
                            $orderDateCarbon = Carbon::parse($orderDate);
                            $startTime = Carbon::parse($promotion->start_time);
                            $endTime = Carbon::parse($promotion->end_time);
                            if ($orderDateCarbon->between($startTime, $endTime)) {
                                $orderPromotionTotal += ($price * $qty);
                            } else {
                                $orderMerchantTotal += ($price * $qty);
                            }
                        } else {
                            $orderMerchantTotal += ($price * $qty);
                        }
                    }
                }

                // Calculate settlement amount per order
                // GOLDEN RULE: Commission NEVER applies to promotional items
                // COMMISSION PLAN RULE: If plan_name = "Commission Plan" → DO NOT deduct commission
                $settlementBase = $orderMerchantTotal + $orderPromotionTotal;
                $orderSettlementAmount = $settlementBase;

                // Apply commission ONLY to non-promotional items (skip if Commission Plan)
                if ($hasPlan && $commission > 0 && !$isCommissionPlan) {
                    $orderSettlementAmount -= $orderMerchantTotal * ($commission / 100);
                }

                // STEP 2: GST check (SECOND) - deduct 5% if gst = 1, else 0%
                // GST applies to everything (both promotional and non-promotional items)
                if ($isGstOn) {
                    $orderSettlementAmount -= $settlementBase * 0.05;
                }

                // STEP 3: Round final value
                $orderSettlementAmount = round($orderSettlementAmount);

                $merchantTotal += $orderMerchantTotal;
                $settlementTotal += $orderSettlementAmount;
            }

            // Commission for profit calculation (use 30% default if no plan for profit calculation only)
            $commissionForProfit = $hasPlan ? $commission : 30;

            // Calculate profit (commission on merchant total)
            $jippyProfit = round($merchantTotal * ($commissionForProfit / 100), 2);
            // Settlement amount is the sum of settlement amounts from all orders (with commission and GST deductions if applicable)
            $settlementAmount = round($settlementTotal, 2);

            $totalSettlement += $settlementAmount;
            $totalProfit += $jippyProfit;
        }

        // Get week status if exists
        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);
        $week = DB::table('settlement_weeks')
            ->where('week_start_date', $startDateCarbon->toDateString())
            ->where('week_end_date', $endDateCarbon->toDateString())
            ->first();

        return response()->json([
            'vendors'          => $vendors->count(),
            'orders'           => $totalOrders,
            'total_settlement' => round($totalSettlement, 2),
            'total_profit'     => round($totalProfit, 2),
            'week_status'      => $week ? $week->status : 'open',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | WEEK → VENDORS (AJAX)
    |--------------------------------------------------------------------------
    */
    public function weekVendors($weekId)
    {
        $week = DB::table('settlement_weeks')->find($weekId);

        if (!$week) {
            return response()->json([]);
        }

        [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$week->week_start_date, (string)$week->week_end_date);

        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        $vendors = DB::table('restaurant_orders as ro')
            ->join('vendors as v', 'v.id', '=', 'ro.vendorID')
            ->leftJoin('subscription_plans as sp', 'sp.id', '=', 'v.subscriptionPlanId')
            ->leftJoin('zone as z', 'z.id', '=', 'v.zoneId')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->groupBy('v.id', 'v.title', 'v.phonenumber', 'v.subscriptionPlanId', 'v.gst', 'v.zoneId', 'sp.name', 'sp.place', 'z.name')
            ->select('v.id', 'v.title', 'v.phonenumber', 'v.subscriptionPlanId', 'v.gst', 'v.zoneId', 'sp.name as plan_name', 'sp.place as commission', 'z.name as zone')
            ->get();

        $ordersDateExpr = $this->getDateExpression('o');
        $orders = DB::table('restaurant_orders as o')
            ->whereIn('o.status', $completedStatuses)
            ->whereBetween(DB::raw("($ordersDateExpr)"), [$startDateTime, $endDateTime])
            ->select('o.*')
            ->get()
            ->groupBy('vendorID');

        $products = DB::table('vendor_products')
            ->select('id', 'vendorID', 'price', 'merchant_price', 'name')
            ->get()
            ->groupBy('vendorID');

        // Build promotions cache
        $allVendorIds = $vendors->pluck('id')->toArray();
        $allProductIds = $products->pluck('id')->unique()->toArray();
        $promotionsCache = $this->buildPromotionsCache($allVendorIds, $allProductIds, $startDateTime, $endDateTime);

        $final = [];

        foreach ($vendors as $vendor) {
            $vendorOrders = $orders[$vendor->id] ?? collect();
            $vendorProducts = $products[$vendor->id] ?? collect();

            // Check GST status for this vendor (vendor-level setting)
            $isGstOn = $this->isGstEnabled($vendor);

            // Commission calculation - fetch directly from subscription_plans table
            $hasPlan = !empty($vendor->subscriptionPlanId) && !empty($vendor->plan_name);
            if ($hasPlan) {
                $commission = (float) ($vendor->commission ?? 0);
                $planName = $vendor->plan_name ?? 'Subscription';
            } else {
                $commission = 0; // No commission deduction if no plan
                $planName = 'Commission Plan';
            }

            $merchantTotal = 0;
            $settlementTotal = 0; // Sum of settlement amounts from all orders
            $customerPaid = 0;

            foreach ($vendorOrders as $order) {
                // Use helper method to calculate settlement with correct promotion/commission logic
                // GOLDEN RULE: Commission NEVER applies to promotional items
                // FINAL RULE: restaurant_orders.promotion = 1 is the source of truth
                $items = json_decode($order->products, true) ?? [];
                $orderDate = $order->createdAt ?? now();

                $orderCalc = $this->calculateOrderSettlement(
                    $items,
                    $vendor,
                    $vendorProducts,
                    $orderDate,
                    $promotionsCache,
                    $hasPlan,
                    $commission,
                    $isGstOn,
                    $order->promotion ?? null, // Pass order-level promotion flag (source of truth)
                    $planName // Pass plan name to check if it's "Commission Plan"
                );

                $merchantTotal += $orderCalc['merchant_total'];
                $settlementTotal += $orderCalc['settlement'];
                $customerPaid += $order->toPayAmount;
            }

            // Commission for profit calculation (use 30% default if no plan for profit calculation only)
            $commissionForProfit = $hasPlan ? $commission : 30;

            // Calculate profit (commission on merchant total)
            $jippyProfit = round($merchantTotal * ($commissionForProfit / 100), 2);
            // Settlement amount is the sum of settlement amounts from all orders (with commission and GST deductions if applicable)
            $settlementAmount = round($settlementTotal, 2);

            // Get saved vendor settlement data if exists (for weekVendors, use week_id)
            $savedSettlement = DB::table('vendor_settlements')
                ->where('settlement_week_id', $weekId)
                ->where('vendor_id', $vendor->id)
                ->select('transaction_id', 'payment_status', 'payment_comments', 'payment_date')
                ->first();

            $final[] = [
                'vendor_id'         => $vendor->id,
                'vendor_name'       => $vendor->title,
                'phone'             => $vendor->phonenumber,
                'plan_name'         => $planName,
                'commission'        => $commission,
                'orders_count'      => $vendorOrders->count(),
                'merchant_price'    => $merchantTotal,
                'customer_paid'     => $customerPaid,
                'jippy_profit'      => $jippyProfit,
                'settlement_amount' => $settlementAmount,
                'gst'               => (int)($vendor->gst ?? 0), // 1 = accepted, 0 = unaccepted
                'zone'              => $vendor->zone ?? 'N/A', // Zone name from zone table
                'saved_settlement' => $savedSettlement ? [
                    'transaction_id' => $savedSettlement->transaction_id,
                    'payment_status' => ucfirst($savedSettlement->payment_status ?? 'pending'),
                    'payment_comments' => $savedSettlement->payment_comments,
                    'payment_date' => $savedSettlement->payment_date,
                ] : null,
            ];
        }

        return response()->json($final);
    }

    /*
    |--------------------------------------------------------------------------
    | WEEK SUMMARY STATS (AJAX)
    |--------------------------------------------------------------------------
    */
    public function weekSummary($weekId)
    {
        $week = DB::table('settlement_weeks')->find($weekId);

        if (!$week) {
            return response()->json(['error' => 'Week not found'], 404);
        }

        [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$week->week_start_date, (string)$week->week_end_date);

        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        $vendorsCount = DB::table('restaurant_orders as ro')
            ->join('vendors as v', 'v.id', '=', 'ro.vendorID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->distinct('v.id')
            ->count('v.id');

        $ordersDateExpr = $this->getDateExpression('o');
        $ordersCount = DB::table('restaurant_orders as o')
            ->whereIn('o.status', $completedStatuses)
            ->whereBetween(DB::raw("($ordersDateExpr)"), [$startDateTime, $endDateTime])
            ->count();

        $vendors = DB::table('restaurant_orders as ro')
            ->join('vendors as v', 'v.id', '=', 'ro.vendorID')
            ->leftJoin('subscription_plans as sp', 'sp.id', '=', 'v.subscriptionPlanId')
            ->leftJoin('zone as z', 'z.id', '=', 'v.zoneId')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->groupBy('v.id', 'v.title', 'v.subscriptionPlanId', 'v.gst', 'v.zoneId', 'sp.name', 'sp.place', 'z.name')
            ->select('v.id', 'v.title', 'v.subscriptionPlanId', 'v.gst', 'v.zoneId', 'sp.name as plan_name', 'sp.place as commission', 'z.name as zone')
            ->get();

        $orders = DB::table('restaurant_orders as o')
            ->whereIn('o.status', $completedStatuses)
            ->whereBetween(DB::raw("($ordersDateExpr)"), [$startDateTime, $endDateTime])
            ->select('o.*')
            ->get()
            ->groupBy('vendorID');

        $products = DB::table('vendor_products')
            ->select('id', 'vendorID', 'price', 'merchant_price')
            ->get()
            ->groupBy('vendorID');

        // Build promotions cache
        $allVendorIds = $vendors->pluck('id')->toArray();
        $allProductIds = $products->pluck('id')->unique()->toArray();
        $promotionsCache = $this->buildPromotionsCache($allVendorIds, $allProductIds, $startDateTime, $endDateTime);

        $totalSettlement = 0;
        $totalProfit = 0;

        foreach ($vendors as $vendor) {
            $vendorOrders = $orders[$vendor->id] ?? collect();
            $vendorProducts = $products[$vendor->id] ?? collect();

            // Check GST status for this vendor (vendor-level setting)
            $isGstOn = $this->isGstEnabled($vendor);

            // Commission calculation - fetch directly from subscription_plans table
            $hasPlan = !empty($vendor->subscriptionPlanId) && !empty($vendor->plan_name);
            if ($hasPlan) {
                $commission = (float) ($vendor->commission ?? 0);
                $planName = $vendor->plan_name ?? 'Subscription';
            } else {
                $commission = 0; // No commission deduction if no plan
                $planName = 'Commission Plan';
            }

            // COMMISSION PLAN RULE: If plan_name = "Commission Plan" (case-insensitive) → DO NOT deduct commission
            // Handles all case variations: "Commission Plan", "COMMISSION PLAN", "commission plan", etc.
            $isCommissionPlan = !empty($planName) && strtolower(trim((string)$planName)) === 'commission plan';

            $merchantTotal = 0;
            $settlementTotal = 0; // Sum of settlement amounts from all orders
            foreach ($vendorOrders as $order) {
                $orderMerchantTotal = 0;
                $items = json_decode($order->products, true) ?? [];
                $orderDate = $order->createdAt ?? now();

                foreach ($items as $item) {
                    $product = $vendorProducts->firstWhere('id', $item['id'] ?? null);
                    if (!$product) continue;
                    $qty = $item['quantity'] ?? 1;
                    $merchantPrice = $product->merchant_price ?? 0;

                    // STEP 1: Promotion check (FIRST) - use special_price if promo = 1, else merchant_price
                    $price = $this->getProductPrice($product->id, $vendor->id, $orderDate, $merchantPrice, $promotionsCache);

                    $orderMerchantTotal += ($price * $qty);
                }

                // Calculate settlement amount per order
                // COMMISSION PLAN RULE: If plan_name = "Commission Plan" → DO NOT deduct commission
                $orderSettlementAmount = $orderMerchantTotal;

                // If plan exists, apply commission deduction (skip if Commission Plan)
                if ($hasPlan && $commission > 0 && !$isCommissionPlan) {
                    $orderSettlementAmount -= $orderMerchantTotal * ($commission / 100);
                }

                // STEP 2: GST check (SECOND) - deduct 5% if gst = 1, else 0%
                if ($isGstOn) {
                    $orderSettlementAmount -= $orderMerchantTotal * 0.05;
                }

                // STEP 3: Round final value
                $orderSettlementAmount = round($orderSettlementAmount);

                $merchantTotal += $orderMerchantTotal;
                $settlementTotal += $orderSettlementAmount;
            }

            // Commission for profit calculation (use 30% default if no plan for profit calculation only)
            $commissionForProfit = $hasPlan ? $commission : 30;

            // Calculate profit (commission on merchant total)
            $jippyProfit = round($merchantTotal * ($commissionForProfit / 100), 2);
            // Settlement amount is the sum of settlement amounts from all orders (with commission and GST deductions if applicable)
            $settlementAmount = round($settlementTotal, 2);

            $totalSettlement += $settlementAmount;
            $totalProfit += $jippyProfit;
        }

        return response()->json([
            'vendors' => $vendorsCount,
            'orders' => $ordersCount,
            'total_settlement' => round($totalSettlement, 2),
            'total_profit' => round($totalProfit, 2),
            'week' => $week
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | VENDOR → ORDERS (AJAX)
    |--------------------------------------------------------------------------
    */
    public function vendorOrders(Request $request, $vendorId)
    {
        $completedStatuses = $this->getCompletedStatuses();
        $dateExpr = $this->getDateExpression('restaurant_orders');

        $q = DB::table('restaurant_orders')
            ->where('vendorID', $vendorId)
            ->whereIn('status', $completedStatuses);

        // Prefer week_id (when user is browsing a settlement week)
        if ($request->filled('week_id')) {
            $week = DB::table('settlement_weeks')->find($request->input('week_id'));
            if ($week) {
                [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$week->week_start_date, (string)$week->week_end_date);
                if ($request->boolean('debug')) {
                    Log::info('Settlement vendor-orders (by week)', [
                        'vendorId' => $vendorId,
                        'week_id' => $request->input('week_id'),
                        'week_range' => [$week->week_start_date, $week->week_end_date],
                        'normalized' => [$startDateTime, $endDateTime],
                    ]);
                }
                $q->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);
            }
        } elseif ($request->start_date && $request->end_date) {
            [$startDateTime, $endDateTime] = $this->normalizeDateRange(
                (string)$request->start_date,
                (string)$request->end_date
            );
            if ($request->boolean('debug')) {
                Log::info('Settlement vendor-orders (by date)', [
                    'vendorId' => $vendorId,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'normalized' => [$startDateTime, $endDateTime],
                ]);
            }
            $q->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);
        }

        $orders = $q->get();

        // Fetch vendor products to get merchant_price for each product
        $vendorProducts = DB::table('vendor_products')
            ->where('vendorID', $vendorId)
            ->select('id', 'merchant_price', 'price')
            ->get()
            ->keyBy('id');

        // Build promotions cache for this vendor
        $productIds = $vendorProducts->pluck('id')->toArray();
        $startDate = $request->filled('week_id')
            ? DB::table('settlement_weeks')->find($request->input('week_id'))->week_start_date ?? now()
            : ($request->start_date ?? now());
        $endDate = $request->filled('week_id')
            ? DB::table('settlement_weeks')->find($request->input('week_id'))->week_end_date ?? now()
            : ($request->end_date ?? now());

        [$startDateTime, $endDateTime] = $this->normalizeDateRange(
            is_string($startDate) ? $startDate : $startDate->toDateString(),
            is_string($endDate) ? $endDate : $endDate->toDateString()
        );

        $promotionsCache = $this->buildPromotionsCache([$vendorId], $productIds, $startDateTime, $endDateTime);

        // Enrich each order's products with merchant_price and promotion data
        // GOLDEN RULE: restaurant_orders.promotion = 1 is the source of truth
        $orders = $orders->map(function ($order) use ($vendorProducts, $vendorId, $promotionsCache) {
            $products = json_decode($order->products, true) ?? [];
            $orderDate = $order->createdAt ?? now();

            // FINAL RULE: Check restaurant_orders.promotion first (order is source of truth)
            $orderHasPromotion = (int)($order->promotion ?? 0) === 1;

            if (is_array($products)) {
                $products = array_map(function ($item) use ($vendorProducts, $vendorId, $orderDate, $promotionsCache, $orderHasPromotion) {
                    $productId = $item['id'] ?? null;
                    if ($productId && isset($vendorProducts[$productId])) {
                        // Add merchant_price from vendor_products table
                        $merchantPrice = $vendorProducts[$productId]->merchant_price ?? 0;
                        $item['merchant_price'] = $merchantPrice;

                        // FINAL RULE: Use promo price ONLY when BOTH are true:
                        // promotions.promo = 1 AND restaurant_orders.promotion = 1
                        // Use the price from order JSON (frozen at order time)
                        // Otherwise, always use merchant_price
                        $cacheKey = $vendorId . '_' . $productId;
                        $promotion = $promotionsCache[$cacheKey] ?? null;
                        $restaurantAcceptsPromo = $promotion && (int)($promotion->promo ?? 0) === 1;

                        if ($orderHasPromotion && $restaurantAcceptsPromo) {
                            // BOTH conditions true: promotions.promo = 1 AND order.promotion = 1
                            // Use the price from order JSON (frozen at order time)
                            // This is the promo price that was stored when the order was placed
                            $promoPrice = (float)($item['price'] ?? $merchantPrice);
                            $item['promotion_price'] = $promoPrice;
                            $item['has_promotion'] = true;
                            $item['promo_accepted'] = true;
                        } else {
                            // In all other cases, use merchant_price
                            $item['promotion_price'] = null;
                            $item['has_promotion'] = false;
                            $item['promo_accepted'] = false;
                        }
                    } else {
                        $item['merchant_price'] = 0;
                        $item['promotion_price'] = null;
                        $item['has_promotion'] = false;
                    }
                    return $item;
                }, $products);

                $order->products = json_encode($products);
            }

            return $order;
        });

        return response()->json($orders);
    }

    /*
    |--------------------------------------------------------------------------
    | SAVE PAYMENT INFO
    |--------------------------------------------------------------------------
    */
    public function saveVendorSettlement(Request $request, $vendorId)
    {
        try {
            $request->validate([
                'txn_id' => 'required|string',
                'status' => 'required|in:Pending,Settled',
                'comments' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'vendor_name' => 'required|string',
                'jippy_percentage' => 'required|numeric',
                'total_orders' => 'required|integer',
                'total_merchant_price' => 'required|numeric',
                'total_customer_paid' => 'nullable|numeric',
                'settlement_amount' => 'required|numeric',
                'total_jippy_commission' => 'required|numeric',
            ]);

            // Prevent saving with Pending status - only Settled status can be saved
            if (strtolower($request->status) === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot save with Pending status. Please select "Settled" status to save the settlement.'
                ], 400);
            }

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Get or create settlement_week_id
            $settlementWeek = DB::table('settlement_weeks')
                ->where('week_start_date', $startDate->toDateString())
                ->where('week_end_date', $endDate->toDateString())
                ->first();

            $settlementWeekId = null;
            if ($settlementWeek) {
                $settlementWeekId = $settlementWeek->id;
            } else {
                // Create settlement week if it doesn't exist
                $settlementDate = $endDate->copy()->next(Carbon::FRIDAY);
                $weekCode = $startDate->format('Y') . '-W' . $startDate->weekOfYear . '-V';

                $settlementWeekId = DB::table('settlement_weeks')->insertGetId([
                    'week_start_date' => $startDate->toDateString(),
                    'week_end_date' => $endDate->toDateString(),
                    'week_code' => $weekCode,
                    'settlement_date' => $settlementDate->toDateString(),
                    'status' => 'open',
                    'total_restaurants' => 0,
                    'total_orders' => 0,
                    'total_settlement_amount' => 0,
                    'total_jippy_profit' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Get vendor subscription plan info
            $vendor = DB::table('vendors')
                ->leftJoin('subscription_plans as sp', 'sp.id', '=', 'vendors.subscriptionPlanId')
                ->where('vendors.id', $vendorId)
                ->select('vendors.subscriptionPlanId', 'sp.name as plan_name', 'sp.place as commission')
                ->first();

            $subscriptionPlanId = $vendor->subscriptionPlanId ?? null;
            $subscriptionPlanName = $vendor->plan_name ?? null;

            // Map status to payment_status enum
            $paymentStatus = strtolower($request->status);
            if (!in_array($paymentStatus, ['pending', 'processing', 'settled', 'failed', 'on_hold'])) {
                $paymentStatus = 'pending';
            }

            // Prepare data for vendor_settlements
            $settlementData = [
                'settlement_week_id' => $settlementWeekId,
                'vendor_id' => $vendorId,
                'vendor_name' => $request->vendor_name,
                'subscription_plan_id' => $subscriptionPlanId,
                'subscription_plan_name' => $subscriptionPlanName,
                'jippy_percentage' => (float)$request->jippy_percentage,
                'total_orders' => (int)$request->total_orders,
                'total_merchant_price' => (float)$request->total_merchant_price,
                // 'total_customer_paid' => (float)($request->total_customer_paid ?? 0),
                'total_jippy_commission' => (float)$request->total_jippy_commission,
                'settlement_amount' => (float)$request->settlement_amount,
                'transaction_id' => $request->txn_id ?? null,
                'payment_status' => $paymentStatus,
                'payment_comments' => $request->comments ?? null,
            ];

            // Set payment_date and verified_by if status is Settled
            if ($paymentStatus === 'settled') {
                $settlementData['payment_date'] = now()->toDateString();
                if (Auth::check()) {
                    $settlementData['verified_by'] = Auth::user()->name ?? Auth::user()->email ?? 'System';
                    $settlementData['verified_at'] = now();
                }
            }

            // Update or create vendor settlement
            VendorSettlement::updateOrCreate(
                [
                    'settlement_week_id' => $settlementWeekId,
                    'vendor_id' => $vendorId,
                ],
                $settlementData
            );

            return response()->json([
                'success' => true,
                'message' => 'Vendor settlement saved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving vendor settlement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving vendor settlement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportSettlement(Request $request)
    {
        $startDate = $request->start_date;
        $endDate   = $request->end_date;

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Start date and end date are required'], 400);
        }

        // Normalize dates
        [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$startDate, (string)$endDate);

        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        // Get vendors with orders in date range - join with subscription_plans
        $vendors = DB::table('restaurant_orders as ro')
            ->join('vendors as v', 'v.id', '=', 'ro.vendorID')
            ->leftJoin('subscription_plans as sp', 'sp.id', '=', 'v.subscriptionPlanId')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->groupBy('v.id', 'v.title', 'v.subscriptionPlanId', 'v.gst', 'sp.name', 'sp.place')
            ->select('v.id', 'v.title', 'v.subscriptionPlanId', 'v.gst', 'sp.name as plan_name', 'sp.place as commission')
            ->get();

        // Get all orders in date range - explicitly select promotion column
        $ordersDateExpr = $this->getDateExpression('o');
        $orders = DB::table('restaurant_orders as o')
            ->whereIn('o.status', $completedStatuses)
            ->whereBetween(DB::raw("($ordersDateExpr)"), [$startDateTime, $endDateTime])
            ->select('o.*', DB::raw('COALESCE(o.promotion, 0) as order_promotion'))
            ->get()
            ->groupBy('vendorID');

        // Get vendor products
        $products = DB::table('vendor_products')
            ->select('id', 'vendorID', 'price', 'merchant_price', 'name')
            ->get()
            ->groupBy('vendorID');

        // Build promotions cache - extract product IDs from order JSON products
        $allVendorIds = $vendors->pluck('id')->toArray();
        $allProductIdsFromOrders = [];
        foreach ($orders as $vendorOrderList) {
            foreach ($vendorOrderList as $order) {
                $items = json_decode($order->products, true) ?? [];
                foreach ($items as $item) {
                    if (!empty($item['id'])) {
                        $allProductIdsFromOrders[] = $item['id'];
                    }
                }
            }
        }
        $allProductIds = array_unique(array_merge($products->pluck('id')->toArray(), $allProductIdsFromOrders));
        $promotionsCache = $this->buildPromotionsCache($allVendorIds, $allProductIds, $startDateTime, $endDateTime);

        // Get settlement week for vendor settlements
        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);
        $settlementWeek = DB::table('settlement_weeks')
            ->where('week_start_date', $startDateCarbon->toDateString())
            ->where('week_end_date', $endDateCarbon->toDateString())
            ->where('settlement_type', 'vendor')
            ->first();
        $settlementWeekId = $settlementWeek->id ?? null;

        // Get saved vendor settlements
        $vendorSettlements = [];
        if ($settlementWeekId) {
            $savedSettlementsRaw = DB::table('vendor_settlements')
                ->where('settlement_week_id', $settlementWeekId)
                ->select('vendor_id', 'transaction_id', 'payment_status', 'payment_comments')
                ->get();
            foreach ($savedSettlementsRaw as $vs) {
                $vendorSettlements[$vs->vendor_id] = [
                    'transaction_id' => $vs->transaction_id ?? '',
                    'payment_status' => ucfirst($vs->payment_status ?? 'Pending'),
                    'payment_comments' => $vs->payment_comments ?? '',
                ];
            }
        }

        // Build export data
        $exportData = [];
        foreach ($vendors as $vendor) {
            $vendorOrders = $orders[$vendor->id] ?? collect();
            $vendorProducts = $products[$vendor->id] ?? collect();

            // Get settlement data for this vendor
            $settlementData = $vendorSettlements[$vendor->id] ?? [
                'transaction_id' => '',
                'payment_status' => 'Pending',
                'payment_comments' => '',
            ];

            // Check GST status for this vendor
            $isGstOn = $this->isGstEnabled($vendor);

            // Commission calculation
            $hasPlan = !empty($vendor->subscriptionPlanId) && !empty($vendor->plan_name);
            if ($hasPlan) {
                $commission = (float) ($vendor->commission ?? 0);
                $planName = $vendor->plan_name ?? 'Subscription';
            } else {
                $commission = 0; // No commission deduction if no plan
                $planName = 'Commission Plan';
            }

            foreach ($vendorOrders as $order) {
                $items = json_decode($order->products, true) ?? [];
                $orderDate = $order->createdAt ?? now();

                // Get order promotion flag - check multiple possible column names
                $orderPromotionFlag = $order->order_promotion ?? $order->promotion ?? 0;

                // Use helper method to calculate settlement with correct promotion/commission logic
                // GOLDEN RULE: Commission NEVER applies to promotional items
                // COMMISSION PLAN RULE: If plan_name = "Commission Plan" → DO NOT deduct commission
                // FINAL RULE: restaurant_orders.promotion = 1 is the source of truth
                $orderCalc = $this->calculateOrderSettlement(
                    $items,
                    $vendor,
                    $vendorProducts,
                    $orderDate,
                    $promotionsCache,
                    $hasPlan,
                    $commission,
                    $isGstOn,
                    $orderPromotionFlag, // Pass order-level promotion flag (source of truth)
                    $planName // Pass plan name to check if it's "Commission Plan"
                );

                // Build export row
                $exportData[] = (object) [
                    'id' => $order->id,
                    'vendor_name' => $vendor->title,
                    'products' => $order->products,
                    'merchant_price' => $orderCalc['merchant_total'],
                    'promotion_price' => $orderCalc['promotion_total'],
                    // 'customer_paid' => $order->toPayAmount ?? 0,
                    'commission' => $commission,
                    'gst' => (int)($vendor->gst ?? 0),
                    'settlement_amount' => $orderCalc['settlement'],
                    'createdAt' => $order->createdAt ?? null,
                    'transaction_id' => $settlementData['transaction_id'],
                    'payment_status' => $settlementData['payment_status'],
                    'payment_comments' => $settlementData['payment_comments'],
                ];
            }
        }

        return Excel::download(
            new MerchantSettlementExport(collect($exportData)),
            'merchant_settlement_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }

    public function saveSettlementWeek(Request $request)
    {

        $request->validate([
            'week_start' => 'required|date',
            'week_end'   => 'required|date',
            'status'     => 'required|in:open,under_review,approved,processing,settled,failed,on_hold',
            'restaurants'=> 'required|integer',
            'orders'     => 'required|integer',
            'to_settle'  => 'required|numeric',
            'profit'     => 'required|numeric',
        ]);

        $weekStart = Carbon::parse($request->week_start);
        $weekEnd   = Carbon::parse($request->week_end);
        $status = strtolower(trim($request->status));

        // Check if any vendor has pending status when trying to settle the week
        if ($status === 'settled') {
            $pendingVendors = DB::table('vendor_settlements')
                ->join('settlement_weeks', 'settlement_weeks.id', '=', 'vendor_settlements.settlement_week_id')
                ->where('settlement_weeks.week_start_date', $weekStart->toDateString())
                ->where('settlement_weeks.week_end_date', $weekEnd->toDateString())
                ->where('vendor_settlements.payment_status', 'pending')
                ->select('vendor_settlements.vendor_name')
                ->get();

            if ($pendingVendors->count() > 0) {
                $vendorNames = $pendingVendors->pluck('vendor_name')->toArray();
                $vendorList = implode(', ', array_slice($vendorNames, 0, 5));
                $moreCount = count($vendorNames) > 5 ? ' and ' . (count($vendorNames) - 5) . ' more' : '';

                return response()->json([
                    'success' => false,
                    'message' => "Cannot settle week. Some restaurants have pending status: {$vendorList}{$moreCount}. Please ensure all restaurants are settled before marking the week as settled."
                ], 400);
            }
        }

        // 👉 Settlement date = next Friday after week end
        $settlementDate = $weekEnd->copy()->next(Carbon::FRIDAY);

        // 👉 Week code like 2025-W49
        $weekCode = $weekStart->format('Y') . '-W' . $weekStart->weekOfYear . '-V';

        // Update or insert settlement week and get the ID
        $settlementWeek = DB::table('settlement_weeks')
            ->where('week_start_date', $weekStart->toDateString())
            ->where('week_end_date', $weekEnd->toDateString())
            ->first();

        if ($settlementWeek) {
            // Update existing week
            DB::table('settlement_weeks')
                ->where('id', $settlementWeek->id)
                ->update([
                    'week_code' => $weekCode,
                    'settlement_date' => $settlementDate->toDateString(),
                    'status' => $status,
                    'total_restaurants' => $request->restaurants,
                    'total_orders' => $request->orders,
                    'total_settlement_amount' => $request->to_settle,
                    'total_jippy_profit' => $request->profit,
                    'updated_at' => now(),
                ]);
            $settlementWeekId = $settlementWeek->id;
        } else {
            // Insert new week
            $settlementWeekId = DB::table('settlement_weeks')->insertGetId([
                'week_start_date' => $weekStart->toDateString(),
                'week_end_date' => $weekEnd->toDateString(),
                'week_code' => $weekCode,
                'settlement_date' => $settlementDate->toDateString(),
                'status' => $status,
                'total_restaurants' => $request->restaurants,
                'total_orders' => $request->orders,
                'total_settlement_amount' => $request->to_settle,
                'total_jippy_profit' => $request->profit,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // If status is "settled", create/update all vendor_settlements for this week
        if ($status === 'settled') {
            $this->createOrUpdateVendorSettlementsForWeek($settlementWeekId, $weekStart, $weekEnd);
        }

        return response()->json([
            'success' => true,
            'week_code' => $weekCode,
            'settlement_date' => $settlementDate->toDateString(),
            'week_id' => $settlementWeekId
        ]);
    }

    /**
     * Create or update vendor_settlements for all vendors in a week
     * Used when week status is set to "settled"
     */
    private function createOrUpdateVendorSettlementsForWeek($settlementWeekId, $weekStart, $weekEnd)
    {
        // Normalize dates
        [$startDateTime, $endDateTime] = $this->normalizeDateRange(
            $weekStart->toDateString(),
            $weekEnd->toDateString()
        );

        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        // Get vendors with orders in date range
        $vendors = DB::table('restaurant_orders as ro')
            ->join('vendors as v', 'v.id', '=', 'ro.vendorID')
            ->leftJoin('subscription_plans as sp', 'sp.id', '=', 'v.subscriptionPlanId')
            ->leftJoin('zone as z', 'z.id', '=', 'v.zoneId')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->groupBy('v.id', 'v.title', 'v.subscriptionPlanId', 'v.gst', 'v.zoneId', 'sp.name', 'sp.place', 'z.name')
            ->select('v.id', 'v.title', 'v.subscriptionPlanId', 'v.gst', 'v.zoneId', 'sp.name as plan_name', 'sp.place as commission', 'z.name as zone')
            ->get();

        // Get all orders in date range
        $ordersDateExpr = $this->getDateExpression('o');
        $orders = DB::table('restaurant_orders as o')
            ->whereIn('o.status', $completedStatuses)
            ->whereBetween(DB::raw("($ordersDateExpr)"), [$startDateTime, $endDateTime])
            ->select('o.*')
            ->get()
            ->groupBy('vendorID');

        // Get vendor products
        $products = DB::table('vendor_products')
            ->select('id', 'vendorID', 'price', 'merchant_price', 'name')
            ->get()
            ->groupBy('vendorID');

        // Build promotions cache
        $allVendorIds = $vendors->pluck('id')->toArray();
        $allProductIds = $products->pluck('id')->unique()->toArray();
        $promotionsCache = $this->buildPromotionsCache($allVendorIds, $allProductIds, $startDateTime, $endDateTime);

        $verifiedBy = Auth::check() ? (Auth::user()->name ?? Auth::user()->email ?? 'System') : 'System';
        $verifiedAt = now();

        foreach ($vendors as $vendor) {
            $vendorOrders = $orders[$vendor->id] ?? collect();
            $vendorProducts = $products[$vendor->id] ?? collect();

            $merchantTotal = 0;
            $settlementTotal = 0; // Sum of settlement amounts from all orders
            $customerPaid = 0;

            // Check GST status for this vendor
            $isGstOn = $this->isGstEnabled($vendor);

            // Commission calculation - fetch directly from subscription_plans table
            $hasPlan = !empty($vendor->subscriptionPlanId) && !empty($vendor->plan_name);
            if ($hasPlan) {
                $commission = (float) ($vendor->commission ?? 0);
                $planName = $vendor->plan_name ?? 'Subscription';
            } else {
                $commission = 0; // No commission deduction if no plan
                $planName = 'Commission Plan';
            }

            // COMMISSION PLAN RULE: If plan_name = "Commission Plan" (case-insensitive) → DO NOT deduct commission
            // Handles all case variations: "Commission Plan", "COMMISSION PLAN", "commission plan", etc.
            $isCommissionPlan = !empty($planName) && strtolower(trim((string)$planName)) === 'commission plan';

            foreach ($vendorOrders as $order) {
                $orderMerchantTotal = 0;
                $items = json_decode($order->products, true) ?? [];
                $orderDate = $order->createdAt ?? now();

                foreach ($items as $item) {
                    $product = $vendorProducts->firstWhere('id', $item['id'] ?? null);
                    if (!$product) continue;

                    $qty = $item['quantity'] ?? 1;
                    $merchantPrice = $product->merchant_price ?? 0;

                    // STEP 1: Promotion check (FIRST) - use special_price if promo = 1, else merchant_price
                    $price = $this->getProductPrice($product->id, $vendor->id, $orderDate, $merchantPrice, $promotionsCache);

                    $orderMerchantTotal += ($price * $qty);
                }

                // Calculate settlement amount per order
                // COMMISSION PLAN RULE: If plan_name = "Commission Plan" → DO NOT deduct commission
                $orderSettlementAmount = $orderMerchantTotal;

                // If plan exists, apply commission deduction (skip if Commission Plan)
                if ($hasPlan && $commission > 0 && !$isCommissionPlan) {
                    $orderSettlementAmount -= $orderMerchantTotal * ($commission / 100);
                }

                // STEP 2: GST check (SECOND) - deduct 5% if gst = 1, else 0%
                if ($isGstOn) {
                    $orderSettlementAmount -= $orderMerchantTotal * 0.05;
                }

                // STEP 3: Round final value
                $orderSettlementAmount = round($orderSettlementAmount);

                $merchantTotal += $orderMerchantTotal;
                $settlementTotal += $orderSettlementAmount;
                $customerPaid += $order->toPayAmount;
            }

            // Commission for profit calculation (use 30% default if no plan for profit calculation only)
            $commissionForProfit = $hasPlan ? $commission : 30;

            // Calculate profit (commission on merchant total)
            $jippyProfit = round($merchantTotal * ($commissionForProfit / 100), 2);
            // Settlement amount is the sum of settlement amounts from all orders (with commission and GST deductions if applicable)
            $settlementAmount = round($settlementTotal, 2);

            // Create or update vendor_settlement
            VendorSettlement::updateOrCreate(
                [
                    'settlement_week_id' => $settlementWeekId,
                    'vendor_id' => $vendor->id,
                ],
                [
                    'vendor_name' => $vendor->title,
                    'subscription_plan_id' => $vendor->subscriptionPlanId,
                    'subscription_plan_name' => $planName,
                    'jippy_percentage' => $commission,
                    'total_orders' => $vendorOrders->count(),
                    'total_merchant_price' => $merchantTotal,
                    'total_customer_paid' => $customerPaid,
                    'total_jippy_commission' => $jippyProfit,
                    'settlement_amount' => $settlementAmount,
                    'payment_status' => 'settled',
                    'payment_date' => now()->toDateString(),
                    'verified_by' => $verifiedBy,
                    'verified_at' => $verifiedAt,
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function driverIndex()
    {
        $weeks = DB::table('settlement_weeks')
            ->where('settlement_type', 'driver')
            ->orderBy('week_start_date', 'desc')
            ->get();

        return view('reports.driverSettlement', compact('weeks'));
    }

    /**
     * Get drivers by week ID (similar to weekVendors for merchants)
     */
    public function weekDrivers($weekId)
    {
        $week = DB::table('settlement_weeks')
            ->where('id', $weekId)
            ->where('settlement_type', 'driver')
            ->first();

        if (!$week) {
            return response()->json([]);
        }

        [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$week->week_start_date, (string)$week->week_end_date);

        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        // Get drivers with orders in date range
        $drivers = DB::table('restaurant_orders as ro')
            ->join('users as u', 'u.firebase_id', '=', 'ro.driverID')
            ->where('u.role', 'driver')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->groupBy('u.firebase_id', 'u.firstName', 'u.lastName', 'u.phoneNumber')
            ->select([
                'u.firebase_id as driver_id',
                DB::raw("CONCAT(u.firstName,' ',u.lastName) as driver_name"),
                'u.phoneNumber as phone',
                DB::raw('COUNT(ro.id) as orders_count'),
                DB::raw('SUM(IFNULL(ro.deliveryCharge,0)) as delivery_earning'),
                DB::raw('SUM(IFNULL(ro.tip_amount,0)) as tip_earning'),
                DB::raw('SUM(IFNULL(ro.deliveryCharge,0) + IFNULL(ro.tip_amount,0)) as total_earning'),
            ])
            ->get();

        // Get all orders for expandable view
        $orders = DB::table('restaurant_orders as ro')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->select([
                'ro.id',
                'ro.driverID',
                'ro.deliveryCharge',
                'ro.tip_amount',
                'ro.createdAt',
            ])
            ->orderBy('ro.createdAt')
            ->get()
            ->groupBy('driverID');

        $final = [];

        foreach ($drivers as $driver) {
            $driverOrders = $orders[$driver->driver_id] ?? collect();

            // Get saved driver settlement data if exists
            $savedSettlement = DB::table('driver_settlements')
                ->where('settlement_week_id', $weekId)
                ->where('driver_id', $driver->driver_id)
                ->select('transaction_id', 'payment_status', 'payment_comments', 'payment_date', 'incentives', 'deductions')
                ->first();

            $deliveryEarning = round((float)$driver->delivery_earning, 2);
            $tipEarning = round((float)$driver->tip_earning, 2);
            $totalEarning = round((float)$driver->total_earning, 2);

            // Apply incentives and deductions if saved settlement exists
            $settlementAmount = $totalEarning;
            if ($savedSettlement) {
                $settlementAmount += (float)($savedSettlement->incentives ?? 0);
                $settlementAmount -= (float)($savedSettlement->deductions ?? 0);
                $settlementAmount = round($settlementAmount, 2);
            }

            $final[] = [
                'driver_id'        => $driver->driver_id,
                'driver_name'      => $driver->driver_name,
                'phone'            => $driver->phone,
                'orders_count'     => (int) $driver->orders_count,
                'delivery_earning' => $deliveryEarning,
                'tip_earning'      => $tipEarning,
                'total_earning'    => $totalEarning,
                'settlement_amount' => $settlementAmount,
                'saved_settlement' => $savedSettlement ? [
                    'transaction_id' => $savedSettlement->transaction_id,
                    'payment_status' => ucfirst($savedSettlement->payment_status ?? 'pending'),
                    'payment_comments' => $savedSettlement->payment_comments,
                    'payment_date' => $savedSettlement->payment_date,
                    'incentives' => (float)($savedSettlement->incentives ?? 0),
                    'deductions' => (float)($savedSettlement->deductions ?? 0),
                ] : null,
                'orders' => $driverOrders->map(function ($o) {
                    return [
                        'order_id'       => $o->id,
                        'deliveryCharge' => (float) ($o->deliveryCharge ?? 0),
                        'tip_amount'     => (float) ($o->tip_amount ?? 0),
                        'date'           => $o->createdAt,
                    ];
                })->values()
            ];
        }

        return response()->json($final);
    }

    /**
     * Get driver week summary stats
     */
    public function weekDriverSummary($weekId)
    {
        $week = DB::table('settlement_weeks')
            ->where('id', $weekId)
            ->where('settlement_type', 'driver')
            ->first();

        if (!$week) {
            return response()->json(['error' => 'Week not found'], 404);
        }

        [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$week->week_start_date, (string)$week->week_end_date);

        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        // Get drivers count
        $driversCount = DB::table('restaurant_orders as ro')
            ->join('users as u', 'u.firebase_id', '=', 'ro.driverID')
            ->where('u.role', 'driver')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->distinct('u.firebase_id')
            ->count('u.firebase_id');

        // Get orders count
        $ordersCount = DB::table('restaurant_orders as ro')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->count();

        // Calculate totals
        $totals = DB::table('restaurant_orders as ro')
            ->join('users as u', 'u.firebase_id', '=', 'ro.driverID')
            ->where('u.role', 'driver')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->select([
                DB::raw('SUM(IFNULL(ro.deliveryCharge,0)) as total_delivery_earnings'),
                DB::raw('SUM(IFNULL(ro.tip_amount,0)) as total_tips'),
                DB::raw('SUM(IFNULL(ro.deliveryCharge,0) + IFNULL(ro.tip_amount,0)) as total_settlement'),
            ])
            ->first();

        // Apply incentives and deductions from saved settlements
        $savedSettlements = DB::table('driver_settlements')
            ->where('settlement_week_id', $weekId)
            ->select(DB::raw('SUM(IFNULL(incentives,0)) as total_incentives'), DB::raw('SUM(IFNULL(deductions,0)) as total_deductions'))
            ->first();

        $totalSettlement = round((float)($totals->total_settlement ?? 0), 2);
        $totalIncentives = round((float)($savedSettlements->total_incentives ?? 0), 2);
        $totalDeductions = round((float)($savedSettlements->total_deductions ?? 0), 2);
        $finalSettlement = $totalSettlement + $totalIncentives - $totalDeductions;

        return response()->json([
            'drivers' => $driversCount,
            'orders' => $ordersCount,
            'total_delivery_earnings' => round((float)($totals->total_delivery_earnings ?? 0), 2),
            'total_tips' => round((float)($totals->total_tips ?? 0), 2),
            'total_settlement' => round($finalSettlement, 2),
            'total_incentives' => $totalIncentives,
            'total_deductions' => $totalDeductions,
            'week' => $week
        ]);
    }

    /**
     * Get driver summary by date range
     */
    public function getDriverSummaryByDateRange(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Start date and end date are required'], 400);
        }

        [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$startDate, (string)$endDate);

        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        // Build base query with zone join
        $baseQuery = DB::table('restaurant_orders as ro')
            ->join('users as u', 'u.firebase_id', '=', 'ro.driverID')
            ->leftJoin('zone as z', 'z.id', '=', 'u.zoneId')
            ->where('u.role', 'driver')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);

        // Apply zone filter if provided
        if ($request->has('zone_id') && !empty($request->zone_id)) {
            $baseQuery->where('u.zoneId', $request->zone_id);
        }

        // Get drivers count
        $driversCount = (clone $baseQuery)
            ->distinct('u.firebase_id')
            ->count('u.firebase_id');

        // Get orders count (apply zone filter if set)
        $ordersQuery = DB::table('restaurant_orders as ro')
            ->join('users as u', 'u.firebase_id', '=', 'ro.driverID')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);

        if ($request->has('zone_id') && !empty($request->zone_id)) {
            $ordersQuery->where('u.zoneId', $request->zone_id);
        }

        $ordersCount = $ordersQuery->count();

        // Calculate totals
        $totals = (clone $baseQuery)
            ->select([
                DB::raw('SUM(IFNULL(ro.deliveryCharge,0)) as total_delivery_earnings'),
                DB::raw('SUM(IFNULL(ro.tip_amount,0)) as total_tips'),
                DB::raw('SUM(IFNULL(ro.deliveryCharge,0) + IFNULL(ro.tip_amount,0)) as total_settlement'),
            ])
            ->first();

        // Get week status if exists
        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);
        $week = DB::table('settlement_weeks')
            ->where('week_start_date', $startDateCarbon->toDateString())
            ->where('week_end_date', $endDateCarbon->toDateString())
            ->where('settlement_type', 'driver')
            ->first();

        return response()->json([
            'drivers' => $driversCount,
            'orders' => $ordersCount,
            'total_delivery_earnings' => round((float)($totals->total_delivery_earnings ?? 0), 2),
            'total_tips' => round((float)($totals->total_tips ?? 0), 2),
            'total_settlement' => round((float)($totals->total_settlement ?? 0), 2),
            'week_status' => $week ? $week->status : 'open',
        ]);
    }

    /**
     * Save driver settlement payment info
     */
    public function saveDriverSettlement(Request $request, $driverId)
    {
        try {
            $request->validate([
                'txn_id' => 'nullable|string',
                'status' => 'required|in:Pending,Processing,Settled,Failed,On Hold',
                'comments' => 'nullable|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'driver_name' => 'required|string',
                'total_deliveries' => 'required|integer',
                'delivery_earnings' => 'required|numeric',
                'tips_received' => 'required|numeric',
                'settlement_amount' => 'required|numeric',
                'incentives' => 'nullable|numeric',
                'deductions' => 'nullable|numeric',
            ]);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Get driver phone from database if not provided
            $driverPhone = $request->driver_phone ?? null;
            if (!$driverPhone) {
                $driver = DB::table('users')
                    ->where('firebase_id', $driverId)
                    ->where('role', 'driver')
                    ->select('phoneNumber')
                    ->first();
                $driverPhone = $driver->phoneNumber ?? null;
            }

            // Get or create settlement_week_id
            $settlementWeek = DB::table('settlement_weeks')
                ->where('week_start_date', $startDate->toDateString())
                ->where('week_end_date', $endDate->toDateString())
                ->where('settlement_type', 'driver')
                ->first();

            $settlementWeekId = null;
            if ($settlementWeek) {
                $settlementWeekId = $settlementWeek->id;
            } else {
                // Create settlement week if it doesn't exist
                // Settlement date = next Friday after week end (Sunday)
                $settlementDate = $endDate->copy()->next(Carbon::FRIDAY);
                $weekCode = $startDate->format('Y') . '-W' . $startDate->weekOfYear . '-D';

                $settlementWeekId = DB::table('settlement_weeks')->insertGetId([
                    'week_start_date' => $startDate->toDateString(),
                    'week_end_date' => $endDate->toDateString(),
                    'week_code' => $weekCode,
                    'settlement_date' => $settlementDate->toDateString(),
                    'settlement_type' => 'driver',
                    'status' => 'open',
                    'total_drivers' => 0,
                    'total_orders' => 0,
                    'total_driver_earnings' => 0,
                    'total_driver_tips' => 0,
                    'total_settlement_amount' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Map status to payment_status enum
            $paymentStatus = strtolower($request->status);
            if (!in_array($paymentStatus, ['pending', 'processing', 'settled', 'failed', 'on_hold'])) {
                $paymentStatus = 'pending';
            }

            // Prepare data for driver_settlements
            $settlementData = [
                'settlement_week_id' => $settlementWeekId,
                'driver_id' => $driverId,
                'driver_name' => $request->driver_name,
                'driver_phone' => $driverPhone,
                'total_deliveries' => (int)$request->total_deliveries,
                'delivery_earnings' => (float)$request->delivery_earnings,
                'tips_received' => (float)$request->tips_received,
                'incentives' => (float)($request->incentives ?? 0),
                'deductions' => (float)($request->deductions ?? 0),
                'settlement_amount' => (float)$request->settlement_amount,
                'transaction_id' => $request->txn_id ?? null,
                'payment_status' => $paymentStatus,
                'payment_comments' => $request->comments ?? null,
            ];

            // Set payment_date and verified_by if status is Settled
            if ($paymentStatus === 'settled') {
                $settlementData['payment_date'] = now()->toDateString();
            }

            // Update or create driver settlement
            DriverSettlement::updateOrCreate(
                [
                    'settlement_week_id' => $settlementWeekId,
                    'driver_id' => $driverId,
                ],
                $settlementData
            );

            return response()->json([
                'success' => true,
                'message' => 'Driver settlement saved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error saving driver settlement: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving driver settlement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get driver orders for a specific driver (for expandable view)
     */
    public function driverOrders(Request $request, $driverId)
    {
        $completedStatuses = $this->getCompletedStatuses();
        $dateExpr = $this->getDateExpression('restaurant_orders');

        $q = DB::table('restaurant_orders')
            ->where('driverID', $driverId)
            ->whereIn('status', $completedStatuses);

        // Prefer week_id (when user is browsing a settlement week)
        if ($request->filled('week_id')) {
            $week = DB::table('settlement_weeks')
                ->where('id', $request->input('week_id'))
                ->where('settlement_type', 'driver')
                ->first();
            if ($week) {
                [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$week->week_start_date, (string)$week->week_end_date);
                $q->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);
            }
        } elseif ($request->start_date && $request->end_date) {
            [$startDateTime, $endDateTime] = $this->normalizeDateRange(
                (string)$request->start_date,
                (string)$request->end_date
            );
            $q->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);
        }

        $orders = $q->select([
            'id',
            'deliveryCharge',
            'tip_amount',
            'createdAt',
        ])
            ->orderBy('createdAt')
            ->get();

        return response()->json($orders);
    }

    /**
     * Export driver settlement to Excel
     */
    public function exportDriverSettlement(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Start date and end date are required'], 400);
        }

        [$startDateTime, $endDateTime] = $this->normalizeDateRange((string)$startDate, (string)$endDate);
        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        // Get settlement week for status filtering
        $startDateCarbon = Carbon::parse($startDate);
        $endDateCarbon = Carbon::parse($endDate);
        $settlementWeek = DB::table('settlement_weeks')
            ->where('week_start_date', $startDateCarbon->toDateString())
            ->where('week_end_date', $endDateCarbon->toDateString())
            ->where('settlement_type', 'driver')
            ->first();
        $settlementWeekId = $settlementWeek->id ?? null;

        // Get drivers with orders in date range - include zone
        $driversQuery = DB::table('restaurant_orders as ro')
            ->join('users as u', 'u.firebase_id', '=', 'ro.driverID')
            ->leftJoin('zone as z', 'z.id', '=', 'u.zoneId')
            ->where('u.role', 'driver')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);

        // Apply zone filter if provided
        if ($request->has('zone_id') && !empty($request->zone_id)) {
            $driversQuery->where('u.zoneId', $request->zone_id);
        }

        $drivers = $driversQuery
            ->groupBy('u.firebase_id', 'u.firstName', 'u.lastName', 'u.phoneNumber', 'u.zoneId', 'z.name')
            ->select([
                'u.firebase_id as driver_id',
                DB::raw("CONCAT(u.firstName,' ',u.lastName) as driver_name"),
                'u.phoneNumber as phone',
                'u.zoneId',
                'z.name as zone',
            ])
            ->get();

        // Get all orders in date range
        $ordersQuery = DB::table('restaurant_orders as ro')
            ->join('users as u', 'u.firebase_id', '=', 'ro.driverID')
            ->leftJoin('zone as z', 'z.id', '=', 'u.zoneId')
            ->where('u.role', 'driver')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);

        // Apply zone filter if provided
        if ($request->has('zone_id') && !empty($request->zone_id)) {
            $ordersQuery->where('u.zoneId', $request->zone_id);
        }

        $orders = $ordersQuery
            ->select([
                'ro.id as order_id',
                'ro.driverID',
                'ro.deliveryCharge',
                'ro.tip_amount',
                'ro.createdAt',
                'u.firebase_id as driver_id',
                DB::raw("CONCAT(u.firstName,' ',u.lastName) as driver_name"),
                'u.phoneNumber as phone',
                'z.name as zone',
            ])
            ->orderBy('ro.createdAt')
            ->get();

        // Get saved settlements for status filtering and export data
        $driverSettlements = [];
        if ($settlementWeekId) {
            $savedSettlementsRaw = DB::table('driver_settlements')
                ->where('settlement_week_id', $settlementWeekId)
                ->select('driver_id', 'payment_status', 'transaction_id', 'payment_comments', 'incentives', 'deductions')
                ->get();
            foreach ($savedSettlementsRaw as $ss) {
                $driverSettlements[$ss->driver_id] = [
                    'payment_status' => strtolower($ss->payment_status ?? 'pending'),
                    'transaction_id' => $ss->transaction_id ?? '',
                    'payment_comments' => $ss->payment_comments ?? '',
                    'incentives' => (float)($ss->incentives ?? 0),
                    'deductions' => (float)($ss->deductions ?? 0),
                ];
            }
        }

        // Build export data - order level (like merchant export)
        $exportData = [];
        foreach ($orders as $order) {
            $driverId = $order->driver_id;
            $settlementData = $driverSettlements[$driverId] ?? [
                'payment_status' => 'pending',
                'transaction_id' => '',
                'payment_comments' => '',
                'incentives' => 0,
                'deductions' => 0,
            ];
            $paymentStatus = $settlementData['payment_status'];

            // Apply status filter if provided
            if ($request->has('status') && !empty($request->status)) {
                $statusFilter = strtolower($request->status);
                if ($statusFilter === 'pending' && $paymentStatus !== 'pending') {
                    continue;
                } elseif ($statusFilter === 'settled' && $paymentStatus !== 'settled') {
                    continue;
                }
            }

            $deliveryCharge = (float)($order->deliveryCharge ?? 0);
            $tipAmount = (float)($order->tip_amount ?? 0);
            $totalEarning = $deliveryCharge + $tipAmount;

            $exportData[] = (object)[
                'order_id' => $order->order_id,
                'driver_id' => $driverId,
                'driver_name' => $order->driver_name,
                'phone' => $order->phone,
                'zone' => $order->zone ?? 'N/A',
                'delivery_charge' => $deliveryCharge,
                'tip_amount' => $tipAmount,
                'total_earning' => $totalEarning,
                'createdAt' => $order->createdAt,
                'transaction_id' => $settlementData['transaction_id'],
                'payment_status' => ucfirst($paymentStatus),
                'incentives' => $settlementData['incentives'],
                'deductions' => $settlementData['deductions'],
                'payment_comments' => $settlementData['payment_comments'],
            ];
        }

        return Excel::download(
            new \App\Exports\DriverSettlementExport(collect($exportData)),
            'driver_settlement_' . $startDate . '_to_' . $endDate . '.xlsx'
        );
    }

    /**
     * Save driver settlement week (similar to saveSettlementWeek but for drivers)
     */
    public function saveDriverSettlementWeek(Request $request)
    {
        $request->validate([
            'week_start' => 'required|date',
            'week_end'   => 'required|date',
            'status'     => 'required|in:open,under_review,approved,processing,settled,failed,on_hold',
            'drivers'    => 'required|integer',
            'orders'     => 'required|integer',
            'to_settle'  => 'required|numeric',
            'delivery_earnings' => 'nullable|numeric',
            'tips' => 'nullable|numeric',
        ]);

        $weekStart = Carbon::parse($request->week_start);
        $weekEnd   = Carbon::parse($request->week_end);
        $status = strtolower(trim($request->status));

        // Settlement date = next Friday after week end (Sunday)
        $settlementDate = $weekEnd->copy()->next(Carbon::FRIDAY);

        // Week code like 2025-W49
        $weekCode = $weekStart->format('Y') . '-W' . $weekStart->weekOfYear . '-D';

        // Update or insert settlement week and get the ID
        $settlementWeek = DB::table('settlement_weeks')
            ->where('week_start_date', $weekStart->toDateString())
            ->where('week_end_date', $weekEnd->toDateString())
            ->where('settlement_type', 'driver')
            ->first();

        $deliveryEarnings = (float)($request->delivery_earnings ?? 0);
        $driverTips = (float)($request->tips ?? 0);

        if ($settlementWeek) {
            // Update existing week
            DB::table('settlement_weeks')
                ->where('id', $settlementWeek->id)
                ->update([
                    'week_code' => $weekCode,
                    'settlement_date' => $settlementDate->toDateString(),
                    'status' => $status,
                    'total_drivers' => $request->drivers,
                    'total_orders' => $request->orders,
                    'total_driver_earnings' => $deliveryEarnings,
                    'total_driver_tips' => $driverTips,
                    'total_settlement_amount' => $request->to_settle,
                    'updated_at' => now(),
                ]);
            $settlementWeekId = $settlementWeek->id;
        } else {
            // Insert new week
            $settlementWeekId = DB::table('settlement_weeks')->insertGetId([
                'week_start_date' => $weekStart->toDateString(),
                'week_end_date' => $weekEnd->toDateString(),
                'week_code' => $weekCode,
                'settlement_date' => $settlementDate->toDateString(),
                'settlement_type' => 'driver',
                'status' => $status,
                'total_drivers' => $request->drivers,
                'total_orders' => $request->orders,
                'total_driver_earnings' => $deliveryEarnings,
                'total_driver_tips' => $driverTips,
                'total_settlement_amount' => $request->to_settle,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // If status is "settled", create/update all driver_settlements for this week
        // Also update existing driver_settlements to "settled" status
        if ($status === 'settled') {
            // Update all existing driver_settlements for this week to "settled"
            DB::table('driver_settlements')
                ->where('settlement_week_id', $settlementWeekId)
                ->update([
                    'payment_status' => 'settled',
                    'payment_date' => now()->toDateString(),
                    'updated_at' => now(),
                ]);

            // Create/update driver_settlements for all drivers in this week
            $this->createOrUpdateDriverSettlementsForWeek($settlementWeekId, $weekStart, $weekEnd);
        }

        return response()->json([
            'success' => true,
            'week_code' => $weekCode,
            'settlement_date' => $settlementDate->toDateString(),
            'week_id' => $settlementWeekId
        ]);
    }

    /**
     * Create or update driver_settlements for all drivers in a week
     * Used when week status is set to "settled"
     */
    private function createOrUpdateDriverSettlementsForWeek($settlementWeekId, $weekStart, $weekEnd)
    {
        [$startDateTime, $endDateTime] = $this->normalizeDateRange(
            $weekStart->toDateString(),
            $weekEnd->toDateString()
        );

        $dateExpr = $this->getDateExpression('ro');
        $completedStatuses = $this->getCompletedStatuses();

        // Get drivers with orders in date range
        $drivers = DB::table('restaurant_orders as ro')
            ->join('users as u', 'u.firebase_id', '=', 'ro.driverID')
            ->where('u.role', 'driver')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->groupBy('u.firebase_id', 'u.firstName', 'u.lastName', 'u.phoneNumber')
            ->select([
                'u.firebase_id as driver_id',
                DB::raw("CONCAT(u.firstName,' ',u.lastName) as driver_name"),
                'u.phoneNumber as phone',
                DB::raw('COUNT(ro.id) as orders_count'),
                DB::raw('SUM(IFNULL(ro.deliveryCharge,0)) as delivery_earning'),
                DB::raw('SUM(IFNULL(ro.tip_amount,0)) as tip_earning'),
                DB::raw('SUM(IFNULL(ro.deliveryCharge,0) + IFNULL(ro.tip_amount,0)) as total_earning'),
            ])
            ->get();

        foreach ($drivers as $driver) {
            $deliveryEarning = round((float)$driver->delivery_earning, 2);
            $tipEarning = round((float)$driver->tip_earning, 2);
            $totalEarning = round((float)$driver->total_earning, 2);

            // Check if settlement already exists
            $existingSettlement = DB::table('driver_settlements')
                ->where('settlement_week_id', $settlementWeekId)
                ->where('driver_id', $driver->driver_id)
                ->first();

            if ($existingSettlement) {
                // Update existing settlement: preserve incentives/deductions/transaction_id/comments, only update status and recalculate settlement_amount
                $incentives = (float)($existingSettlement->incentives ?? 0);
                $deductions = (float)($existingSettlement->deductions ?? 0);
                $settlementAmount = $totalEarning + $incentives - $deductions;

                DriverSettlement::updateOrCreate(
                    [
                        'settlement_week_id' => $settlementWeekId,
                        'driver_id' => $driver->driver_id,
                    ],
                    [
                        'driver_name' => $driver->driver_name,
                        'driver_phone' => $driver->phone,
                        'total_deliveries' => (int)$driver->orders_count,
                        'delivery_earnings' => $deliveryEarning,
                        'tips_received' => $tipEarning,
                        'settlement_amount' => round($settlementAmount, 2),
                        'payment_status' => 'settled',
                        'payment_date' => now()->toDateString(),
                        'incentives' => $incentives,
                        'deductions' => $deductions,
                        'transaction_id' => $existingSettlement->transaction_id ?? null,
                        'payment_comments' => $existingSettlement->payment_comments ?? null,
                        'updated_at' => now(),
                    ]
                );
            } else {
                // Create new settlement
                DriverSettlement::updateOrCreate(
                    [
                        'settlement_week_id' => $settlementWeekId,
                        'driver_id' => $driver->driver_id,
                    ],
                    [
                        'driver_name' => $driver->driver_name,
                        'driver_phone' => $driver->phone,
                        'total_deliveries' => (int)$driver->orders_count,
                        'delivery_earnings' => $deliveryEarning,
                        'tips_received' => $tipEarning,
                        'settlement_amount' => $totalEarning,
                        'payment_status' => 'settled',
                        'payment_date' => now()->toDateString(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    public function getDriversByDateRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date',
        ]);

        [$startDateTime, $endDateTime] = $this->normalizeDateRange(
            $request->start_date,
            $request->end_date
        );

        $completedStatuses = $this->getCompletedStatuses();
        $dateExpr = $this->getDateExpression('ro');

        /*
        |--------------------------------------------------------------------------
        | DRIVER SUMMARY
        |--------------------------------------------------------------------------
        */
        $driversQuery = DB::table('restaurant_orders as ro')
            ->join('users as u', 'u.firebase_id', '=', 'ro.driverID')
            ->leftJoin('zone as z', 'z.id', '=', 'u.zoneId')
            ->where('u.role', 'driver')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime]);

        // Apply zone filter if provided
        if ($request->has('zone_id') && !empty($request->zone_id)) {
            $driversQuery->where('u.zoneId', $request->zone_id);
        }

        $drivers = $driversQuery
            ->groupBy('u.firebase_id', 'u.firstName', 'u.lastName', 'u.phoneNumber', 'u.zoneId', 'z.name')
            ->select([
                'u.firebase_id as driver_id',
                DB::raw("CONCAT(u.firstName,' ',u.lastName) as driver_name"),
                'u.phoneNumber as phone',
                'u.zoneId',
                'z.name as zone',
                DB::raw('COUNT(ro.id) as orders_count'),
                DB::raw('SUM(IFNULL(ro.deliveryCharge,0)) as delivery_earning'),
                DB::raw('SUM(IFNULL(ro.tip_amount,0)) as tip_earning'),
                DB::raw('SUM(IFNULL(ro.deliveryCharge,0) + IFNULL(ro.tip_amount,0)) as total_earning'),
            ])
            ->get();

        /*
        |--------------------------------------------------------------------------
        | DRIVER ORDERS (EXPAND VIEW)
        |--------------------------------------------------------------------------
        */
        $orders = DB::table('restaurant_orders as ro')
            ->whereNotNull('ro.driverID')
            ->whereIn('ro.status', $completedStatuses)
            ->whereBetween(DB::raw("($dateExpr)"), [$startDateTime, $endDateTime])
            ->select([
                'ro.id',
                'ro.driverID',
                'ro.deliveryCharge',
                'ro.tip_amount',
                'ro.createdAt',
            ])
            ->orderBy('ro.createdAt')
            ->get()
            ->groupBy('driverID');

        /*
        |--------------------------------------------------------------------------
        | GET SETTLEMENT WEEK ID (if exists)
        |--------------------------------------------------------------------------
        */
        $startDateCarbon = Carbon::parse($request->start_date);
        $endDateCarbon = Carbon::parse($request->end_date);
        $settlementWeek = DB::table('settlement_weeks')
            ->where('week_start_date', $startDateCarbon->toDateString())
            ->where('week_end_date', $endDateCarbon->toDateString())
            ->where('settlement_type', 'driver')
            ->first();
        $settlementWeekId = $settlementWeek->id ?? null;

        /*
        |--------------------------------------------------------------------------
        | FINAL RESPONSE
        |--------------------------------------------------------------------------
        */
        $final = [];

        foreach ($drivers as $driver) {
            $driverOrders = $orders[$driver->driver_id] ?? collect();

            // Get saved driver settlement data if exists
            $savedSettlement = null;
            if ($settlementWeekId) {
                $savedSettlement = DB::table('driver_settlements')
                    ->where('settlement_week_id', $settlementWeekId)
                    ->where('driver_id', $driver->driver_id)
                    ->select('transaction_id', 'payment_status', 'payment_comments', 'payment_date', 'incentives', 'deductions')
                    ->first();
            }

            $deliveryEarning = round((float)$driver->delivery_earning, 2);
            $tipEarning = round((float)$driver->tip_earning, 2);
            $totalEarning = round((float)$driver->total_earning, 2);

            // Apply incentives and deductions if saved settlement exists
            $settlementAmount = $totalEarning;
            if ($savedSettlement) {
                $settlementAmount += (float)($savedSettlement->incentives ?? 0);
                $settlementAmount -= (float)($savedSettlement->deductions ?? 0);
                $settlementAmount = round($settlementAmount, 2);
            }

            $final[] = [
                'driver_id'        => $driver->driver_id,
                'driver_name'      => $driver->driver_name,
                'phone'            => $driver->phone,
                'zone'             => $driver->zone ?? 'N/A',
                'orders_count'     => (int) $driver->orders_count,
                'delivery_earning' => $deliveryEarning,
                'tip_earning'      => $tipEarning,
                'total_earning'    => $totalEarning,
                'settlement_amount' => $settlementAmount,
                'saved_settlement' => $savedSettlement ? [
                    'transaction_id' => $savedSettlement->transaction_id,
                    'payment_status' => ucfirst($savedSettlement->payment_status ?? 'pending'),
                    'payment_comments' => $savedSettlement->payment_comments,
                    'payment_date' => $savedSettlement->payment_date,
                    'incentives' => (float)($savedSettlement->incentives ?? 0),
                    'deductions' => (float)($savedSettlement->deductions ?? 0),
                ] : null,

                // Expand section
                'orders' => $driverOrders->map(function ($o) {
                    return [
                        'order_id'       => $o->id,
                        'deliveryCharge' => (float) ($o->deliveryCharge ?? 0),
                        'tip_amount'     => (float) ($o->tip_amount ?? 0),
                        'date'           => $o->createdAt,
                    ];
                })->values()
            ];
        }

        // Apply status filter if provided
        if ($request->has('status') && !empty($request->status)) {
            $statusFilter = strtolower($request->status);
            $final = array_filter($final, function($driver) use ($statusFilter) {
                $paymentStatus = null;
                if ($driver['saved_settlement'] && isset($driver['saved_settlement']['payment_status'])) {
                    $paymentStatus = strtolower($driver['saved_settlement']['payment_status']);
                }

                if ($statusFilter === 'pending') {
                    // Show drivers with no settlement or payment_status = 'pending'
                    return $paymentStatus === null || $paymentStatus === 'pending';
                } elseif ($statusFilter === 'settled') {
                    // Show drivers with payment_status = 'settled'
                    return $paymentStatus === 'settled';
                }

                return true;
            });
            // Re-index array after filtering
            $final = array_values($final);
        }

        return response()->json($final);
    }
}
