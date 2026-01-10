<?php

namespace App\Http\Controllers;
use App\Models\ReviewAttribute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Google\Client as Google_Client;

class OrderController extends Controller
{

    public function __construct()
    {
//        $this->middleware('auth')->except('getLatestOrderId');
//        $this->middleware('auth')->except('getOrder');
        $this->middleware('auth')->except([
            'getLatestOrderId',
            'getOrder'
        ]);
    }

//    public function index(Request $request, $id = '')
//    {
//        $status = $request->query('status');
//
//        $query = \App\Models\restaurant_orders::query();
//
//        if ($status) {
//            $query->where('status', $status);
//        }
//
//        $orders = $query->orderBy('id', 'desc')->paginate(10);
//
//        // Load zones from MySQL
//        $zones = DB::table('zone')
//            ->where('publish', 1)
//            ->orderBy('name', 'asc')
//            ->get(['id', 'name']);
//
//        // ✅ Pass both $status and $id to the view
//        return view('orders.index', compact('orders', 'status', 'id', 'zones'));
//    }

    public function edit($id)
    {
        // Fetch order from MySQL with joins
        $order = DB::table('restaurant_orders as ro')
            ->leftJoin('vendors as v', 'v.id', '=', 'ro.vendorID')
            ->leftJoin('users as u', 'u.id', '=', 'ro.authorID')
            ->leftJoin('users as d', 'd.id', '=', 'ro.driverID')
            ->leftJoin('zone as z', 'z.id', '=', 'v.zoneId')
            ->where('ro.id', $id)
            ->select(
                'ro.*',
                'v.id as vendor_db_id',
                'v.title as vendor_title',
                'v.vType as vendor_type',
                'v.photo as vendor_photo',
                'v.phonenumber as vendor_phone',
                'v.location as vendor_location',
                'v.zoneId as vendor_zone_id',
                'u.id as user_db_id',
                'u.firstName as user_first_name',
                'u.lastName as user_last_name',
                'u.email as user_email',
                'u.phoneNumber as user_phone',
                'd.id as driver_db_id',
                'd.firstName as driver_first_name',
                'd.lastName as driver_last_name',
                'd.email as driver_email',
                'd.phoneNumber as driver_phone',
                'z.name as zone_name'
            )
            ->first();

        if (!$order) {
            abort(404, 'Order not found');
        }

        // Parse JSON fields
        $order->products = !empty($order->products) ? json_decode($order->products, true) : [];
        $order->author = !empty($order->author) ? json_decode($order->author, true) : [];
        $order->driver = !empty($order->driver) ? json_decode($order->driver, true) : [];
        $order->address = !empty($order->address) ? json_decode($order->address, true) : [];
        $order->vendor = !empty($order->vendor) ? json_decode($order->vendor, true) : [];
        $order->specialDiscount = !empty($order->specialDiscount) ? json_decode($order->specialDiscount, true) : null;
        $order->calculatedCharges = $this->decodeJsonField($order->calculatedCharges ?? null);

        // Load currency settings
        $currency = DB::table('currencies')
            ->where('isActive', true)
            ->first();

        // Load zones for driver assignment
        $zones = DB::table('zone')
            ->where('publish', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        // Load available drivers if needed
        $availableDrivers = [];
        if (!empty($order->driverID)) {
            // Already has driver
        } else {
            // Load available drivers for assignment
            $availableDrivers = DB::table('users')
                ->where('role', 'driver')
                ->where('isActive', true)
                ->select('id', 'firstName', 'lastName', 'phoneNumber', 'email')
                ->get();
        }

        // Mirror mobile app billing logic for admin view
        $order->calculatedBillDetails = $this->calculateOrderBillDetails($order);

        return view('orders.edit', compact('order', 'currency', 'zones', 'availableDrivers', 'id'));
    }

    public function sendNotification(Request $request)
    {

        $orderStatus=$request->orderStatus;

        // Email notifications removed to prevent resource issues on shared hosting

        if(Storage::disk('local')->has('firebase/credentials.json') && ($orderStatus=="restaurantorders Accepted" || $orderStatus=="restaurantorders Rejected"|| $orderStatus=="restaurantorders Completed" || $orderStatus=="Driver Accepted")){

            $client= new Google_Client();
            $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $client->refreshTokenWithAssertion();
            $client_token = $client->getAccessToken();
            $access_token = $client_token['access_token'];

            $fcm_token = $request->fcm;

            if(!empty($access_token) && !empty($fcm_token)){

                $projectId = env('FIREBASE_PROJECT_ID');
                $url = 'https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send';

                $data = [
                    'message' => [
                        'notification' => [
                            'title' => $request->subject,
                            'body' => $request->message,
                        ],
                        'token' => $fcm_token,
                    ],
                ];

                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: Bearer '.$access_token
                );

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

                $result = curl_exec($ch);
                if ($result === FALSE) {
                    die('FCM Send Error: ' . curl_error($ch));
                }
                curl_close($ch);
                $result=json_decode($result);

                $response = array();
                $response['success'] = true;
                $response['message'] = 'Notification successfully sent.';
                $response['result'] = $result;

            }else{
                $response = array();
                $response['success'] = false;
                $response['message'] = 'Missing sender id or token to send notification.';
            }

        }else{
            $response = array();
            $response['success'] = false;
            $response['message'] = 'Firebase credentials file not found.';
        }

        return response()->json($response);
    }

    /**
     * Public method to send email notifications (for direct API calls) - DISABLED
     */
    public function sendOrderEmailNotificationPublic(Request $request)
    {
        // Email notifications disabled to prevent resource issues on shared hosting
        return response()->json([
            'success' => false,
            'message' => 'Email notifications are disabled'
        ]);
    }

