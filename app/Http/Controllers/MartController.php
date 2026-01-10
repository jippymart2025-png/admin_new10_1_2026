<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Vendor;

class MartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // If DataTables request, return JSON
        if (request()->has('draw')) {
            $request = request();
            $draw = (int) ($request->input('draw', 1));
            $orderColumnIdx = (int) data_get($request->input('order'), '0.column', 0);
            $orderDir = strtolower((string) data_get($request->input('order'), '0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';

            // Orderable columns mapping (keep minimal/reliable)
            $orderable = [
                0 => 'v.createdAt',
                1 => 'v.title',
                2 => 'u.firstName',
                3 => 'v.createdAt',
            ];
            $orderBy = $orderable[$orderColumnIdx] ?? 'v.createdAt';

            $start = (int) ($request->input('start', 0));
            $length = (int) ($request->input('length', 10));
            $search = strtolower((string) data_get($request->input('search'), 'value', ''));
            $zoneId = (string) $request->input('zone_id', '');

            $query = DB::table('vendors as v')
                ->leftJoin('users as u', 'u.vendorID', '=', 'v.id')
                ->leftJoin('zone as z', 'z.id', '=', 'v.zoneId')
                ->where('v.vType', '=', 'mart');

            if ($zoneId !== '') {
                $query->where('v.zoneId', $zoneId);
            }

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where('v.title', 'like', "%{$search}%")
                      ->orWhere('u.firstName', 'like', "%{$search}%")
                      ->orWhere('u.lastName', 'like', "%{$search}%")
                      ->orWhere('v.location', 'like', "%{$search}%")
                      ->orWhere('u.email', 'like', "%{$search}%")
                      ->orWhere('u.phoneNumber', 'like', "%{$search}%");
                });
            }

            $recordsFiltered = (clone $query)->count();

            $rows = $query->select(
                    'v.id', 'v.title', 'v.photo', 'v.location', 'v.phonenumber', 'v.createdAt', DB::raw('v.adminCommission as admin_commission'),
                    'u.firstName as u_first', 'u.lastName as u_last', 'u.id as u_id', 'u.phoneNumber as u_phone',
                    'z.name as zone_name'
                )
                ->orderBy($orderBy, $orderDir)
                ->skip($start)->take($length)
                ->get();

            $canDelete = false;
            try {
                $perm = @json_decode((string) session('user_permissions'), true);
                if (is_array($perm)) {
                    $vals = array_values($perm);
                    $canDelete = in_array('marts.delete', $vals) || in_array('mart.delete', $vals);
                }
            } catch (\Throwable $e) {}

            $data = [];
            foreach ($rows as $row) {
                $id = (string) $row->id;
                $vendorTitle = (string) ($row->title ?? '');
                $ownerName = trim((string) (($row->u_first ?? '') . ' ' . ($row->u_last ?? '')));
                $phone = (string) ($row->phonenumber ?? $row->u_phone ?? '');
                $createdAt = (string) ($row->createdAt ?? '');
                try {
                    $createdAtNorm = is_string($createdAt) ? trim($createdAt, "\" ") : $createdAt;
                    $dt = \Carbon\Carbon::parse($createdAtNorm);
                    $createdAtText = $dt->format('M d, Y h:i A');
                } catch (\Throwable $e) {
                    $createdAtText = $createdAt;
                }

                $editUrl = route('marts.edit', $id);
                $viewUrl = route('marts.view', $id);
                $foodsUrl = route('marts.foods', $id);
                $ordersUrl = route('marts.orders', $id);
                $walletUrl = $row->u_id ? route('users.walletstransaction', $row->u_id) : '#';

                $rowCells = [];
                if ($canDelete) {
                    $rowCells[] = '<input type="checkbox" id="is_open_' . e($id) . '" class="is_open" dataId="' . e($id) . '"><label class="col-3 control-label" for="is_open_' . e($id) . '"></label>';
                }

                // Mart info (image + title link)
                $placeholder = asset('assets/images/placeholder-image.png');
                $imgSrc = $row->photo ? e($row->photo) : e($placeholder);
                $martInfo = '<img onerror="this.onerror=null;this.src=\'' . $placeholder . '\'" style="width:70px;height:70px;" src="' . $imgSrc . '">';
                $martInfo .= ' <a href="' . e($viewUrl) . '">' . e($vendorTitle ?: 'UNKNOWN') . '</a>';
                $rowCells[] = $martInfo;

                // Owner info
                $ownerInfo = e($ownerName);
                if ($phone) { $ownerInfo .= '<br><span>' . e($phone) . '</span>'; }
                $rowCells[] = $ownerInfo;

                // Admin commission formatting
                $adminCell = '-';
                $raw = $row->admin_commission;
                if ($raw !== null && $raw !== '') {
                    $decoded = null;
                    if (is_string($raw)) {
                        $trim = trim($raw);
                        if ((str_starts_with($trim, '{') && str_ends_with($trim, '}')) || (str_starts_with($trim, '[') && str_ends_with($trim, ']'))) {
                            try { $decoded = json_decode($trim, true); } catch (\Throwable $e) { $decoded = null; }
                        }
                    }
                    if (is_array($decoded)) {
                        $val = isset($decoded['fix_commission']) ? (string)$decoded['fix_commission'] : '';
                        $type = isset($decoded['commissionType']) ? (string)$decoded['commissionType'] : '';
                        if ($val !== '') {
                            $adminCell = ($type === 'Percent') ? ($val . '%') : $val;
                        }
                    } else {
                        $adminCell = (string) $raw;
                    }
                }
                $rowCells[] = e($adminCell);

                // Date
                $rowCells[] = '<span class="dt-time">' . e($createdAtText) . '</span>';

                // Wallet history link
                $rowCells[] = '<a href="' . e($walletUrl) . '">' . e(trans('lang.wallet_history')) . '</a>';

                // Actions
                $actionHtml = '<span class="action-btn">';
                $actionHtml .= '<a href="' . e($foodsUrl) . '"><i class="mdi mdi-food" title="Foods"></i></a>';
                $actionHtml .= '<a href="' . e($ordersUrl) . '"><i class="mdi mdi-view-list" title="Orders"></i></a>';
                $actionHtml .= '<a href="' . e($viewUrl) . '"><i class="mdi mdi-eye" title="View"></i></a>';
                $actionHtml .= '<a href="' . e($editUrl) . '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>';
                if ($canDelete) {
                    $actionHtml .= '<a id="' . e($id) . '" name="vendor-delete" class="delete-btn" href="javascript:void(0)" title="Delete"><i class="mdi mdi-delete"></i></a>';
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

        // Regular page load: pass zones for filters
        $zones = \Illuminate\Support\Facades\DB::table('zone')
            ->where('publish', 1)
            ->orderBy('name', 'asc')
            ->get(['id','name']);
        return view('mart.index', compact('zones'));
    }

    public function create()
    {
        $zones = \Illuminate\Support\Facades\DB::table('zone')
            ->where('publish', 1)
            ->orderBy('name', 'asc')
            ->get(['id','name']);

        // Load mart vendors only (vType='mart' and role='vendor')
        $vendors = \Illuminate\Support\Facades\DB::table('users')
            ->select('id', 'firstName', 'lastName', 'vendorID', 'role', 'vType')
            ->where('role', '=', 'vendor')
            ->where('vType', '=', 'mart')
            ->whereIn('active', ['true', '1', 1, true])
            ->orderBy('firstName')
            ->get();

        // Load mart categories
        $categories = \Illuminate\Support\Facades\DB::table('mart_categories')
            ->where('publish', 1)
            ->orderBy('title', 'asc')
            ->get(['id', 'title']);

        return view('mart.create', compact('zones', 'vendors', 'categories'));
    }

    public function edit($id)
    {
        $mart = \Illuminate\Support\Facades\DB::table('vendors')->where('id', $id)->first();
        $zones = \Illuminate\Support\Facades\DB::table('zone')
            ->where('publish', 1)
            ->orderBy('name', 'asc')
            ->get(['id','name']);
        $owner = null;
        if ($mart && !empty($mart->author)) {
            $owner = \Illuminate\Support\Facades\DB::table('users')->select('id','firstName','lastName','email','phoneNumber')->where('id', $mart->author)->first();
        }
        return view('mart.edit', compact('id', 'mart', 'zones', 'owner'));
    }

    public function view($id)
    {
        return view('mart.view')->with('id', $id);
    }

    public function foods($id)
    {
        return view('martItems.index')->with('id', $id);
    }

    public function orders($id)
    {
        return view('orders.index')->with('id', $id);
    }

    // Lightweight JSON for a single mart (for edit/view pages)
    public function showJson(string $id)
    {
        $vendor = DB::table('vendors as v')
            ->leftJoin('users as u', 'u.id', '=', 'v.author')
            ->leftJoin('zone as z', 'z.id', '=', 'v.zoneId')
            ->where('v.id', $id)
            ->select(
                'v.*',
                DB::raw('z.name as zone_name'),
                DB::raw('u.firstName as owner_first'),
                DB::raw('u.lastName as owner_last'),
                DB::raw('u.email as owner_email'),
                DB::raw('u.phoneNumber as owner_phone')
            )
            ->first();
        if (!$vendor) {
            return response()->json(['success' => false, 'error' => 'Mart not found'], 404);
        }
        return response()->json(['success' => true, 'vendor' => $vendor]);
    }

    // ===== SQL-backed create/update for Mart =====
    public function store(Request $request)
    {
        $data = $this->normalizeVendorPayload($request);
        $id = $data['id'] ?: Str::uuid()->toString();
        $data['id'] = $id;
        $data['vType'] = 'mart';
        if (empty($data['createdAt'])) { $data['createdAt'] = Carbon::now()->toDateTimeString(); }

        try {
            Vendor::updateOrCreate(['id' => $id], $data);
        } catch (\Throwable $e) {
            \Log::error('Mart store failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        // Link vendorID to user if provided
        if (!empty($data['author'])) {
            DB::table('users')->where('id', $data['author'])->update(['vendorID' => $id]);
        }

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function update(Request $request, string $id)
    {
        $data = $this->normalizeVendorPayload($request);
        $data['vType'] = 'mart';
        try {
            Vendor::updateOrCreate(['id' => $id], array_merge($data, ['id' => $id]));
        } catch (\Throwable $e) {
            \Log::error('Mart update failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString(), 'data' => $data]);
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }

        if (!empty($data['author'])) {
            DB::table('users')->where('id', $data['author'])->update(['vendorID' => $id]);
        }

        return response()->json(['success' => true, 'id' => $id]);
    }

    private function normalizeVendorPayload(Request $request): array
    {
        // Accept JSON or form
        $payload = $request->all();
        $jsonKeys = ['categoryID','categoryTitle','photos','restaurantMenuPhotos','filters','specialDiscount','workingHours','adminCommission'];
        foreach ($jsonKeys as $k) {
            if (isset($payload[$k]) && is_string($payload[$k])) {
                $decoded = json_decode($payload[$k], true);
                if ($decoded !== null) { $payload[$k] = $decoded; }
            }
        }
        // Ensure arrays/strings per DB expectations
        $coerceArray = ['categoryID','categoryTitle','photos','restaurantMenuPhotos','specialDiscount','workingHours'];
        foreach ($coerceArray as $k) {
            if (isset($payload[$k]) && is_array($payload[$k])) {
                $payload[$k] = json_encode(array_values($payload[$k]));
            }
        }
        if (isset($payload['filters']) && is_array($payload['filters'])) {
            $payload['filters'] = json_encode($payload['filters']);
        }
        if (isset($payload['adminCommission']) && is_array($payload['adminCommission'])) {
            $payload['adminCommission'] = json_encode($payload['adminCommission']);
        }

        // Coerce createdAt
        $createdAt = $payload['createdAt'] ?? null;
        if ($createdAt) {
            if (is_string($createdAt)) {
                try { $createdAt = Carbon::parse(trim($createdAt, "\" "))->toDateTimeString(); } catch (\Throwable $e) { $createdAt = Carbon::now()->toDateTimeString(); }
            } else {
                $createdAt = Carbon::now()->toDateTimeString();
            }
        }

        return [
            'id' => $payload['id'] ?? null,
            'title' => $payload['title'] ?? '',
            'description' => $payload['description'] ?? '',
            'latitude' => isset($payload['latitude']) ? (float)$payload['latitude'] : null,
            'longitude' => isset($payload['longitude']) ? (float)$payload['longitude'] : null,
            'location' => $payload['location'] ?? '',
            'phonenumber' => $payload['phonenumber'] ?? null,
            'countryCode' => $payload['countryCode'] ?? null,
            'photo' => $payload['photo'] ?? null,
            'photos' => $payload['photos'] ?? json_encode([]),
            'restaurantMenuPhotos' => $payload['restaurantMenuPhotos'] ?? json_encode([]),
            'categoryID' => $payload['categoryID'] ?? json_encode([]),
            'categoryTitle' => $payload['categoryTitle'] ?? json_encode([]),
            'zoneId' => $payload['zoneId'] ?? null,
            'author' => $payload['author'] ?? null,
            'authorName' => $payload['authorName'] ?? null,
            'authorProfilePic' => $payload['authorProfilePic'] ?? null,
            'isOpen' => !empty($payload['isOpen']) ? 1 : 0,
            'enabledDelivery' => !empty($payload['enabledDelivery']) ? 1 : 0,
            'openDineTime' => $payload['openDineTime'] ?? null,
            'closeDineTime' => $payload['closeDineTime'] ?? null,
            'restaurantCost' => $payload['restaurantCost'] ?? 0,
            'filters' => $payload['filters'] ?? json_encode(new \stdClass()),
            'workingHours' => $payload['workingHours'] ?? json_encode([]),
            'specialDiscount' => $payload['specialDiscount'] ?? json_encode([]),
            'specialDiscountEnable' => !empty($payload['specialDiscountEnable']) ? 1 : 0,
            'adminCommission' => $payload['adminCommission'] ?? json_encode(['commissionType' => 'Percent', 'fix_commission' => 0, 'isEnabled' => true]),
            'createdAt' => $createdAt,
            'vType' => 'mart',
        ];
    }

    /**
     * Get published mart categories for dropdown (API endpoint)
     */
    public function getCategories()
    {
        try {
            $categories = DB::table('mart_categories')
                ->where('publish', 1)
                ->orderBy('title', 'asc')
                ->get(['id', 'title'])
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching mart categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mart vendors (users with vType='mart' and role='vendor') for API
     */
    public function getMartVendors()
    {
        try {
            $vendors = DB::table('users')
                ->where('role', 'vendor')
                ->where('vType', 'mart')
                ->whereIn('active', ['true', '1', 1, true])
                ->orderBy('firstName', 'asc')
                ->get(['id', 'firstName', 'lastName', 'vendorID'])
                ->map(function($vendor) {
                    return [
                        'id' => $vendor->id,
                        'name' => trim(($vendor->firstName ?? '') . ' ' . ($vendor->lastName ?? '')),
                        'firstName' => $vendor->firstName ?? '',
                        'lastName' => $vendor->lastName ?? '',
                        'vendorID' => $vendor->vendorID ?? null
                    ];
                })
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => $vendors
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching mart vendors: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