    // Email notification methods removed to prevent resource issues on shared hosting

    // Helper methods removed along with email functionality

    public function orderprint($id){
        // Fetch order from MySQL with joins (same as edit)
        $order = DB::table('restaurant_orders as ro')
            ->leftJoin('vendors as v', 'v.id', '=', 'ro.vendorID')
            ->leftJoin('users as u', 'u.id', '=', 'ro.authorID')
            ->leftJoin('users as d', 'd.id', '=', 'ro.driverID')
            ->where('ro.id', $id)
            ->select(
                'ro.*',
                'v.id as vendor_db_id',
                'v.title as vendor_title',
                'v.vType as vendor_type',
                'v.photo as vendor_photo',
                'v.phonenumber as vendor_phone',
                'v.location as vendor_location',
                'u.id as user_db_id',
                'u.firstName as user_first_name',
                'u.lastName as user_last_name',
                'u.email as user_email',
                'u.phoneNumber as user_phone',
                'd.id as driver_db_id',
                'd.firstName as driver_first_name',
                'd.lastName as driver_last_name',
                'd.email as driver_email',
                'd.phoneNumber as driver_phone'
            )
            ->first();

        if (!$order) {
            abort(404, 'Order not found');
        }

        // Parse JSON fields
        $order->products = !empty($order->products) ? json_decode($order->products, true) : [];
        $order->author = !empty($order->author) ? json_decode($order->author, true) : [];
        $order->driver = !empty($order->driver) ? json_decode($order->driver, true) : [];
        $order->address = !empty($order->address) ? json_decode($order->address, true) : [];
        $order->vendor = !empty($order->vendor) ? json_decode($order->vendor, true) : [];
        $order->specialDiscount = !empty($order->specialDiscount) ? json_decode($order->specialDiscount, true) : null;

        // Load currency settings
        $currency = DB::table('currencies')
            ->where('isActive', true)
            ->first();

        return view('orders.print', compact('order', 'currency', 'id'));
    }

    /**
     * Orders index page - handles both page load and DataTables requests
     */
    public function index(Request $request, $id='')
    {
        // Check if this is a DataTables AJAX request
        if ($request->has('draw')) {
            // This is a DataTables request - return JSON (delegated to Model)
            $draw = (int) ($request->input('draw', 1));
            $orderColumnIdx = (int) data_get($request->input('order'), '0.column', 0);
            $orderDir = strtolower((string) data_get($request->input('order'), '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            $orderable = [
                0 => 'ro.id',
                1 => 'v.title',
                2 => 'ro.createdAt',
                3 => 'ro.toPayAmount',
                4 => 'ro.status',
            ];
            // Default to ro.id desc for reliable ordering
            $orderBy = $orderable[$orderColumnIdx] ?? 'ro.createdAt';

            $filters = [
                'vendor_id' => !empty($id) ? (string) $id : (string) $request->input('vendor_id', ''),
                'user_id' => (string) $request->input('user_id', ''),
                'driver_id' => (string) $request->input('driver_id', ''),
                'status' => (string) $request->input('status', ''),
                'zone_id' => (string) $request->input('zone_id', ''),
                'order_type' => (string) $request->input('order_type', ''),
                'date_from' => (string) $request->input('date_from', ''),
                'date_to' => (string) $request->input('date_to', ''),
                'search' => strtolower((string) data_get($request->input('search'), 'value', '')),
                'order_by' => $orderBy,
                'order_dir' => $orderDir,
                'start' => (int) ($request->input('start', 0)),
                'length' => (int) ($request->input('length', 10)),
            ];

            // 🔥 Date preset buttons (Last 24h, Last Week, Last Month, All Orders)
            if ($request->input('date_range') == 'last_24_hours') {
                $filters['date_from'] = now()->subDay()->toDateTimeString();
                $filters['date_to']   = now()->toDateTimeString();
            }

            if ($request->input('date_range') == 'last_week') {
                $filters['date_from'] = now()->subWeek()->startOfDay()->toDateTimeString();
                $filters['date_to']   = now()->endOfDay()->toDateTimeString();
            }

            if ($request->input('date_range') == 'last_month') {
                $filters['date_from'] = now()->subMonth()->startOfDay()->toDateTimeString();
                $filters['date_to']   = now()->endOfDay()->toDateTimeString();
            }

            // All Orders - use special flag to skip date filtering
            if ($request->input('date_range') == 'all_orders') {
                $filters['date_from'] = 'all_orders';
                $filters['date_to'] = 'all_orders';
            }

            $result = \App\Models\restaurant_orders::fetchForDatatable($filters);

            $rows = $result['rows'];
            $recordsFiltered = $result['recordsFiltered'];

            // Log search queries for debugging
            if (!empty($filters['search'])) {
                \Log::info('📡 Orders search query:', [
                    'search_term' => $filters['search'],
                    'results_count' => $recordsFiltered
                ]);
            }

            // Extract filter values for use in row processing
            $vendorId = $filters['vendor_id'];
            $userId = $filters['user_id'];
            $driverId = $filters['driver_id'];

            // Build records for DataTables
            $data = [];
            // Permission: delete
            $canDelete = false;
            try {
                $perm = @json_decode((string) session('user_permissions'), true);
                if (is_array($perm) && in_array('orders.delete', array_values($perm))) {
                    $canDelete = true;
                }
            } catch (\Throwable $e) {}

            $isVendorContext = $vendorId !== '';
            $isUserContext = $userId !== '';
            $isDriverContext = $driverId !== '';

            foreach ($rows as $row) {
                $id = (string) $row->id;
                $vendorTitle = (string) ($row->vendor_title ?? '');
                $vendorType = (string) ($row->vendor_type ?? 'restaurant');

                // If vendor join failed, try to extract from vendor JSON if available
                if (empty($vendorTitle)) {
                    // Check if there's vendor data in the order JSON (might be in a 'vendor' field)
                    // For now, just show empty or try to get from vendorID if needed
                }

                // Author/client
                $clientName = '';
                if (!empty($row->user_first_name) || !empty($row->user_last_name)) {
                    $clientName = trim(($row->user_first_name ?? '') . ' ' . ($row->user_last_name ?? ''));
                }
                // Always try JSON as fallback (in case join didn't match)
                if (empty($clientName)) {
                    $clientName = $this->extractNameFromJson($row->author);
                }
                if (empty($clientName)) {
                    $clientName = 'N/A';
                }

                // Driver name from JSON if available
                $driverName = $this->extractNameFromJson($row->driver);

                // // Order type
                // $takeAway = strtolower((string) ($row->takeAway ?? ''));
                // $orderTypeText = ($takeAway === '1' || $takeAway === 'true') ? trans('lang.order_takeaway') : trans('lang.order_delivery');

                // Amount
                $amountValue = $this->resolveAmount($row);
                $amountText = $this->formatCurrency($amountValue);

                // Date render - Format like "Oct 1, 2025 11:27 PM"
                $dateText = '';
                if (!empty($row->createdAt)) {
                    try {
                        // Parse ISO 8601 string (e.g., "2025-10-14T14:53:43.860219Z")
                        $date = \Carbon\Carbon::parse($row->createdAt)
                            ->setTimezone('Asia/Kolkata');
                        // Format: "Oct 1, 2025 11:27 PM"
                        $dateText = $date->format('M j, Y g:i A');

                        // Log first row for debugging
                        if ($id === ($rows->first()->id ?? '')) {
                            \Log::info('📅 Date formatting sample:', [
                                'raw_date' => $row->createdAt,
                                'formatted_date' => $dateText,
                                'format_used' => 'M j, Y g:i A'
                            ]);
                        }
                    } catch (\Throwable $e) {
                        // Fallback to raw string if parsing fails
                        \Log::warning('⚠️ Date parsing failed:', ['date' => $row->createdAt, 'error' => $e->getMessage()]);
                        $dateText = (string) $row->createdAt;
                    }
                }

                // Status
                $statusText = (string) ($row->status ?? '');

                // Build action URLs (keep same routes)
                $editUrl = route('orders.edit', $id);
                if ($vendorId !== '') {
                    $editUrl .= '?eid=' . $vendorId;
                }
                $printUrl = route('vendors.orderprint', $id);
                if ($vendorId !== '') {
                    $printUrl .= '?eid=' . $vendorId;
                }

                // Build cells matching exact table structure from blade file
                $rowCells = [];

                // Column 0: Checkbox (if delete permission)
                if ($canDelete) {
                    $rowCells[] = '<input type="checkbox" id="is_open_' . e($id) . '" class="is_open" dataId="' . e($id) . '"><label class="col-3 control-label" for="is_open_' . e($id) . '"></label>';
                }

                // Column 1: Order ID
                $rowCells[] = '<a href="' . e($editUrl) . '" class="redirecttopage">' . e($id) . '</a>';

                // Column 2: Restaurant (only if $id == '', i.e., not vendor context)
                if (!$isVendorContext) {
                    // Check if vendor_id exists before generating route
                    if (!empty($row->vendor_id)) {
                        $vendorViewUrl = ($vendorType === 'mart' ? route('marts.view', $row->vendor_id) : route('restaurants.view', $row->vendor_id));
                        $rowCells[] = '<a href="' . e($vendorViewUrl) . '">' . e($vendorTitle ?: 'N/A') . '</a>';
                    } else {
                        $rowCells[] = e($vendorTitle ?: 'N/A');
                    }
                }

                // Column 3: Driver or User depending on context
                // If userId is set: show Driver column
                // If driverId is set: show User column
                // Default: show Driver then User
                if ($isUserContext) {
                    // User context: show Driver column
                    $driverLink = '';
                    if (!empty($driverName)) {
                        // Extract driver ID from JSON if possible
                        $driverIdFromJson = $this->extractIdFromJson($row->driver);
                        if ($driverIdFromJson) {
                            $driverViewUrl = route('drivers.view', $driverIdFromJson);
                            $driverLink = '<a href="' . e($driverViewUrl) . '">' . e($driverName) . '</a>';
                        } else {
                            $driverLink = e($driverName);
                        }
                    }
                    $rowCells[] = $driverLink;
                } elseif ($isDriverContext) {
                    // Driver context: show User/Client column
                    $authorId = $row->authorID ?? '';
                    if (!empty($authorId)) {
                        $userViewUrl = route('users.view', $authorId);
                        $rowCells[] = '<a href="' . e($userViewUrl) . '">' . e($clientName ?: 'N/A') . '</a>';
                    } else {
                        $rowCells[] = e($clientName ?: 'N/A');
                    }
                } else {
                    // Default: show Driver then User
                    $driverLink = '';
                    if (!empty($driverName)) {
                        $driverIdFromJson = $this->extractIdFromJson($row->driver);
                        if ($driverIdFromJson) {
                            $driverViewUrl = route('drivers.view', $driverIdFromJson);
                            $driverLink = '<a href="' . e($driverViewUrl) . '">' . e($driverName) . '</a>';
                        } else {
                            $driverLink = e($driverName);
                        }
                    }
                    $rowCells[] = $driverLink;

                    // User column
                    $authorId = $row->authorID ?? '';
                    if (!empty($authorId)) {
                        $userViewUrl = route('users.view', $authorId);
                        $rowCells[] = '<a href="' . e($userViewUrl) . '" class="redirecttopage">' . e($clientName ?: 'N/A') . '</a>';
                    } else {
                        $rowCells[] = '<span class="redirecttopage">' . e($clientName ?: 'N/A') . '</span>';
                    }
                }

                // Date column
                $rowCells[] = '<span class="dt-time">' . e($dateText) . '</span>';

                // Amount column
                $rowCells[] = '<span class="text-green">' . e($amountText) . '</span>';

                // Order Type column
                // $rowCells[] = e($orderTypeText);

                // Status column with proper styling
                $statusClass = $this->getStatusClass($statusText);
                $rowCells[] = '<span class="' . e($statusClass) . '"><span>' . e($statusText) . '</span></span>';

                // Actions column
                $actionHtml = '<span class="action-btn">';
                $actionHtml .= '<a href="' . e($printUrl) . '"><i class="fa fa-print" style="font-size:20px;"></i></a>';
                $actionHtml .= '<a href="' . e($editUrl) . '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>';

                // Add delete button if permission
                if ($canDelete) {
                    $actionHtml .= '<a id="' . e($id) . '" class="delete-btn" name="order-delete" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a>';
                }

                $actionHtml .= '</span>';
                $rowCells[] = $actionHtml;

                $data[] = $rowCells;
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $recordsFiltered,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        // Regular page load - return view with zones
        $status = $request->query('status');

        // Load zones from MySQL
        $zones = DB::table('zone')
            ->where('publish', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name']);

        return view('orders.index', compact('status', 'id', 'zones'));
    }

    private function calculateOrderBillDetails($order): array
    {
        $defaults = [
            'subTotal' => 0.0,
            'deliveryCharges' => 0.0,
            'originalDeliveryFee' => 0.0,
            'couponAmount' => 0.0,
            'specialDiscountAmount' => 0.0,
            'taxAmount' => 0.0,
            'deliveryTips' => $this->floatValue($order->tip_amount ?? 0),
            'totalAmount' => 0.0,
            'isFreeDelivery' => false,
            'totalDistance' => 0.0,
            'hasPromotionalItems' => false,
        ];

        $products = is_array($order->products) ? $order->products : [];
        if (empty($products)) {
            return $defaults;
        }

        $products = array_map(function ($product) {
            return is_array($product) ? $product : (array) $product;
        }, $products);

        $vendorData = is_array($order->vendor) ? $order->vendor : (array) ($order->vendor ?? []);
        $deliveryConfig = $this->getDeliveryChargeSettings();
        $vendorDelivery = [];
        if (!empty($vendorData['DeliveryCharge'])) {
            $vendorDelivery = is_array($vendorData['DeliveryCharge'])
                ? $vendorData['DeliveryCharge']
                : $this->decodeJsonField($vendorData['DeliveryCharge'], []);
        }
        if (!empty($vendorDelivery)) {
            $vendorDelivery = array_filter($vendorDelivery, function ($value) {
                return $value !== null && $value !== '';
            });
            $deliveryConfig = array_merge($deliveryConfig, $vendorDelivery);
        }

        $threshold = (float) ($deliveryConfig['item_total_threshold'] ?? 299.0);
        $baseCharge = (float) ($deliveryConfig['base_delivery_charge'] ?? 23.0);
        $freeKm = (float) ($deliveryConfig['free_delivery_distance_km'] ?? 5.0);
        $perKm = (float) ($deliveryConfig['per_km_charge_above_free_distance'] ?? 7.0);

        $subTotal = 0.0;
        $promotionalProducts = [];

        foreach ($products as $product) {
            $priceValue = $this->floatValue($product['price'] ?? 0);
            $discountPriceValue = $this->floatValue($product['discountPrice'] ?? 0);
            $promoId = $product['promoId'] ?? ($product['promo_id'] ?? null);
            $hasPromo = !empty($promoId);
            $isPricePromotional = $priceValue > 0 && $discountPriceValue > 0 && $priceValue < $discountPriceValue;
            $isPromotional = $hasPromo || $isPricePromotional;

            if ($isPromotional) {
                $promotionalProducts[] = $product;
            }

            if ($isPromotional) {
                if ($priceValue > 0 && $discountPriceValue > 0) {
                    $itemPrice = min($priceValue, $discountPriceValue);
                } elseif ($discountPriceValue > 0) {
                    $itemPrice = $discountPriceValue;
                } else {
                    $itemPrice = $priceValue;
                }
            } elseif ($discountPriceValue <= 0) {
                $itemPrice = $priceValue;
            } else {
                $itemPrice = $discountPriceValue;
            }

            $quantity = $this->floatValue($product['quantity'] ?? 1);
            if ($quantity <= 0) {
                $quantity = 1;
            }
            $extrasPrice = $this->floatValue($product['extrasPrice'] ?? ($product['extras_price'] ?? 0));
            $itemTotal = ($itemPrice * $quantity) + ($extrasPrice * $quantity);
            $subTotal += $itemTotal;
        }

        $hasPromotionalItems = !empty($promotionalProducts);
        $deliveryTips = $this->floatValue($order->tip_amount ?? 0);
        $totalDistance = $this->resolveTotalDistanceKm($order);
        $vendorId = $order->vendorID ?? ($vendorData['id'] ?? null);
        $selfDeliveryFeature = $this->isSelfDeliveryFeatureEnabled();
        $vendorSelfDelivery = $this->boolValue($vendorData['isSelfDelivery'] ?? false);

        $deliveryCharges = 0.0;
        $originalDeliveryFee = $baseCharge;
        $promotionDetails = null;

        if ($vendorSelfDelivery && $selfDeliveryFeature) {
            $deliveryCharges = 0.0;
            $originalDeliveryFee = 0.0;
        } elseif ($hasPromotionalItems) {
            $firstPromotionalProduct = (array) $promotionalProducts[0];
            $promoProductId = $firstPromotionalProduct['id'] ?? ($firstPromotionalProduct['product_id'] ?? null);
            $promotionDetails = $this->fetchPromotionDetails($promoProductId, $vendorId);

            if ($promotionDetails) {
                $promoFreeKm = (float) ($promotionDetails['free_delivery_km'] ?? $freeKm);
                $promoExtraKmCharge = (float) ($promotionDetails['extra_km_charge'] ?? $perKm);

                if ($totalDistance <= $promoFreeKm) {
                    $deliveryCharges = 0.0;
                    $originalDeliveryFee = $baseCharge;
                } else {
                    $extraKm = max(0.0, ceil($totalDistance - $promoFreeKm));
                    $deliveryCharges = $extraKm * $promoExtraKmCharge;
                    $originalDeliveryFee = $deliveryCharges;
                }
            } else {
                [$deliveryCharges, $originalDeliveryFee] = $this->calculateRegularDeliveryCharges(
                    $subTotal,
                    $threshold,
                    $totalDistance,
                    $freeKm,
                    $baseCharge,
                    $perKm
                );
            }
        } else {
            [$deliveryCharges, $originalDeliveryFee] = $this->calculateRegularDeliveryCharges(
                $subTotal,
                $threshold,
                $totalDistance,
                $freeKm,
                $baseCharge,
                $perKm
            );
        }

        $couponAmount = 0.0;
        if (!$hasPromotionalItems && !empty($order->couponId)) {
            $couponAmount = $this->floatValue($order->discount ?? 0);
        }
        $couponAmount = max(0.0, min($couponAmount, $subTotal));

        $specialDiscountData = is_array($order->specialDiscount) ? $order->specialDiscount : (array) ($order->specialDiscount ?? []);
        $specialDiscountAmount = $this->floatValue($specialDiscountData['special_discount'] ?? 0);
        $specialDiscountCap = max(0.0, $subTotal - $couponAmount);
        $specialDiscountAmount = max(0.0, min($specialDiscountAmount, $specialDiscountCap));

        $sgst = $subTotal * 0.05;
        $gst = $deliveryCharges > 0 ? ($deliveryCharges * 0.18) : 0.0;

        if (!is_finite($sgst) || $sgst < 0) {
            $sgst = 0.0;
        }
        if (!is_finite($gst) || $gst < 0) {
            $gst = 0.0;
        }

        $taxAmount = $sgst + $gst;
        if (!is_finite($taxAmount) || $taxAmount < 0) {
            $taxAmount = 0.0;
        }

        $isFreeDelivery = false;
        if ($hasPromotionalItems) {
            if ($promotionDetails && isset($promotionDetails['free_delivery_km'])) {
                if ($totalDistance <= (float) $promotionDetails['free_delivery_km']) {
                    $isFreeDelivery = true;
                }
            } elseif ($subTotal >= $threshold && $totalDistance <= $freeKm) {
                $isFreeDelivery = true;
            }
        } else {
            if ($subTotal >= $threshold && $totalDistance <= $freeKm) {
                $isFreeDelivery = true;
            }
        }

        $netSubtotal = max(0.0, $subTotal - $couponAmount - $specialDiscountAmount);
        $totalAmount = $netSubtotal + $taxAmount + ($isFreeDelivery ? 0.0 : $deliveryCharges) + $deliveryTips;
        $totalAmount = max(0.0, $totalAmount);

        return [
            'subTotal' => round($subTotal, 2),
            'deliveryCharges' => round($deliveryCharges, 2),
            'originalDeliveryFee' => round($originalDeliveryFee, 2),
            'couponAmount' => round($couponAmount, 2),
            'specialDiscountAmount' => round($specialDiscountAmount, 2),
            'taxAmount' => round($taxAmount, 2),
            'deliveryTips' => round($deliveryTips, 2),
            'totalAmount' => round($totalAmount, 2),
            'isFreeDelivery' => $isFreeDelivery,
            'totalDistance' => round($totalDistance, 2),
            'hasPromotionalItems' => $hasPromotionalItems,
        ];
    }

    private function getDeliveryChargeSettings(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $defaults = [
            'base_delivery_charge' => 23,
            'free_delivery_distance_km' => 5,
            'per_km_charge_above_free_distance' => 7,
            'item_total_threshold' => 299,
        ];

        $record = DB::table('settings')->where('document_name', 'DeliveryCharge')->first();
        if ($record && !empty($record->fields)) {
            $decoded = json_decode($record->fields, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $filtered = array_filter($decoded, function ($value) {
                    return $value !== null && $value !== '';
                });
                $cache = array_merge($defaults, $filtered);
                return $cache;
            }
        }

        return $cache = $defaults;
    }

    private function isSelfDeliveryFeatureEnabled(): bool
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $record = DB::table('settings')->where('document_name', 'globalSettings')->first();
        if ($record && !empty($record->fields)) {
            $decoded = json_decode($record->fields, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && array_key_exists('isSelfDelivery', $decoded)) {
                return $cache = (bool) $decoded['isSelfDelivery'];
            }
        }

        return $cache = false;
    }

    private function fetchPromotionDetails(?string $productId, ?string $vendorId): ?array
    {
        if (empty($productId) || empty($vendorId)) {
            return null;
        }

        try {
            $promotion = DB::table('promotions')
                ->where('product_id', $productId)
                ->where('restaurant_id', $vendorId)
                ->where('isAvailable', 1)
                ->orderByDesc('id')
                ->first();

            if (!$promotion) {
                return null;
            }

            $now = Carbon::now();
            if (!empty($promotion->start_time)) {
                try {
                    if (Carbon::parse($promotion->start_time)->gt($now)) {
                        return null;
                    }
                } catch (\Throwable $e) {
                }
            }
            if (!empty($promotion->end_time)) {
                try {
                    if (Carbon::parse($promotion->end_time)->lt($now)) {
                        return null;
                    }
                } catch (\Throwable $e) {
                }
            }

            $config = $this->getDeliveryChargeSettings();

            return [
                'free_delivery_km' => (float) ($promotion->free_delivery_km ?? $config['free_delivery_distance_km']),
                'extra_km_charge' => (float) ($promotion->extra_km_charge ?? $config['per_km_charge_above_free_distance']),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function calculateRegularDeliveryCharges(
        float $subTotal,
        float $threshold,
        float $distance,
        float $freeKm,
        float $baseCharge,
        float $perKm
    ): array {
        $deliveryCharges = 0.0;
        $originalDeliveryFee = $baseCharge;

        if ($subTotal < $threshold) {
            if ($distance <= $freeKm) {
                $deliveryCharges = $baseCharge;
                $originalDeliveryFee = $baseCharge;
            } else {
                $extraKm = max(0.0, ceil($distance - $freeKm));
                $deliveryCharges = $baseCharge + ($extraKm * $perKm);
                $originalDeliveryFee = $deliveryCharges;
            }
        } else {
            if ($distance <= $freeKm) {
                $deliveryCharges = 0.0;
                $originalDeliveryFee = $baseCharge;
            } else {
                $extraKm = max(0.0, ceil($distance - $freeKm));
                $deliveryCharges = $extraKm * $perKm;
                $originalDeliveryFee = $baseCharge + ($extraKm * $perKm);
            }
        }

        return [max(0.0, $deliveryCharges), max(0.0, $originalDeliveryFee)];
    }

    private function resolveTotalDistanceKm($order): float
    {
        $candidates = [];
        if (is_array($order->calculatedCharges ?? null)) {
            $candidates[] = $order->calculatedCharges;
        }
        $distanceKeys = ['total_distance', 'distance', 'distance_km', 'delivery_distance', 'distanceKm', 'km'];
        foreach ($candidates as $payload) {
            foreach ($distanceKeys as $key) {
                $value = data_get($payload, $key);
                if (is_numeric($value)) {
                    $dist = (float) $value;
                    if ($dist > 0) {
                        return $dist;
                    }
                }
            }
        }

        $addressDistance = data_get($order->address ?? [], 'distance');
        if (is_numeric($addressDistance)) {
            return max(0.0, (float) $addressDistance);
        }

        $addressLat = $this->resolveCoordinate($order->address ?? [], ['location.latitude', 'latitude', 'lat']);
        $addressLng = $this->resolveCoordinate($order->address ?? [], ['location.longitude', 'longitude', 'lng', 'lon']);
        $vendorLat = $this->resolveCoordinate($order->vendor ?? [], [
            'coordinates.latitude',
            'location.latitude',
            'latitude',
            'lat',
            'g.geopoint.latitude'
        ]);
        $vendorLng = $this->resolveCoordinate($order->vendor ?? [], [
            'coordinates.longitude',
            'location.longitude',
            'longitude',
            'lng',
            'g.geopoint.longitude'
        ]);

        if ($addressLat !== null && $addressLng !== null && $vendorLat !== null && $vendorLng !== null) {
            return $this->haversineDistanceKm($addressLat, $addressLng, $vendorLat, $vendorLng);
        }

        return 0.0;
    }

    private function resolveCoordinate($source, array $paths): ?float
    {
        if (empty($source)) {
            return null;
        }
        $data = is_array($source) ? $source : (array) $source;
        foreach ($paths as $path) {
            $value = data_get($data, $path);
            if (is_numeric($value)) {
                return (float) $value;
            }
        }
        return null;
    }

    private function haversineDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;
        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);
        $a = sin($latDelta / 2) ** 2 +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lonDelta / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return max(0.0, $earthRadius * $c);
    }

    private function floatValue($value): float
    {
        if (is_null($value)) {
            return 0.0;
        }

        if (is_bool($value)) {
            return $value ? 1.0 : 0.0;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value)) {
            $clean = preg_replace('/[^0-9\.\-]+/', '', $value);
            if ($clean === '' || !is_numeric($clean)) {
                return 0.0;
            }
            return (float) $clean;
        }

        return 0.0;
    }

    private function boolValue($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        $normalized = strtolower((string) $value);
        return in_array($normalized, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    private function decodeJsonField($value, $default = [])
    {
        if (is_array($value)) {
            return $value;
        }
        if (!is_string($value)) {
            return $default;
        }
        $trimmed = trim($value);
        if ($trimmed === '' || strtolower($trimmed) === 'null') {
            return $default;
        }
        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
            return $decoded;
        }
        return $default;
    }

    private function extractNameFromJson($json)
    {
        if (empty($json)) return '';
        try {
            $data = is_string($json) ? json_decode($json, true) : $json;
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                $first = (string) ($data['firstName'] ?? '');
                $last = (string) ($data['lastName'] ?? '');
                return trim(($first . ' ' . $last));
            }
        } catch (\Throwable $e) {}
        return '';
    }

    private function extractIdFromJson($json)
    {
        if (empty($json)) return '';
        try {
            $data = is_string($json) ? json_decode($json, true) : $json;
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                return (string) ($data['id'] ?? '');
            }
        } catch (\Throwable $e) {}
        return '';
    }

    private function getStatusClass($status)
    {
        $status = strtolower(trim((string) $status));
        $classMap = [
            'restaurantorders placed' => 'order_placed',
            'orders placed' => 'order_placed',
            'order placed' => 'order_placed',
            'restaurantorders accepted' => 'order_accepted',
            'order accepted' => 'order_accepted',
            'restaurantorders rejected' => 'order_rejected',
            'order rejected' => 'order_rejected',
            'driver pending' => 'driver_pending',
            'driver rejected' => 'driver_rejected',
            'restaurantorders shipped' => 'order_shipped',
            'order shipped' => 'order_shipped',
            'in transit' => 'in_transit',
            'restaurantorders completed' => 'order_completed',
            'order completed' => 'order_completed',
        ];
        return $classMap[$status] ?? 'order_completed';
    }

    private function resolveAmount($row): float
    {
        // Prefer toPayAmount (check if it's not null and not empty string and not "0")
        if (!is_null($row->toPayAmount) && $row->toPayAmount !== '' && $row->toPayAmount !== '0') {
            $amount = (float) $row->toPayAmount;
            if ($amount > 0) {
                return $amount;
            }
        }
        // Fallback: compute from products JSON and charges
        $total = 0.0;
        try {
            $products = is_string($row->products) ? json_decode($row->products, true) : $row->products;
            if (json_last_error() === JSON_ERROR_NONE && is_array($products)) {
                foreach ($products as $product) {
                    $price = (float) ($product['discountPrice'] ?? 0);
                    if ($price <= 0) {
                        $price = (float) ($product['price'] ?? 0);
                    }
                    $qty = (int) ($product['quantity'] ?? 1);
                    $extrasPrice = (float) ($product['extras_price'] ?? 0);
                    $total += ($price * $qty) + $extrasPrice;
                }
            }
            $delivery = (float) ($row->deliveryCharge ?? 0);
            $discount = (float) ($row->discount ?? 0);
            $tip = (float) ($row->tip_amount ?? 0);
            $specialDiscount = 0.0;
            if (!empty($row->specialDiscount)) {
                $sd = is_string($row->specialDiscount) ? json_decode($row->specialDiscount, true) : $row->specialDiscount;
                if (json_last_error() === JSON_ERROR_NONE && is_array($sd)) {
                    $specialDiscount = (float) ($sd['special_discount'] ?? 0);
                }
            }
            $total = max(0.0, $total + $delivery + $tip - $discount - $specialDiscount);
        } catch (\Throwable $e) {
            $total = 0.0;
        }
        return $total;
    }

    private function formatCurrency(float $amount): string
    {
        // Simple formatting; extend to read active currency settings if needed
        $symbol = config('app.currency_symbol', '₹');
        $atRight = (bool) config('app.currency_symbol_at_right', false);
        $digits = (int) config('app.currency_decimal_digits', 2);
        $val = number_format($amount, $digits, '.', '');
        return $atRight ? ($val . $symbol) : ($symbol . $val);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|string'
            ]);

            $order = DB::table('restaurant_orders')->where('id', $id)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $oldStatus = $order->status;
            $newStatus = $request->input('status');

            DB::table('restaurant_orders')
                ->where('id', $id)
                ->update(['status' => $newStatus]);

            // Log activity
            \Log::info('✅ Order status updated:', [
                'order_id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully',
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error updating order status:', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating order status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign driver to order
     */
    public function assignDriver(Request $request, $id)
    {
        try {
            $request->validate([
                'driver_id' => 'required|string'
            ]);

            $order = DB::table('restaurant_orders')->where('id', $id)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $driverId = $request->input('driver_id');

            // Get driver details
            $driver = DB::table('users')->where('id', $driverId)->where('role', 'driver')->first();

            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found'
                ], 404);
            }

            // IMPORTANT: Do NOT directly assign driverID to the order
            // Instead, add order ID to driver's orderRequestData to trigger popup in driver app
            // Driver must accept/reject through the app, then driverID will be set automatically

            // Append this order ID to the driver's orderRequestData (JSON array) without duplicating
            try {
                $orderRequestData = [];
                $raw = $driver->orderRequestData ?? '[]';
                if (is_string($raw) && trim($raw) !== '') {
                    $decoded = json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $orderRequestData = $decoded;
                    }
                } elseif (is_array($raw)) {
                    $orderRequestData = $raw;
                }

                if (!in_array($id, $orderRequestData, true)) {
                    $orderRequestData[] = $id;
                    DB::table('users')
                        ->where('id', $driverId)
                        ->update(['orderRequestData' => json_encode($orderRequestData)]);

                    // Log activity
                    \Log::info('✅ Order request sent to driver (pending acceptance):', [
                        'order_id' => $id,
                        'driver_id' => $driverId,
                        'driver_name' => ($driver->firstName ?? '') . ' ' . ($driver->lastName ?? '')
                    ]);
                } else {
                    // Order already in request list
                    \Log::info('ℹ️ Order already in driver request list:', [
                        'order_id' => $id,
                        'driver_id' => $driverId
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::error('❌ Could not update driver orderRequestData on manual assign', [
                    'order_id' => $id,
                    'driver_id' => $driverId,
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error sending order request to driver: ' . $e->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order request sent to driver. Driver will receive a notification to accept or reject the order.',
                'driver_id' => $driverId,
                'driver_name' => ($driver->firstName ?? '') . ' ' . ($driver->lastName ?? ''),
                'pending_acceptance' => true
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error assigning driver:', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error assigning driver: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove driver from order
     */
    public function removeDriver(Request $request, $id)
    {
        try {
            $order = DB::table('restaurant_orders')->where('id', $id)->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $oldDriverId = $order->driverID;
            $oldDriverData = !empty($order->driver) ? json_decode($order->driver, true) : [];
            $oldDriverName = ($oldDriverData['firstName'] ?? '') . ' ' . ($oldDriverData['lastName'] ?? '');

            // Clear driver assignment from order
            DB::table('restaurant_orders')
                ->where('id', $id)
                ->update([
                    'driverID' => null,
                    'driver' => null
                ]);

            // Remove this order ID from driver's orderRequestData
            // Case 1: Driver was already assigned (driverID is set)
            if (!empty($oldDriverId)) {
                try {
                    $raw = DB::table('users')->where('id', $oldDriverId)->value('orderRequestData');
                    $orderRequestData = [];
                    if (is_string($raw) && trim($raw) !== '') {
                        $decoded = json_decode($raw, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $orderRequestData = $decoded;
                        }
                    } elseif (is_array($raw)) {
                        $orderRequestData = $raw;
                    }

                    $updated = array_values(array_filter($orderRequestData, function ($val) use ($id) {
                        return (string) $val !== (string) $id;
                    }));

                    DB::table('users')
                        ->where('id', $oldDriverId)
                        ->update(['orderRequestData' => json_encode($updated)]);
                } catch (\Throwable $e) {
                    \Log::warning('⚠️ Could not clean orderRequestData on driver removal', [
                        'order_id' => $id,
                        'driver_id' => $oldDriverId,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                // Case 2: No driverID set (request was sent but not accepted yet)
                // Find and remove order ID from any driver's orderRequestData
                try {
                    $drivers = DB::table('users')
                        ->where('role', 'driver')
                        ->whereNotNull('orderRequestData')
                        ->get(['id', 'orderRequestData']);

                    foreach ($drivers as $driver) {
                        $raw = $driver->orderRequestData;
                        $orderRequestData = [];
                        if (is_string($raw) && trim($raw) !== '') {
                            $decoded = json_decode($raw, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $orderRequestData = $decoded;
                            }
                        } elseif (is_array($raw)) {
                            $orderRequestData = $raw;
                        }

                        // Check if this driver has the order ID in their request list
                        if (in_array($id, $orderRequestData, true)) {
                            $updated = array_values(array_filter($orderRequestData, function ($val) use ($id) {
                                return (string) $val !== (string) $id;
                            }));

                            DB::table('users')
                                ->where('id', $driver->id)
                                ->update(['orderRequestData' => json_encode($updated)]);

                            \Log::info('✅ Removed pending order request from driver:', [
                                'order_id' => $id,
                                'driver_id' => $driver->id
                            ]);
                            break; // Found and removed, no need to check other drivers
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning('⚠️ Could not remove pending order request from drivers', [
                        'order_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Log activity
            if (!empty($oldDriverId)) {
                \Log::info('✅ Driver removed from order:', [
                    'order_id' => $id,
                    'driver_id' => $oldDriverId,
                    'driver_name' => $oldDriverName
                ]);
            } else {
                \Log::info('✅ Pending order request cancelled:', [
                    'order_id' => $id
                ]);
            }

            $message = !empty($oldDriverId)
                ? 'Driver removed successfully'
                : 'Pending order request cancelled successfully';

            return response()->json([
                'success' => true,
                'message' => $message,
                'old_driver_id' => $oldDriverId,
                'old_driver_name' => $oldDriverName
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error removing driver:', [
                'order_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error removing driver: ' . $e->getMessage()
            ], 500);
        }
    }
//    public function getLatestOrderId()
//    {
//        $last = DB::table('restaurant_orders')->orderBy('id', 'desc')->first();
//        return response()->json(['latest_id' => $last->id ?? 0]);
//    }

    public function getLatestOrderId()
    {
        $last = DB::table('restaurant_orders')
            ->select('id')
            ->orderByRaw('CAST(SUBSTRING(id, 6) AS UNSIGNED) DESC') // from "Jippy30001018"
            ->first();

        return response()->json(['latest_id' => $last->id ?? '']);
    }


    public function getOrder($id)
    {
        $order = DB::table('restaurant_orders')->where('id', $id)->first();

        if (!$order) return response()->json([]);

        // Convert JSON fields if needed
        // Fetch vendor details from vendors table
        $vendor = DB::table('vendors')
            ->where('id', $order->vendorID)
            ->select('id', 'title', 'photo', 'vType', 'phonenumber', 'location')
            ->first();

        $order->vendor_title = $vendor->title ?? null;
        $order->vendor_photo = $vendor->photo ?? null;
        $order->vendor_full = $vendor;   // optional full vendor object

        $order->author = json_decode($order->author, true);

        return response()->json($order);
    }

    public function attributes(){
        return ReviewAttribute::select('id', 'title')->get();
    }

    public function orderReviews($orderId)
    {
        $reviews = DB::table('foods_review')
            ->where('orderid', $orderId)
            ->orderByDesc('createdAt')
            ->get()
            ->map(function ($review) {
                // decode json values
                $review->photos = json_decode($review->photos ?? '[]', true);
                $review->reviewAttributes = json_decode($review->reviewAttributes ?? '{}', true);

                // ⭐ format the createdAt datetime in Asia/Kolkata timezone
                // Format: "Nov 29, 2025" (like screenshot)
                if (!empty($review->createdAt)) {
                    try {
                        $date = \Carbon\Carbon::parse($review->createdAt)
                            ->setTimezone('Asia/Kolkata');
                        $review->createdAtFormatted = $date->format('M j, Y'); // "Nov 29, 2025"
                        $review->createdAtFormattedWithTime = $date->format('M j, Y h:i A'); // "Nov 29, 2025 6:06 PM"
                    } catch (\Exception $e) {
                        $review->createdAtFormatted = null;
                        $review->createdAtFormattedWithTime = null;
                    }
                } else {
                    $review->createdAtFormatted = null;
                    $review->createdAtFormattedWithTime = null;
                }

                return $review;
            });

        return response()->json(['data' => $reviews]);
    }

}
