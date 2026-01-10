<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\DynamicEmail;
use App\Models\AppUser;
use App\Models\Vendor;
use App\Models\VendorProduct;
use App\Models\SubscriptionPlan;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RestaurantController extends Controller
{
    /**
     * Normalize various truthy/falsy inputs to integer 1/0 for SQL columns.
     */
    protected function toBoolInt($value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_numeric($value)) {
            return ((int) $value) === 1 ? 1 : 0;
        }

        $stringValue = strtolower((string) $value);

        return in_array($stringValue, ['1', 'true', 'yes', 'on'], true) ? 1 : 0;
    }

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {

        return view("restaurants.index");
    }

    public function vendors()
    {
        return view("vendors.index");
    }


    public function edit($id)
    {
        return view('restaurants.edit')->with('id',$id);
    }

    public function vendorEdit($id)
    {
        return view('vendors.edit')->with('id',$id);
    }

    public function vendorSubscriptionPlanHistory($id='')
    {
        return view('subscription_plans.history')->with('id',$id);
    }

    public function subscriptionHistoryData(Request $request, $id = '')
    {
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $draw = (int) $request->input('draw', 1);
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));

        $q = DB::table('subscription_history');

        // Filter by user_id if provided
        if ($id !== '' && $id !== null) {
            $q->where('user_id', $id);
        }

        // 🔹 Business Model filter (Plan ID inside JSON)
        if ($request->has('business_model') && $request->business_model != '') {
            $q->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(subscription_plan, '$.id')) = ?",
                [$request->business_model]
            );
        }

         // 🔹 Zone filter
        if ($request->has('zone') && $request->zone != '') {
            $q->whereExists(function ($sub) use ($request) {
                $sub->select(DB::raw(1))
                    ->from('vendors')
                    ->whereColumn('vendors.author', 'subscription_history.user_id')
                    ->where('vendors.zoneId', $request->zone);
            });
        }

//        if ($search !== '') {
//            $q->where(function($query) use ($search) {
//                $query->whereRaw("LOWER(subscription_plan) LIKE ?", ['%'.$search.'%'])
//                    ->orWhere('user_id', 'like', '%'.$search.'%')
//                    ->orWhere('payment_type', 'like', '%'.$search.'%');
//            });
//        }

        if ($search !== '') {
            $q->where(function ($query) use ($search) {

                // 🔹 Search inside subscription_plan JSON
                $query->whereRaw("LOWER(subscription_plan) LIKE ?", ['%' . $search . '%'])
                    // 🔹 Search by user_id
                    ->orWhere('user_id', 'like', '%' . $search . '%')
                    // 🔹 Search by payment type
                    ->orWhere('payment_type', 'like', '%' . $search . '%')
                    // 🔹 Search by Vendor title (IMPORTANT)
                    ->orWhereExists(function ($sub) use ($search) {
                        $sub->select(DB::raw(1))
                            ->from('vendors')
                            ->whereColumn('vendors.author', 'subscription_history.user_id')
                            ->whereRaw('LOWER(vendors.title) LIKE ?', ['%' . $search . '%']);
                    });
            });
        }


        $total = (clone $q)->count();
        $rows = $q->orderBy('createdAt', 'desc')->offset($start)->limit($length)->get();

        $data = [];
        foreach ($rows as $r) {
            $row = [];

            // Parse subscription_plan JSON
            $planData = json_decode($r->subscription_plan, true);
            if (!$planData) {
                $planData = [];
            }

            // Checkbox
            $row[] = '<input type="checkbox" class="is_open" dataId="'.$r->id.'">';

            // Vendor name (if not filtering by specific vendor)
//            if ($id == '' || $id == null) {
//                $vendor = DB::table('vendors')->where('id', $r->user_id)->first();
//                if ($vendor) {
//                    // Try different possible column names for vendor name
//                    $vendorName = $vendor->title ?? $vendor->name ?? $vendor->restaurant_name ?? 'Unknown Vendor';
//                } else {
//                    $vendorName = 'Unknown Vendor';
//                }
//
//                // Format as clickable link (HTML will be rendered by DataTables)
//                $vendorLink = '<a href="'.route('restaurants.view', $r->user_id).'">' . htmlspecialchars($vendorName, ENT_QUOTES, 'UTF-8') . '</a>';
//                $row[] = $vendorLink;
//            }

            if ($id == '' || $id == null) {

                $vendor = DB::table('vendors')
                    ->where('author', $r->user_id)
                    ->first();

                if ($vendor) {
                    $vendorName = $vendor->title
                        ?? $vendor->name
                        ?? $vendor->restaurant_name
                        ?? 'Unknown Vendor';

                    $vendorLink = '<a href="' . route('restaurants.view', $vendor->id) . '">'
                        . e($vendorName) .
                        '</a>';
                } else {
                    $vendorLink = 'Unknown Vendor';
                }

                $row[] = $vendorLink;

            }


            // Plan name
            $planName = $planData['name'] ?? 'N/A';
            $row[] = e($planName);

            // Plan type
            $planType = $planData['type'] ?? 'paid';
            $row[] = ucfirst($planType);

            // ✅ ADD THIS ZONE LOGIC HERE
            $zoneName = 'N/A';
            if ($vendor && !empty($vendor->zoneId)) {
                $zone = DB::table('zone')->where('id', $vendor->zoneId)->first();
                if ($zone) {
                    $zoneName = $zone->name;
                }
            }
            $row[] = e($zoneName);

            // Expires at (using expiry_date column)
            $expiryDate = $r->expiry_date ?? 'N/A';
            if ($expiryDate && $expiryDate != 'N/A') {
                try {
                    $row[] = date('Y-m-d', strtotime($expiryDate));
                } catch (\Exception $e) {
                    $row[] = $expiryDate;
                }
            } else {
                $row[] = 'N/A';
            }

            // Purchase date (using createdAt column)
            $purchaseDate = $r->createdAt ?? 'N/A';
            if ($purchaseDate && $purchaseDate != 'N/A') {
                try {
                    $row[] = date('Y-m-d H:i:s', strtotime($purchaseDate));
                } catch (\Exception $e) {
                    $row[] = $purchaseDate;
                }
            } else {
                $row[] = 'N/A';
            }

            $data[] = $row;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    public function view($id)
    {
        return view('restaurants.view')->with('id',$id);
    }

    public function plan($id)
    {

        return view("restaurants.plan")->with('id',$id);
    }

    public function payout($id)
    {
        return view('restaurants.payout')->with('id',$id);
    }

    public function foods($id)
    {
        return view('restaurants.foods')->with('id',$id);
    }

    public function orders($id)
    {
        return view('restaurants.orders')->with('id',$id);
    }

    public function reviews($id)
    {
        return view('restaurants.reviews')->with('id',$id);
    }

    public function promos($id)
    {
        return view('restaurants.promos')->with('id',$id);
    }

    public function vendorCreate(){
        return view('vendors.create');
    }

    public function create(){
        return view('restaurants.create');
    }

    public function DocumentList($id){
        return view("vendors.document_list")->with('id',$id);
    }

    public function DocumentUpload($vendorId, $id)
    {
        return view("vendors.document_upload", compact('vendorId', 'id'));
    }
    public function currentSubscriberList($id)
    {
        return view("subscription_plans.current_subscriber", compact( 'id'));
    }

    public function importVendors(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $spreadsheet = IOFactory::load($request->file('file'));
        $sheet = $spreadsheet->getSheetByName('Restaurants');
        if (!$sheet) {
            $sheet = $spreadsheet->getSheet(0);
        }
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file is empty or missing data.']);
        }

        $headerRow = array_shift($rows);
        $headers = array_map('trim', array_values($headerRow));
        $imported = 0;
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $rowIndex => $row) {
                $rowNum = $rowIndex + 2; // Excel row number
                $data = array_combine($headers, $row);

                // Required fields
                if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email']) || empty($data['password'])) {
                    $errors[] = "Row $rowNum: Missing required fields.";
                    continue;
                }

                // Email format
                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Row $rowNum: Invalid email format.";
                    continue;
                }

                // Duplicate email check
                if (AppUser::where('email', $data['email'])->exists()) {
                    $errors[] = "Row $rowNum: Email already exists.";
                    continue;
                }

                // Phone number validation
                if (!empty($data['phoneNumber']) && !preg_match('/^[+0-9\\- ]{7,20}$/', $data['phoneNumber'])) {
                    $errors[] = "Row $rowNum: Invalid phone number format.";
                    continue;
                }

                // Create vendor user
                $vendor = new AppUser();
                $vendor->firstName = $data['firstName'];
                $vendor->lastName = $data['lastName'];
                $vendor->email = $data['email'];
                $vendor->password = Hash::make($data['password']);
                $vendor->phoneNumber = $data['phoneNumber'] ?? null;
                $vendor->countryCode = $data['countryCode'] ?? '';
                $vendor->role = 'vendor';
                $vendor->vType = $data['vType'] ?? 'restaurant';
                $vendor->active = strtolower($data['active'] ?? '') === 'true' ? 1 : 0;
                $vendor->profilePictureURL = $data['profilePictureURL'] ?? '';
                $vendor->provider = 'email';
                $vendor->appIdentifier = 'web';
                $vendor->isDocumentVerify = 0;
                $vendor->createdAt = !empty($data['createdAt'])
                    ? '"' . Carbon::parse($data['createdAt'])->utc()->format('Y-m-d\TH:i:s.u\Z') . '"'
                    : '"' . gmdate('Y-m-d\TH:i:s.u\Z') . '"';
                $vendor->firebase_id = uniqid();
                $vendor->_id = $vendor->firebase_id;

                // Optional zoneId column
                if (!empty($data['zoneId'])) {
                    $vendor->zoneId = $data['zoneId'];
                }

                $vendor->save();
                $imported++;
            }

            DB::commit();

            $msg = "Vendors imported successfully! ($imported rows)";
            if (!empty($errors)) {
                $msg .= "<br>Some issues occurred:<br>" . implode('<br>', $errors);
            }

            if ($imported === 0) {
                return back()->withErrors(['file' => $msg]);
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['file' => 'An error occurred while importing: ' . $e->getMessage()]);
        }
    }

    public function downloadVendorsTemplate()
    {
        $filePath = storage_path('app/templates/vendors_import_template.xlsx');
        $templateDir = dirname($filePath);

        // Create template directory if it doesn't exist
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateVendorsTemplate($filePath);
        }

        return response()->download($filePath, 'vendors_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="vendors_import_template.xlsx"'
        ]);
    }

    /**
     * Generate Excel template for vendors import
     */
    private function generateVendorsTemplate($filePath)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Restaurants');

            // Set headers
            $headers = [
                'A1' => 'firstName',
                'B1' => 'lastName',
                'C1' => 'email',
                'D1' => 'password',
                'E1' => 'phoneNumber',
                'F1' => 'countryCode',
                'G1' => 'profilePictureURL',
                'H1' => 'zoneId',
                'I1' => 'active'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }

            // Add sample data
            $sampleData = [
                'Restaurant',
                'Owner',
                'owner@restaurant.com',
                'password123',
                '1234567890',
                '+1',
                'https://example.com/profile.jpg',
                'zone_id_123',
                'true'
            ];

            $sheet->fromArray([$sampleData], null, 'A2');

            // Auto-size columns
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

        } catch (\Exception $e) {
            \Log::error('Failed to generate vendors template: ' . $e->getMessage());
            abort(500, 'Failed to generate template');
        }
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $skipInvalidRows = filter_var($request->input('skip_invalid_rows', false), FILTER_VALIDATE_BOOLEAN);

        $spreadsheet = IOFactory::load($request->file('file'));
        $sheet = $spreadsheet->getSheetByName('Restaurants');
        if (!$sheet) {
            $sheet = $spreadsheet->getActiveSheet();
        }
        $rows = $sheet->toArray(null, true, true, false);
        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file is empty or missing data.']);
        }

//        $rawHeaderRow = array_shift($rows);
        // Find the real header row dynamically
        $rawHeaderRow = null;
        while (!empty($rows)) {
            $candidate = array_shift($rows);
            $candidateLower = array_map('strtolower', array_map('trim', $candidate));

            // Detect if this row contains the required headers
            if (in_array('title', $candidateLower) && in_array('description', $candidateLower)) {
                $rawHeaderRow = $candidate;
                break;
            }
        }

        if (!$rawHeaderRow) {
            return back()->withErrors(['file' => 'Could not detect header row. Please use the correct template.']);
        }

        $columnHeaders = [];
        $headersLower = [];
        foreach ($rawHeaderRow as $index => $value) {
            $headerValue = '';
            if (is_string($value)) {
                $headerValue = trim($value);
            } elseif (!is_null($value)) {
                $headerValue = trim((string) $value);
            }

            $columnHeaders[$index] = $headerValue;
            if ($headerValue !== '') {
                $headersLower[] = strtolower($headerValue);
            }
        }

        $headersLower = array_unique($headersLower);

//        $requiredHeaders = ['title', 'description', 'latitude', 'longitude', 'location', 'phonenumber', 'countrycode'];
//        $missingHeaders = array_diff(array_map('strtolower', $requiredHeaders), $headersLower);
        // Make header validation fully case-insensitive and trim-safe
        $requiredHeaders = ['title', 'description', 'latitude', 'longitude', 'location', 'phonenumber', 'countrycode'];

        $headersLower = array_map(fn($h) => strtolower(trim($h)), $headersLower);
        $missingHeaders = array_diff($requiredHeaders, $headersLower);

        if (!empty($missingHeaders)) {
            return back()->withErrors(['file' => 'Missing required columns: ' . implode(', ', $missingHeaders) .
                '. Please use the template provided by the "Download Template" button.']);
        }

        $batchSize = 50;
        $batches = array_chunk($rows, $batchSize);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $skippedRows = 0;
        $errorMessages = [];

        $lookupData = $this->preloadLookupData();

        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $rowIndex => $row) {
                $globalRowIndex = $batchIndex * $batchSize + $rowIndex;
                $rowNum = $globalRowIndex + 2;

                if ($this->isEmptyRow($row)) {
                    continue;
                }

                $data = [];
                foreach ($columnHeaders as $idx => $headerName) {
                    if ($headerName === '') {
                        continue;
                    }
                    $data[$headerName] = $row[$idx] ?? null;
                }

                try {
                    $result = $this->processRestaurantRow($data, $rowNum, $lookupData, $skipInvalidRows);
                    if ($result['success'] ?? false) {
                        if (($result['action'] ?? '') === 'created') {
                            $created++;
                        } else {
                            $updated++;
                        }
                    } else {
                        if (($result['action'] ?? '') === 'skipped') {
                            $skippedRows++;
                        } else {
                            $failed++;
                            if (!empty($result['error'])) {
                                $errorMessages[] = $result['error'];
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $errorMessages[] = "Row $rowNum: {$e->getMessage()}";
                    \Log::error('Restaurant bulk update error', [
                        'row' => $rowNum,
                        'exception' => $e->getMessage(),
                    ]);
                }
            }
        }

        $msg = "Restaurant created: $created, updated: $updated, failed: $failed";
        if ($skippedRows > 0) {
            $msg .= ", skipped: $skippedRows";
        }

        if (!empty($errorMessages)) {
            $msg .= "<br>" . implode('<br>', array_unique($errorMessages));
        }

        if ($failed > 0 && $created === 0 && $updated === 0) {
            return back()->withErrors(['file' => $msg]);
        }

        if ($failed > 0) {
            return back()->withErrors(['file' => $msg]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Preload lookup data to avoid repeated queries
     */
    private function preloadLookupData(): array
    {
        $lookupData = [
            'users' => [
                'records' => [],
                'email_index' => [],
                'name_index' => [],
            ],
            'categories' => [],
            'cuisines' => [],
            'zones' => [
                'by_name' => [],
                'by_id' => [],
            ],
            'existing_restaurants' => [],
        ];

        // Users (limit to avoid huge memory usage)
        AppUser::select('id', 'firstName', 'lastName', 'email')
            ->where('role', 'vendor')
            ->limit(2000)
            ->get()
            ->each(function ($user) use (&$lookupData) {
                $lookupData['users']['records'][$user->id] = $user;

                if (!empty($user->email)) {
                    $lookupData['users']['email_index'][strtolower(trim($user->email))] = $user->id;
                }

                $fullName = trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? ''));
                if ($fullName !== '') {
                    $lookupData['users']['name_index'][strtolower($fullName)] = $user->id;
                }
            });

        // Categories
        DB::table('vendor_categories')
            ->select('id', 'title')
            ->get()
            ->each(function ($category) use (&$lookupData) {
                if (!empty($category->title)) {
                    $lookupData['categories'][strtolower(trim($category->title))] = (string)$category->id;
                }
            });

        // Cuisines
        DB::table('vendor_cuisines')
            ->select('id', 'title')
            ->get()
            ->each(function ($cuisine) use (&$lookupData) {
                if (!empty($cuisine->title)) {
                    $lookupData['cuisines'][strtolower(trim($cuisine->title))] = (string)$cuisine->id;
                }
            });

        // Zones
        DB::table('zone')
            ->select('id', 'name')
            ->where('publish', 1)
            ->get()
            ->each(function ($zone) use (&$lookupData) {
                if (!empty($zone->name)) {
                    $lookupData['zones']['by_name'][strtolower(trim($zone->name))] = (string)$zone->id;
                }
                $lookupData['zones']['by_id'][(string)$zone->id] = [
                    'id' => (string)$zone->id,
                    'name' => $zone->name,
                ];
            });

        // Existing restaurants (for duplicate detection)
        Vendor::select('id', 'title', 'location')
            ->orderBy('createdAt', 'desc')
            ->limit(5000)
            ->get()
            ->each(function ($restaurant) use (&$lookupData) {
                if (!empty($restaurant->title) && !empty($restaurant->location)) {
                    $key = strtolower(trim($restaurant->title)) . '|' . strtolower(trim($restaurant->location));
                    $lookupData['existing_restaurants'][$key] = $restaurant->id;
                }
            });

        return $lookupData;
    }

    /**
     * Process a single restaurant row with optimized lookups
     */
    private function processRestaurantRow(array $data, int $rowNum, array &$lookupData, bool $skipInvalidRows = false): array
    {
        $validationErrors = $this->validateRestaurantData($data, $rowNum);
        if (!empty($validationErrors)) {
            if ($skipInvalidRows) {
                return [
                    'success' => false,
                    'action' => 'skipped',
                    'error' => implode('; ', $validationErrors),
                ];
            }

            return [
                'success' => false,
                'error' => implode('; ', $validationErrors),
            ];
        }

        // Duplicate detection (only when creating new records)
        $duplicateCheck = $this->checkDuplicateRestaurant($data, $lookupData, $rowNum);
        if ($duplicateCheck['isDuplicate']) {
            if (!empty($data['id']) && $duplicateCheck['existingId'] === $data['id']) {
                // Updating the same restaurant is allowed
            } else {
                return [
                    'success' => false,
                    'error' => $duplicateCheck['error'],
                ];
            }
        }

        // Resolve author
        if (empty($data['author'])) {
            $authorId = $this->resolveAuthorId($data, $lookupData);
            if ($authorId === false && (!empty($data['authorName']) || !empty($data['authorEmail']))) {
                return [
                    'success' => false,
                    'error' => "Row $rowNum: author lookup failed for authorName '{$data['authorName']}' or authorEmail '{$data['authorEmail']}'.",
                ];
            }

            if ($authorId) {
                $data['author'] = $authorId;
            }
        }

        // Category lookup by title
        if (!empty($data['categoryTitle']) && empty($data['categoryID'])) {
            $titles = json_decode($data['categoryTitle'], true);
            if (!is_array($titles)) {
                $titles = explode(',', $data['categoryTitle']);
            }
            $categoryIds = [];
            foreach ($titles as $title) {
                $titleLower = strtolower(trim($title));
                if (isset($lookupData['categories'][$titleLower])) {
                    $categoryIds[] = $lookupData['categories'][$titleLower];
                } else {
                    $found = $this->fuzzyCategoryLookup($title, $lookupData['categories']);
                    if ($found) {
                        $categoryIds[] = $found;
                    } else {
                        return [
                            'success' => false,
                            'error' => "Row $rowNum: categoryTitle '$title' not found in vendor_categories.",
                        ];
                    }
                }
            }
            $data['categoryID'] = $categoryIds;
        }

        // Zone lookup
        if (!empty($data['zoneName']) && empty($data['zoneId'])) {
            $zoneNameLower = strtolower(trim($data['zoneName']));
            if (isset($lookupData['zones']['by_name'][$zoneNameLower])) {
                $data['zoneId'] = $lookupData['zones']['by_name'][$zoneNameLower];
            } else {
                $found = $this->fuzzyZoneLookup($data['zoneName'], $lookupData['zones']['by_name']);
                if ($found) {
                    $data['zoneId'] = $found;
                } else {
                    return [
                        'success' => false,
                        'error' => "Row $rowNum: zoneName '{$data['zoneName']}' not found in zones table.",
                    ];
                }
            }
        }

        if (!empty($data['zoneId']) && !isset($lookupData['zones']['by_id'][(string)$data['zoneId']])) {
            // Maybe the value is a zone name instead of ID
            $zoneNameLower = strtolower(trim($data['zoneId']));
            if (isset($lookupData['zones']['by_name'][$zoneNameLower])) {
                $data['zoneId'] = $lookupData['zones']['by_name'][$zoneNameLower];
            } else {
                return [
                    'success' => false,
                    'error' => "Row $rowNum: zoneId '{$data['zoneId']}' not found in zones table.",
                ];
            }
        }

        // Cuisine lookup
        if (!empty($data['vendorCuisineTitle']) && empty($data['vendorCuisineID'])) {
            $titleLower = strtolower(trim($data['vendorCuisineTitle']));
            if (isset($lookupData['cuisines'][$titleLower])) {
                $data['vendorCuisineID'] = $lookupData['cuisines'][$titleLower];
            } else {
                $found = $this->fuzzyCuisineLookup($data['vendorCuisineTitle'], $lookupData['cuisines']);
                if ($found) {
                    $data['vendorCuisineID'] = $found;
                } else {
                    return [
                        'success' => false,
                        'error' => "Row $rowNum: vendorCuisineTitle '{$data['vendorCuisineTitle']}' not found in vendor_cuisines.",
                    ];
                }
            }
        }

        if (!empty($data['vendorCuisineID']) && !in_array($data['vendorCuisineID'], array_values($lookupData['cuisines']), true)) {
            $providedValue = strtolower(trim($data['vendorCuisineID']));
            if (isset($lookupData['cuisines'][$providedValue])) {
                $data['vendorCuisineID'] = $lookupData['cuisines'][$providedValue];
            } else {
                return [
                    'success' => false,
                    'error' => "Row $rowNum: vendorCuisineID '{$data['vendorCuisineID']}' not found in vendor_cuisines table.",
                ];
            }
        }

        $data = $this->processDataTypes($data);

        $attributes = $this->prepareVendorAttributes($data);

        if (!empty($data['id'])) {
            $vendor = Vendor::find($data['id']);
            if (!$vendor) {
                return [
                    'success' => false,
                    'error' => "Row $rowNum: Restaurant with ID {$data['id']} not found.",
                ];
            }

            $updateData = array_filter($attributes, function ($value) {
                return $value !== null && $value !== '';
            });

            foreach ($updateData as $key => $value) {
                $vendor->{$key} = $value;
            }

            $vendor->save();
            $this->updateDuplicateLookup($lookupData, $vendor);

            return [
                'success' => true,
                'action' => 'updated',
            ];
        }

        // Create new restaurant
        do {
            $newId = 'rest_' . Str::uuid()->toString();
        } while (Vendor::where('id', $newId)->exists());

        $vendor = new Vendor();
        $vendor->id = $newId;

        foreach ($attributes as $key => $value) {
            $vendor->{$key} = $value;
        }

        if (empty($vendor->createdAt)) {
            $vendor->createdAt = '"' . gmdate('Y-m-d\TH:i:s.u\Z') . '"';
        }

        $vendor->save();
        $this->updateDuplicateLookup($lookupData, $vendor);

        return [
            'success' => true,
            'action' => 'created',
        ];
    }

    private function resolveAuthorId(array $data, array $lookupData)
    {
        if (empty($lookupData['users']['records'])) {
            return false;
        }

        if (!empty($data['authorEmail'])) {
            $emailKey = strtolower(trim($data['authorEmail']));
            if (isset($lookupData['users']['email_index'][$emailKey])) {
                return $lookupData['users']['email_index'][$emailKey];
            }
        }

        if (!empty($data['authorName'])) {
            $nameKey = strtolower(trim($data['authorName']));
            if (isset($lookupData['users']['name_index'][$nameKey])) {
                return $lookupData['users']['name_index'][$nameKey];
            }

            $fuzzy = $this->fuzzyAuthorLookup($data, $lookupData);
            if ($fuzzy) {
                return $fuzzy;
            }
        }

        return false;
    }

    private function prepareVendorAttributes(array $data): array
    {
        $attributes = [];

        $simpleFields = [
            'title', 'description', 'location', 'phonenumber', 'countryCode', 'zoneId',
            'author', 'authorName', 'authorProfilePic', 'vendorCuisineID', 'restaurantCost',
            'openDineTime', 'closeDineTime', 'photo', 'vType', 'walletAmount',
            'subscriptionPlanId', 'subscriptionExpiryDate', 'subscriptionTotalOrders',
            'dine_in_active', 'createdAt',
        ];

        foreach ($simpleFields as $field) {
            if (array_key_exists($field, $data)) {
                $attributes[$field] = $data[$field];
            }
        }

        if (array_key_exists('latitude', $data)) {
            $attributes['latitude'] = $data['latitude'] !== '' ? (float)$data['latitude'] : null;
        }

        if (array_key_exists('longitude', $data)) {
            $attributes['longitude'] = $data['longitude'] !== '' ? (float)$data['longitude'] : null;
        }

        $booleanFields = [
            'isOpen', 'reststatus', 'specialDiscountEnable', 'enabledDiveInFuture', 'hidephotos',
        ];

        foreach ($booleanFields as $field) {
            if (array_key_exists($field, $data)) {
                $attributes[$field] = $this->toBoolInt($data[$field]);
            }
        }

        if (!array_key_exists('reststatus', $attributes) && array_key_exists('isOpen', $attributes)) {
            $attributes['reststatus'] = $attributes['isOpen'];
        }

        $jsonFieldMap = [
            'categoryID' => function ($value) {
                return json_encode(array_values($value));
            },
            'categoryTitle' => function ($value) {
                return json_encode(array_values($value));
            },
            'photos' => function ($value) {
                return json_encode(array_values($value));
            },
            'restaurantMenuPhotos' => function ($value) {
                return json_encode(array_values($value));
            },
            'filters' => function ($value) {
                return json_encode($value);
            },
            'workingHours' => function ($value) {
                return json_encode($value);
            },
            'adminCommission' => function ($value) {
                return json_encode($value);
            },
            'specialDiscount' => function ($value) {
                return json_encode($value);
            },
            'subscription_plan' => function ($value) {
                return json_encode($value);
            },
        ];

        foreach ($jsonFieldMap as $field => $encoder) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                    $attributes[$field] = null;
                } elseif (is_string($value)) {
                    $decoded = json_decode($value, true);
                    $attributes[$field] = $encoder(is_array($decoded) ? $decoded : [$value]);
                } else {
                    $attributes[$field] = $encoder($value);
                }
            }
        }

        return $attributes;
    }

    private function updateDuplicateLookup(array &$lookupData, Vendor $vendor): void
    {
        if (!empty($vendor->title) && !empty($vendor->location)) {
            $key = strtolower(trim($vendor->title)) . '|' . strtolower(trim($vendor->location));
            $lookupData['existing_restaurants'][$key] = $vendor->id;
        }
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($value === null) {
                continue;
            }

            if (is_string($value)) {
                if (trim($value) !== '') {
                    return false;
                }
            } elseif (!empty($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate restaurant data before processing
     */
    private function validateRestaurantData($data, $rowNum)
    {
        $errors = [];

        // Clean and trim data first
        $data = array_map(function($value) {
            return is_string($value) ? trim($value) : $value;
        }, $data);

        // Required field validation - collect all missing fields for this row
        $requiredFields = ['title', 'description', 'latitude', 'longitude', 'location', 'phonenumber', 'countryCode'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missingFields[] = $field;
            }
        }

        // If there are missing required fields, create a simple error message
        if (!empty($missingFields)) {
            $errors[] = "Row $rowNum: Missing required fields: " . implode(', ', $missingFields);
            return $errors; // Return early since missing required fields is a critical error
        }

        // Email validation
        if (!empty($data['authorEmail']) && !filter_var($data['authorEmail'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Row $rowNum: Invalid email format for authorEmail";
        }

        // Phone number validation
        if (!empty($data['phonenumber']) && !preg_match('/^[+0-9\- ]{7,20}$/', $data['phonenumber'])) {
            $errors[] = "Row $rowNum: Invalid phone number format (should be 7-20 digits, can include +, -, spaces)";
        }

        // URL validation for photo
        if (!empty($data['photo']) && !filter_var($data['photo'], FILTER_VALIDATE_URL)) {
            $errors[] = "Row $rowNum: Invalid photo URL format";
        }

        // Coordinate validation
        if (!empty($data['latitude'])) {
            $lat = (float)$data['latitude'];
            if ($lat < -90 || $lat > 90) {
                $errors[] = "Row $rowNum: Latitude must be between -90 and 90 degrees";
            }
        }

        if (!empty($data['longitude'])) {
            $lng = (float)$data['longitude'];
            if ($lng < -180 || $lng > 180) {
                $errors[] = "Row $rowNum: Longitude must be between -180 and 180 degrees";
            }
        }

        // Boolean field validation
        $booleanFields = ['isOpen', 'enabledDiveInFuture', 'hidephotos', 'specialDiscountEnable'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                $value = strtolower($data[$field]);
                if (!in_array($value, ['true', 'false', '1', '0', 'yes', 'no'])) {
                    $errors[] = "Row $rowNum: Invalid boolean value for '$field' (use true/false, 1/0, yes/no)";
                }
            }
        }

        // Numeric field validation
        $numericFields = ['restaurantCost'];
        foreach ($numericFields as $field) {
            if (!empty($data[$field]) && !is_numeric($data[$field])) {
                $errors[] = "Row $rowNum: Invalid numeric value for '$field'";
            }
        }

        // Time format validation
        if (!empty($data['openDineTime']) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['openDineTime'])) {
            $errors[] = "Row $rowNum: Invalid time format for openDineTime (use HH:MM format)";
        }

        if (!empty($data['closeDineTime']) && !preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $data['closeDineTime'])) {
            $errors[] = "Row $rowNum: Invalid time format for closeDineTime (use HH:MM format)";
        }

        return $errors;
    }

    /**
     * Provide helpful guidance for common validation errors
     */
    private function getValidationGuidance($missingFields)
    {
        $guidance = [];

        foreach ($missingFields as $field) {
            switch ($field) {
                case 'title':
                    $guidance[] = "Restaurant name is required";
                    break;
                case 'description':
                    $guidance[] = "Restaurant description is required";
                    break;
                case 'latitude':
                    $guidance[] = "Latitude coordinate is required (use Google Maps to get coordinates)";
                    break;
                case 'longitude':
                    $guidance[] = "Longitude coordinate is required (use Google Maps to get coordinates)";
                    break;
                case 'location':
                    $guidance[] = "Full address is required";
                    break;
                case 'phonenumber':
                    $guidance[] = "Phone number is required (7-20 digits, can include +, -, spaces)";
                    break;
                case 'countryCode':
                    $guidance[] = "Country code is required (e.g., IN for India, US for United States)";
                    break;
            }
        }

        return $guidance;
    }

    /**
     * Check for duplicate restaurants
     */
    private function checkDuplicateRestaurant($data, $lookupData, $rowNum)
    {
        if (empty($data['title']) || empty($data['location'])) {
            return ['isDuplicate' => false];
        }

        $key = strtolower(trim($data['title'])) . '|' . strtolower(trim($data['location']));

        if (isset($lookupData['existing_restaurants'][$key])) {
            return [
                'isDuplicate' => true,
                'existingId' => $lookupData['existing_restaurants'][$key],
                'error' => "Row $rowNum: Restaurant with title '{$data['title']}' and location '{$data['location']}' already exists (ID: {$lookupData['existing_restaurants'][$key]})"
            ];
        }

        return ['isDuplicate' => false];
    }


    /**
     * Optimized fuzzy author lookup
     */
    private function fuzzyAuthorLookup(array $data, array $lookupData)
    {
        $searchTerm = strtolower(trim($data['authorName'] ?? ''));
        if ($searchTerm === '' || empty($lookupData['users']['records'])) {
            return false;
        }

        foreach ($lookupData['users']['records'] as $user) {
            $fullName = strtolower(trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')));
            if ($fullName === '') {
                continue;
            }

            if (str_contains($fullName, $searchTerm) || str_contains($searchTerm, $fullName)) {
                return $user->id;
            }
        }

        return false;
    }

    /**
     * Optimized fuzzy category lookup
     */
    private function fuzzyCategoryLookup($title, $categories)
    {
        $titleLower = strtolower(trim($title));
        foreach ($categories as $catTitle => $catId) {
            if (stripos($catTitle, $titleLower) !== false) {
                return $catId;
            }
        }
        return false;
    }

    /**
     * Optimized fuzzy cuisine lookup
     */
    private function fuzzyCuisineLookup($title, $cuisines)
    {
        $titleLower = strtolower(trim($title));
        foreach ($cuisines as $cuisineTitle => $cuisineId) {
            if (stripos($cuisineTitle, $titleLower) !== false) {
                return $cuisineId;
            }
        }
        return false;
    }

    /**
     * Optimized fuzzy zone lookup
     */
    private function fuzzyZoneLookup($zoneName, $zones)
    {
        $zoneNameLower = strtolower(trim($zoneName));
        foreach ($zones as $zoneTitle => $zoneId) {
            if (stripos($zoneTitle, $zoneNameLower) !== false) {
                return $zoneId;
            }
        }
        return false;
    }

    /**
     * Process data type conversions
     */
    private function processDataTypes($data)
    {
        if (isset($data['categoryID'])) {
            if (is_string($data['categoryID'])) {
                $decoded = json_decode($data['categoryID'], true);
                $data['categoryID'] = is_array($decoded) ? $decoded : explode(',', $data['categoryID']);
            }
            if (!is_array($data['categoryID'])) {
                $data['categoryID'] = [$data['categoryID']];
            }
            $data['categoryID'] = array_values(array_filter(array_map('trim', $data['categoryID']), fn($val) => $val !== ''));
        }

        if (isset($data['categoryTitle'])) {
            if (is_string($data['categoryTitle'])) {
                $decoded = json_decode($data['categoryTitle'], true);
                $data['categoryTitle'] = is_array($decoded) ? $decoded : explode(',', $data['categoryTitle']);
            }
            if (!is_array($data['categoryTitle'])) {
                $data['categoryTitle'] = [$data['categoryTitle']];
            }
            $data['categoryTitle'] = array_values(array_filter(array_map('trim', $data['categoryTitle']), fn($val) => $val !== ''));
        }

        if (isset($data['adminCommission'])) {
            if (is_string($data['adminCommission'])) {
                $decoded = json_decode($data['adminCommission'], true);
                if (is_array($decoded)) {
                    $data['adminCommission'] = $decoded;
                } elseif (is_numeric($data['adminCommission'])) {
                    $data['adminCommission'] = [
                        'commissionType' => 'Percent',
                        'fix_commission' => (float)$data['adminCommission'],
                        'isEnabled' => true,
                    ];
                } else {
                    $data['adminCommission'] = [
                        'commissionType' => 'Percent',
                        'fix_commission' => 10,
                        'isEnabled' => true,
                    ];
                }
            }

            if (!isset($data['adminCommission']['commissionType'])) {
                $data['adminCommission']['commissionType'] = 'Percent';
            }
            if (!isset($data['adminCommission']['fix_commission'])) {
                $data['adminCommission']['fix_commission'] = 10;
            }
            if (!isset($data['adminCommission']['isEnabled'])) {
                $data['adminCommission']['isEnabled'] = true;
            }
        }

        $booleanFields = ['isOpen', 'enabledDiveInFuture', 'hidephotos', 'specialDiscountEnable', 'reststatus'];
        foreach ($booleanFields as $field) {
            if (isset($data[$field])) {
                if (is_string($data[$field])) {
                    $value = strtolower($data[$field]);
                    if (in_array($value, ['true', '1', 'yes', 'on'], true)) {
                        $data[$field] = true;
                    } elseif (in_array($value, ['false', '0', 'no', 'off'], true)) {
                        $data[$field] = false;
                    }
                }
                $data[$field] = (bool)$data[$field];
            }
        }

        $numericFields = ['latitude', 'longitude', 'restaurantCost'];
        foreach ($numericFields as $field) {
            if (isset($data[$field]) && $data[$field] !== '' && is_numeric($data[$field])) {
                $data[$field] = (float)$data[$field];
            }
        }

        $jsonArrayFields = ['photos', 'restaurantMenuPhotos', 'filters', 'workingHours', 'specialDiscount'];
        foreach ($jsonArrayFields as $field) {
            if (isset($data[$field])) {
                if (is_string($data[$field])) {
                    $decoded = json_decode($data[$field], true);
                    $data[$field] = is_array($decoded) ? $decoded : [];
                } elseif (!is_array($data[$field])) {
                    $data[$field] = [];
                }
            }
        }

        if (!isset($data['filters'])) {
            $data['filters'] = [
                'Free Wi-Fi' => 'No',
                'Good for Breakfast' => 'No',
                'Good for Dinner' => 'No',
                'Good for Lunch' => 'No',
                'Live Music' => 'No',
                'Outdoor Seating' => 'No',
                'Takes Reservations' => 'No',
                'Vegetarian Friendly' => 'No'
            ];
        }

        if (!isset($data['workingHours'])) {
            $defaultSlots = [];
            foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $day) {
                $defaultSlots[] = [
                    'day' => $day,
                    'timeslot' => [
                        [
                            'from' => '09:30',
                            'to' => '22:00'
                        ]
                    ]
                ];
            }
            $data['workingHours'] = $defaultSlots;
        }

        if (!isset($data['photos'])) {
            $data['photos'] = [];
        }

        if (!isset($data['restaurantMenuPhotos'])) {
            $data['restaurantMenuPhotos'] = [];
        }

        if (!isset($data['specialDiscount'])) {
            $data['specialDiscount'] = [];
        }

        return $data;
    }

    public function downloadBulkUpdateTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = [
                'id',
                'title',
                'description',
                'latitude',
                'longitude',
                'location',
                'phonenumber',
                'countryCode',
                'zoneName',
                'authorName',
                'authorEmail',
                'categoryTitle',
                'vendorCuisineTitle',
                'adminCommission',
                'isOpen',
                'enabledDiveInFuture',
                'restaurantCost',
                'openDineTime',
                'closeDineTime',
                'photo',
                'hidephotos',
                'specialDiscountEnable'
            ];

            foreach ($headers as $colIndex => $header) {
                $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
                $sheet->setCellValue($column . '1', $header);
                $sheet->getStyle($column . '1')->getFont()->setBold(true);
                $sheet->getStyle($column . '1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $sheet->getStyle($column . '1')->getFill()->getStartColor()->setRGB('E6E6FA');
            }

            $zones = DB::table('zone')
                ->where('publish', 1)
                ->orderBy('name')
                ->pluck('name')
                ->filter()
                ->values()
                ->all();

            $cuisines = DB::table('vendor_cuisines')
                ->orderBy('title')
                ->pluck('title')
                ->filter()
                ->values()
                ->all();

            foreach (range('A', 'V') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            $booleanColumns = ['O', 'P', 'U', 'V'];
            foreach ($booleanColumns as $column) {
                for ($row = 2; $row <= 1000; $row++) {
                    $validation = $sheet->getDataValidation($column . $row);
                    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $validation->setFormula1('"true,false"');
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setPromptTitle('Boolean Value');
                    $validation->setPrompt('Please select true or false');
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Invalid Value');
                    $validation->setError('Please select true or false only');
                }
            }

            for ($row = 2; $row <= 1000; $row++) {
                $validation = $sheet->getDataValidation('H' . $row);
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setFormula1('"IN,US,UK,CA,AU,DE,FR,IT,ES,JP,CN,KR,BR,MX,AR,CL,CO,PE,VE,EC,BO,PY,UY,GY,SR,GF,FG"');
                $validation->setAllowBlank(false);
                $validation->setShowDropDown(true);
                $validation->setPromptTitle('Country Code');
                $validation->setPrompt('Please select a country code');
                $validation->setShowErrorMessage(true);
                $validation->setErrorTitle('Invalid Country Code');
                $validation->setError('Please select a valid country code');
            }

            $instructionsSheet = $spreadsheet->createSheet();
            $instructionsSheet->setTitle('Instructions');
            $instructionsSheet->setCellValue('A1', 'How to use this template');
            $instructionsSheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
            $instructionsSheet->setCellValue('A2', '1. Do not modify the header row on the "Restaurants" sheet.');
            $instructionsSheet->setCellValue('A3', '2. Starting at row 2, enter one restaurant per row. Leave optional cells blank if not needed.');
            $instructionsSheet->setCellValue('A4', '3. Use proper formats for each column (dates, numbers, boolean values as true/false).');
            $instructionsSheet->setCellValue('A5', '4. Save the file as XLSX/XLS and upload it back in the admin panel.');
            $instructionsSheet->setCellValue('A7', 'Available Zones:');
            $instructionsSheet->getStyle('A7')->getFont()->setBold(true);
            $instructionsSheet->setCellValue('A8', empty($zones) ? 'No published zones found.' : implode(', ', $zones));
            $instructionsSheet->setCellValue('A10', 'Available Cuisines:');
            $instructionsSheet->getStyle('A10')->getFont()->setBold(true);
            $instructionsSheet->setCellValue('A11', empty($cuisines) ? 'No cuisines found.' : implode(', ', $cuisines));
            $instructionsSheet->setCellValue('A13', 'Sample row (replace with your own data)');
            $instructionsSheet->getStyle('A13')->getFont()->setBold(true);
            $sampleRow = [
                '',
                'Sample Restaurant',
                'Describe your restaurant here',
                '15.12345',
                '80.12345',
                '123 Main Street, City, State',
                '1234567890',
                'IN',
                '',
                '',
                '',
                'Biryani, Pizza',
                'Indian',
                '{"commissionType":"Fixed","fix_commission":12,"isEnabled":true}',
                'true',
                'false',
                '250',
                '09:30',
                '22:00',
                'https://example.com/restaurant-photo.jpg',
                'false',
                'false'
            ];
            $instructionsSheet->fromArray($headers, null, 'A15');
            $instructionsSheet->fromArray($sampleRow, null, 'A16');
            foreach (range('A', 'V') as $column) {
                $instructionsSheet->getColumnDimension($column)->setAutoSize(true);
            }

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $filePath = storage_path('app/templates/restaurants_bulk_update_template.xlsx');
            $directory = dirname($filePath);

            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            $writer->save($filePath);

            return response()->download($filePath, 'restaurants_bulk_update_template.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="restaurants_bulk_update_template.xlsx"',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error generating restaurant template: ' . $e->getMessage());
            return back()->withErrors(['file' => 'Error generating template: ' . $e->getMessage()]);
        }
    }

    /**
     * Get vendors data for DataTables (SQL-based)
     */
    public function getVendorsData(Request $request)
    {
        try {
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $searchValue = $request->input('search.value', '');
            $type = $request->input('type', 'all'); // all, pending, approved

            // Build base query - use subquery to get unique vendors by firebase_id (like Firebase did)
            // This ensures we only get one record per unique vendor
            $subQuery = DB::table('users')
                ->select(DB::raw('MAX(id) as max_id'))
                ->where('role', 'vendor')
                ->whereNotNull('firebase_id')
                ->where('firebase_id', '!=', '')
                ->groupBy('firebase_id');

            // Apply type filter to subquery
            if ($type === 'pending') {
                $subQuery->where('active', '0');
            } elseif ($type === 'approved') {
                $subQuery->where('active', '1');
            }

            // Get the IDs from subquery
            $uniqueIds = $subQuery->pluck('max_id')->toArray();

            // Now build main query using those unique IDs
//            $query = AppUser::whereIn('id', $uniqueIds);
            $query = AppUser::query()
                ->whereIn('users.id', $uniqueIds)
                ->leftJoin('vendors', 'vendors.id', '=', 'users.vendorID') // ✅ Correct join
                ->leftJoin('zone', 'vendors.zoneId', '=', 'zone.id') // ✅ Join zone table to get zone name
                ->select(
                    'users.*',
                    'vendors.zoneId as vendor_zoneId', // ✅ we pull zoneId from vendors table
                    'zone.name as zone_name' // ✅ we pull zone name from zone table
                );


            // Apply additional filters if provided
            if ($request->has('vendor_type') && $request->vendor_type != '') {
//                $query->where('vType', $request->vendor_type);
                $query->where('users.vType', $request->vendor_type);
            }

            if ($request->has('status') && $request->status != '') {
                $query->where('active', $request->status === 'active' ? '1' : '0');
            }

            if ($request->has('zone') && $request->zone != '') {
                $query->where('vendors.zoneId', $request->zone); // ✅ Correct source table
            }

            // Store zone sort preference for later
            $zoneSort = $request->input('zone_sort', '');

            // Apply date range filter
            // The createdAt field is stored as JSON string like "2025-10-16T07:13:41.487000Z"
            // We need to strip quotes and compare
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = date('Y-m-d', strtotime($request->start_date));
                $endDate = date('Y-m-d', strtotime($request->end_date));

                $query->whereRaw("DATE(REPLACE(REPLACE(createdAt, '\"', ''), 'T', ' ')) BETWEEN ? AND ?",
                    [$startDate, $endDate]);
            }

            // Get total count before applying search
            $totalRecords = $query->count();

            // Apply search filter
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('users.firstName', 'like', "%{$searchValue}%")
                        ->orWhere('users.lastName', 'like', "%{$searchValue}%")
                        ->orWhere('users.email', 'like', "%{$searchValue}%")
                        ->orWhere('users.phoneNumber', 'like', "%{$searchValue}%")
                        ->orWhere('users.vType', 'like', "%{$searchValue}%")
                        ->orWhere('zone.name', 'like', "%{$searchValue}%")
                        ->orWhere(DB::raw("CONCAT(users.firstName, ' ', users.lastName)"), 'like', "%{$searchValue}%");
                });
            }

            $filteredRecords = $query->count();

            // Apply ordering
            // If zone sort is requested, sort by zone name, otherwise by createdAt descending
            if (!empty($zoneSort)) {
                // Sort by zone name (zone table already joined above)
                $vendors = $query->orderBy('zone.name', $zoneSort)
                    ->orderByRaw("REPLACE(REPLACE(users.createdAt, '\"', ''), 'T', ' ') DESC")
                    ->skip($start)
                    ->take($length)
                    ->get();
            } else {
                // Get paginated records - order by parsed createdAt in descending order
                // Remove quotes and convert to proper datetime for sorting
                $vendors = $query->orderByRaw("REPLACE(REPLACE(users.createdAt, '\"', ''), 'T', ' ') DESC")
                    ->skip($start)
                    ->take($length)
                    ->get();
            }

            // Build response data
            $data = [];
            foreach ($vendors as $vendor) {
                // Parse createdAt date from JSON format "2025-10-16T07:13:41.487000Z"
                $createdAtFormatted = '';
                if ($vendor->createdAt) {
                    try {
                        // Remove quotes and parse the ISO date
                        $dateStr = trim($vendor->createdAt, '"');
                        $date = new \DateTime($dateStr);
                        $date->setTimezone(new \DateTimeZone('Asia/Kolkata'));
                        $createdAtFormatted = $date->format('M d, Y h:i A');
                    } catch (\Exception $e) {
                        $createdAtFormatted = $vendor->createdAt;
                    }
                }

                // Parse subscription expiry date similarly
                $expiryDateFormatted = '';
                if ($vendor->subscriptionExpiryDate) {
                    try {
                        $dateStr = trim($vendor->subscriptionExpiryDate, '"');
                        $date = new \DateTime($dateStr);
                        $expiryDateFormatted = $date->format('M d, Y');
                    } catch (\Exception $e) {
                        $expiryDateFormatted = $vendor->subscriptionExpiryDate;
                    }
                }

                $vendorData = [
                    'id' => $vendor->firebase_id ?? $vendor->_id,
                    'firstName' => $vendor->firstName ?? '',
                    'lastName' => $vendor->lastName ?? '',
                    'fullName' => trim(($vendor->firstName ?? '') . ' ' . ($vendor->lastName ?? '')),
                    'email' => $vendor->email ?? '',
                    'phoneNumber' => $vendor->phoneNumber ?? '',
                    'countryCode' => $vendor->countryCode ?? '',
                    'profilePictureURL' => $vendor->profilePictureURL ?? '',
                    'active' => $vendor->active == '1' || $vendor->active === 'true' || $vendor->active === true,
                    'zoneId' => $vendor->vendor_zoneId ?? '',
                    'zoneName' => $vendor->zone_name ?? '', // ✅ Include zone name from joined zone table
                    'vType' => $vendor->vType ?? 'restaurant',
                    'vendorID' => $vendor->vendorID ?? '',
                    'subscriptionPlanId' => $vendor->subscriptionPlanId ?? '',
                    'subscription_plan' => $vendor->subscription_plan ? json_decode($vendor->subscription_plan, true) : null,
                    'subscriptionExpiryDate' => $expiryDateFormatted,
                    'subscriptionExpiryDateRaw' => $vendor->subscriptionExpiryDate ?? '',
                    'userBankDetails' => $vendor->userBankDetails ? json_decode($vendor->userBankDetails, true) : null,
                    'createdAt' => $createdAtFormatted,
                    'createdAtRaw' => $vendor->createdAt ?? '',
                    'isDocumentVerify' => $vendor->isDocumentVerify == '1' || $vendor->isDocumentVerify === 'true' || $vendor->isDocumentVerify === true,
                ];

                $data[] = $vendorData;
            }

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
                'vendor_count' => $filteredRecords
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching vendors data: ' . $e->getMessage());
            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get single vendor data by ID
     */
    public function getVendorById($id)
    {
        try {
            // Try to find vendor by multiple ID fields
            $vendor = AppUser::where(function($query) use ($id) {
                $query->where('firebase_id', $id)
                    ->orWhere('_id', $id);

                // Also try numeric ID if the input is numeric
                if (is_numeric($id)) {
                    $query->orWhere('id', $id);
                }
            })
                ->where('role', 'vendor')
                ->first();


            if (!$vendor) {
                \Log::warning('Vendor not found with ID: ' . $id);


                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found with ID: ' . $id
                ], 404);
            }


            // Parse dates from JSON format
            $subscriptionExpiryDate = '';
            $subscriptionExpiryDateFormatted = '';
            if ($vendor->subscriptionExpiryDate) {
                try {
                    $dateStr = trim($vendor->subscriptionExpiryDate, '"');
                    $date = new \DateTime($dateStr);
                    $subscriptionExpiryDate = $date->format('Y-m-d\TH:i:s.u\Z');
                    $subscriptionExpiryDateFormatted = $date->format('Y-m-d'); // For date input field
                } catch (\Exception $e) {
                    \Log::warning('Error parsing subscription expiry date: ' . $e->getMessage());
                    $subscriptionExpiryDate = trim($vendor->subscriptionExpiryDate, '"');
                    $subscriptionExpiryDateFormatted = '';
                }
            }

            // Parse userBankDetails
            $userBankDetails = null;
            if ($vendor->userBankDetails) {
                try {
                    if (is_string($vendor->userBankDetails)) {
                        $userBankDetails = json_decode($vendor->userBankDetails, true);
                    } else {
                        $userBankDetails = $vendor->userBankDetails;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error parsing bank details: ' . $e->getMessage());
                }
            }

            // Parse subscription_plan
            $subscriptionPlan = null;
            if ($vendor->subscription_plan) {
                try {
                    if (is_string($vendor->subscription_plan)) {
                        $subscriptionPlan = json_decode($vendor->subscription_plan, true);
                    } else {
                        $subscriptionPlan = $vendor->subscription_plan;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Error parsing subscription plan: ' . $e->getMessage());
                }
            }

            $vendorData = [
                'id' => $vendor->firebase_id ?? $vendor->_id ?? $vendor->id,
                'firstName' => $vendor->firstName ?? '',
                'lastName' => $vendor->lastName ?? '',
                'email' => $vendor->email ?? '',
                'phoneNumber' => $vendor->phoneNumber ?? '',
                'countryCode' => $vendor->countryCode ?? '',
                'profilePictureURL' => $vendor->profilePictureURL ?? '',
                'active' => $vendor->active == '1' || $vendor->active === 'true' || $vendor->active === true,
                'zoneId' => $vendor->zoneId ?? '',
                'vType' => $vendor->vType ?? 'restaurant',
                'vendorID' => $vendor->vendorID ?? '',
                'subscriptionPlanId' => $vendor->subscriptionPlanId ?? '',
                'subscription_plan' => $subscriptionPlan,
                'subscriptionExpiryDate' => $subscriptionExpiryDateFormatted, // For date input
                'subscriptionExpiryDateRaw' => $vendor->subscriptionExpiryDate ?? '',
                'userBankDetails' => $userBankDetails,
                'provider' => $vendor->provider ?? 'email',
                'isDocumentVerify' => $vendor->isDocumentVerify == '1' || $vendor->isDocumentVerify === 'true' || $vendor->isDocumentVerify === true,
            ];


            return response()->json([
                'success' => true,
                'data' => $vendorData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching vendor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update vendor data
     */
    public function updateVendor(Request $request, $id)
    {
        try {
            // Try to find vendor by multiple ID fields
            $vendor = AppUser::where(function($query) use ($id) {
                $query->where('firebase_id', $id)
                    ->orWhere('_id', $id);

                // Also try numeric ID if the input is numeric
                if (is_numeric($id)) {
                    $query->orWhere('id', $id);
                }
            })
                ->where('role', 'vendor')
                ->first();


            if (!$vendor) {
                \Log::warning('Vendor not found for update with ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found with ID: ' . $id
                ], 404);
            }

            // Update vendor fields
            if ($request->has('firstName')) $vendor->firstName = $request->firstName;
            if ($request->has('lastName')) $vendor->lastName = $request->lastName;
            if ($request->has('phoneNumber')) $vendor->phoneNumber = $request->phoneNumber;
            if ($request->has('countryCode')) $vendor->countryCode = $request->countryCode;
            if ($request->has('vType')) $vendor->vType = $request->vType;
            if ($request->has('profilePictureURL')) $vendor->profilePictureURL = $request->profilePictureURL;
            if ($request->has('active')) {
                $vendor->active = $this->toBoolInt($request->input('active'));
            }
            if ($request->has('subscriptionPlanId')) $vendor->subscriptionPlanId = $request->subscriptionPlanId;
            if ($request->has('subscription_plan')) $vendor->subscription_plan = json_encode($request->subscription_plan);
            if ($request->has('subscriptionExpiryDate')) {
                // Format date as JSON string like "2025-10-16T07:13:41.487000Z" if it's a regular date
                $expiryDate = $request->subscriptionExpiryDate;
                if (!str_starts_with($expiryDate, '"')) {
                    try {
                        $date = new \DateTime($expiryDate);
                        $expiryDate = '"' . $date->format('Y-m-d\TH:i:s.u\Z') . '"';
                    } catch (\Exception $e) {
                        // Keep as is if parsing fails
                    }
                }
                $vendor->subscriptionExpiryDate = $expiryDate;
            }
            if ($request->has('userBankDetails')) $vendor->userBankDetails = json_encode($request->userBankDetails);

            $vendor->save();

            // Update vendor business info if vendorID exists
            if ($vendor->vendorID && $request->has('authorName')) {
                $vendorBusiness = Vendor::find($vendor->vendorID);
                if ($vendorBusiness) {
                    $vendorBusiness->authorName = $request->authorName;
                    if ($request->has('profilePictureURL')) {
                        $vendorBusiness->authorProfilePic = $request->profilePictureURL;
                    }
                    if ($request->has('subscriptionExpiryDate')) {
                        $vendorBusiness->subscriptionExpiryDate = $request->subscriptionExpiryDate;
                    }
                    $vendorBusiness->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Vendor updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating vendor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle vendor active status
     */
    public function toggleVendorStatus($id)
    {
        try {
            // Try to find vendor by multiple ID fields
            $vendor = AppUser::where(function($query) use ($id) {
                $query->where('firebase_id', $id)
                    ->orWhere('_id', $id);

                // Also try numeric ID if the input is numeric
                if (is_numeric($id)) {
                    $query->orWhere('id', $id);
                }
            })
                ->where('role', 'vendor')
                ->first();

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found with ID: ' . $id
                ], 404);
            }

            $current = $this->toBoolInt($vendor->active);
            $vendor->active = $current ? 0 : 1;
            $vendor->save();

            return response()->json([
                'success' => true,
                'active' => (bool) $vendor->active
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling vendor status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error toggling vendor status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete vendor
     */
    public function deleteVendor($id)
    {
        try {
            // Try to find vendor by multiple ID fields
            $vendor = AppUser::where(function($query) use ($id) {
                $query->where('firebase_id', $id)
                    ->orWhere('_id', $id);

                // Also try numeric ID if the input is numeric
                if (is_numeric($id)) {
                    $query->orWhere('id', $id);
                }
            })
                ->where('role', 'vendor')
                ->first();

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found with ID: ' . $id
                ], 404);
            }

            // Delete vendor and related data
            // Note: This is a simplified version. You may need to delete related data from other tables
            $vendor->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vendor deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting vendor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getZones()
    {
        $zones = DB::table('zone')
            ->where('publish', 1)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json([
            'success' => true,  // <- IMPORTANT
            'data' => $zones
        ]);
    }



    /**
     * Create new vendor
     */
    public function createVendor(Request $request)
    {
        try {
            $request->validate([
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'phoneNumber' => 'required',
                'countryCode' => 'required'
            ]);

            // Check if email already exists
            $existingVendor = AppUser::where('email', $request->email)->first();
            if ($existingVendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already exists'
                ], 400);
            }

            // Create vendor
            $vendor = new AppUser();
            $vendor->firstName = $request->firstName;
            $vendor->lastName = $request->lastName;
            $vendor->email = $request->email;
            $vendor->password = Hash::make($request->password);
            $vendor->phoneNumber = $request->phoneNumber;
            $vendor->countryCode = $request->countryCode;
            $vendor->role = 'vendor';
            $vendor->vType = $request->vType ?? 'restaurant';
            $vendor->active = $this->toBoolInt($request->input('active', 0));
            $vendor->profilePictureURL = $request->profilePictureURL ?? '';
            $vendor->provider = 'email';
            $vendor->appIdentifier = 'web';
            $vendor->isDocumentVerify = 0;
            // Store createdAt in same JSON format as Firebase: "2025-10-16T07:13:41.487000Z"
            $vendor->createdAt = '"' . gmdate('Y-m-d\TH:i:s.u\Z') . '"';

            if ($request->has('subscriptionPlanId')) {
                $vendor->subscriptionPlanId = $request->subscriptionPlanId;
            }
            if ($request->has('subscription_plan')) {
                $vendor->subscription_plan = json_encode($request->subscription_plan);
            }
            if ($request->has('subscriptionExpiryDate')) {
                $vendor->subscriptionExpiryDate = $request->subscriptionExpiryDate;
            }

            // Generate unique firebase_id
            $vendor->firebase_id = uniqid();
            $vendor->_id = $vendor->firebase_id;

            $vendor->save();

            return response()->json([
                'success' => true,
                'message' => 'Vendor created successfully',
                'vendor_id' => $vendor->firebase_id
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating vendor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating vendor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get placeholder image
     */
    public function getPlaceholderImage()
    {
        try {
            // Settings table structure: id (auto-increment), document_name (unique), fields (JSON)
            $placeholder = DB::table('settings')
                ->where('document_name', 'placeHolderImage')
                ->first();

            if ($placeholder && !empty($placeholder->fields)) {
                $fieldsData = json_decode($placeholder->fields, true);
                if (isset($fieldsData['image'])) {
                    return response()->json([
                        'success' => true,
                        'image' => $fieldsData['image']
                    ]);
                }
            }

            // Return default placeholder if not found
            return response()->json([
                'success' => true,
                'image' => asset('assets/images/placeholder-image.png')
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching placeholder image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching placeholder image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new restaurant
     */
    public function createRestaurant(Request $request)
    {
        try {
            $restaurantData = $request->restaurantData;
            $restaurant_id = $request->restaurant_id ?? uniqid('rest_');
            $user_id = $request->user_id;

            // Helper function to convert boolean values
            $toBool = function($value) {
                if (is_bool($value)) return $value ? 1 : 0;
                if (is_string($value)) {
                    $value = strtolower($value);
                    return ($value === 'true' || $value === '1') ? 1 : 0;
                }
                return $value ? 1 : 0;
            };

            // Helper function to handle JSON encoding
            $toJson = function($value, $default = []) {
                if (!isset($value)) return json_encode($default);
                return is_string($value) ? $value : json_encode($value);
            };

            // Create restaurant record
            $restaurant = new Vendor();
            $restaurant->id = $restaurant_id;
            $restaurant->title = $restaurantData['title'] ?? '';
            $restaurant->description = $restaurantData['description'] ?? '';
            $restaurant->latitude = floatval($restaurantData['latitude'] ?? 0);
            $restaurant->longitude = floatval($restaurantData['longitude'] ?? 0);
            $restaurant->location = $restaurantData['location'] ?? '';
            $restaurant->photo = $restaurantData['photo'] ?? '';
            $restaurant->photos = $toJson($restaurantData['photos'] ?? null, []);
            $restaurant->phonenumber = $restaurantData['phonenumber'] ?? '';
            $restaurant->countryCode = $restaurantData['countryCode'] ?? '';
            $restaurant->vType = $restaurantData['vType'] ?? 'restaurant';
            $restaurant->zoneId = $restaurantData['zoneId'] ?? '';
            $restaurant->author = $restaurantData['author'] ?? '';
            $restaurant->authorName = $restaurantData['authorName'] ?? '';
            $restaurant->authorProfilePic = $restaurantData['authorProfilePic'] ?? '';
            $restaurant->categoryID = $toJson($restaurantData['categoryID'] ?? null, []);
            $restaurant->categoryTitle = $toJson($restaurantData['categoryTitle'] ?? null, []);
            $restaurant->vendorCuisineID = $restaurantData['vendorCuisineID'] ?? '';

            // Convert boolean fields to tinyint (0 or 1)
            $restaurant->reststatus = $toBool($restaurantData['reststatus'] ?? 1);
            $restaurant->isOpen = $toBool($restaurantData['isOpen'] ?? 1);
            $restaurant->specialDiscountEnable = $toBool($restaurantData['specialDiscountEnable'] ?? 0);
            $restaurant->enabledDiveInFuture = $toBool($restaurantData['enabledDiveInFuture'] ?? 0);

            $restaurant->workingHours = $toJson($restaurantData['workingHours'] ?? null, []);
            $restaurant->filters = $toJson($restaurantData['filters'] ?? null, []);
            $restaurant->adminCommission = $toJson($restaurantData['adminCommission'] ?? null, []);
            $restaurant->specialDiscount = $toJson($restaurantData['specialDiscount'] ?? null, []);
            $restaurant->restaurantCost = $restaurantData['restaurantCost'] ?? '';
            $restaurant->restaurantMenuPhotos = $toJson($restaurantData['restaurantMenuPhotos'] ?? null, []);
            $restaurant->openDineTime = $restaurantData['openDineTime'] ?? '';
            $restaurant->closeDineTime = $restaurantData['closeDineTime'] ?? '';
            $restaurant->subscriptionPlanId = $restaurantData['subscriptionPlanId'] ?? '';
            $restaurant->subscription_plan = isset($restaurantData['subscription_plan']) ? $toJson($restaurantData['subscription_plan'], null) : null;
            $restaurant->subscriptionExpiryDate = $restaurantData['subscriptionExpiryDate'] ?? '';
            $restaurant->subscriptionTotalOrders = $restaurantData['subscriptionTotalOrders'] ?? '';
            $restaurant->createdAt = now('Asia/Kolkata')->format('M j, Y g:i A');

            $restaurant->save();

            // Update user vendorID if requested
            if ($request->updateUserVendorID && $user_id && $user_id !== 'admin_created') {
                $user = AppUser::where('firebase_id', $user_id)
                    ->orWhere('_id', $user_id)
                    ->where('role', 'vendor')
                    ->first();

                if ($user) {
                    $user->vendorID = $restaurant_id;
                    $user->save();
                }
            }

            // Save story data if provided
            if ($request->has('storyData') && !empty($request->storyData)) {
                $storyData = $request->storyData;
                $videoThumbnail = $storyData['thumbnail'] ?? '';
                $videoUrls = $storyData['videos'] ?? [];

                if (!empty($videoThumbnail) || !empty($videoUrls)) {

                    // convert array to comma separated string instead of JSON
                    $videoUrlJson = !empty($videoUrls) ? implode(',', $videoUrls) : null;

                    $firestoreId = 'story_' . Str::uuid()->toString();

                    DB::table('story')->updateOrInsert(
                        ['vendor_id' => $restaurant_id],
                        [
                            'firestore_id' => $firestoreId,
                            'vendor_id' => $restaurant_id,
                            'video_thumbnail' => $videoThumbnail,
                            'video_url' => $videoUrlJson,
                            'created_at' => now('Asia/Kolkata'),
                            'updated_at' => now('Asia/Kolkata')
                        ]
                    );
                }
            }


            return response()->json([
                'success' => true,
                'message' => 'Restaurant created successfully',
                'restaurant_id' => $restaurant_id
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating restaurant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating restaurant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get restaurants data for DataTables (SQL-based)
     */
    public function getRestaurantsData(Request $request)
    {
        try {
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $searchValue = $request->input('search.value', '');

            // Build base query - get unique restaurants by id
            $subQuery = DB::table('vendors')
                ->select(DB::raw('MAX(id) as vendor_id'))
                ->groupBy('id');

            $vendorIds = $subQuery->pluck('vendor_id')->toArray();
            $query = Vendor::whereIn('id', $vendorIds);

            // Apply filters
            if ($request->has('restaurant_type') && $request->restaurant_type != '') {
                if ($request->restaurant_type === 'true') {
                    $query->where('dine_in_active', '!=', '');
                }
            }

            if ($request->has('zone') && $request->zone != '') {
                $query->where('zoneId', $request->zone);
            }

            if ($request->has('vType') && $request->vType != '') {
                $query->where('vType', $request->vType);
            }

            if ($request->has('business_model') && $request->business_model != '') {
                $query->where('subscriptionPlanId', $request->business_model);
            }

            // Apply date range filter
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = date('Y-m-d', strtotime($request->start_date));
                $endDate = date('Y-m-d', strtotime($request->end_date));
                $query->whereRaw("DATE(REPLACE(REPLACE(createdAt, '\"', ''), 'T', ' ')) BETWEEN ? AND ?",
                    [$startDate, $endDate]);
            }

            $totalRecords = $query->count();

            // Apply search filter
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('title', 'like', "%{$searchValue}%")
                        ->orWhere('location', 'like', "%{$searchValue}%")
                        ->orWhere('phonenumber', 'like', "%{$searchValue}%")
                        ->orWhere('description', 'like', "%{$searchValue}%");
                });
            }

            $filteredRecords = $query->count();

//            // Get counts for statistics
//            $totalRestaurants = Vendor::count();
//            $activeRestaurants = Vendor::where('reststatus', 1)->count();
//            $inactiveRestaurants = Vendor::where('reststatus', 0)->count();
//
//            // Get new joined (last 30 days)
//            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
//            $newJoined = Vendor::whereRaw("DATE(REPLACE(REPLACE(createdAt, '\"', ''), 'T', ' ')) >= ?", [$thirtyDaysAgo])->count();

            $statsQuery = Vendor::query();

// APPLY SAME FILTERS AS MAIN QUERY
            if ($request->zone != '') {
                $statsQuery->where('zoneId', $request->zone);
            }
            if ($request->restaurant_type != '') {
                if ($request->restaurant_type === 'true') {
                    $statsQuery->where('dine_in_active', '!=', '');
                }
            }
            if ($request->vType != '') {
                $statsQuery->where('vType', $request->vType);
            }

            $totalRestaurants = $statsQuery->count();
            $activeRestaurants = $statsQuery->clone()->where('reststatus', 1)->count();
            $inactiveRestaurants = $statsQuery->clone()->where('reststatus', 0)->count();

            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            $newJoined = $statsQuery->clone()
                ->whereRaw("DATE(REPLACE(REPLACE(createdAt, '\"', ''), 'T', ' ')) >= ?", [$thirtyDaysAgo])
                ->count();


            // Apply ordering - descending by createdAt
            $restaurants = $query->orderByRaw("REPLACE(REPLACE(createdAt, '\"', ''), 'T', ' ') DESC")
                ->skip($start)
                ->take($length)
                ->get();

            // Build response data
            $data = [];
            foreach ($restaurants as $restaurant) {
                // Parse createdAt date
                $createdAtFormatted = '';
                if ($restaurant->createdAt) {
                    try {
                        $dateStr = trim($restaurant->createdAt, '"');
                        $date = new \DateTime($dateStr);
                        $createdAtFormatted = $date->format('M d, Y h:i A');
                    } catch (\Exception $e) {
                        $createdAtFormatted = $restaurant->createdAt;
                    }
                }

                // Parse adminCommission (JSON field)
                $adminCommission = null;
                if ($restaurant->adminCommission) {
                    $adminCommission = is_string($restaurant->adminCommission)
                        ? json_decode($restaurant->adminCommission, true)
                        : $restaurant->adminCommission;
                }

                // Fetch plan details directly from subscription_plans table
                $planName = null;
                $planCommission = null;
                if (!empty($restaurant->subscriptionPlanId)) {
                    $plan = DB::table('subscription_plans')
                        ->where('id', $restaurant->subscriptionPlanId)
                        ->first();
                    if ($plan) {
                        $planName = $plan->name ?? null;
                        $planCommission = $plan->place ?? null;
                    }
                }

                $restaurantData = [
                    'id' => $restaurant->id,
                    'title' => $restaurant->title ?? '',
                    'description' => $restaurant->description ?? '',
                    'location' => $restaurant->location ?? '',
                    'latitude' => $restaurant->latitude ?? 0,
                    'longitude' => $restaurant->longitude ?? 0,
                    'photo' => $restaurant->photo ?? '',
                    'photos' => $restaurant->photos ? json_decode($restaurant->photos, true) : [],
                    'phonenumber' => $restaurant->phonenumber ?? '',
                    'zoneId' => $restaurant->zoneId ?? '',
                    'author' => $restaurant->author ?? '',
                    'authorName' => $restaurant->authorName ?? '',
                    'authorProfilePic' => $restaurant->authorProfilePic ?? '',
                    'categoryID' => $restaurant->categoryID ? json_decode($restaurant->categoryID, true) : [],
                    'categoryTitle' => $restaurant->categoryTitle ? json_decode($restaurant->categoryTitle, true) : [],
                    'reststatus' => $restaurant->reststatus == 1 || $restaurant->reststatus === 'true' || $restaurant->reststatus === true,
                    'isActive' => $restaurant->reststatus == 1 || $restaurant->reststatus === 'true' || $restaurant->reststatus === true,
                    'isOpen' => $restaurant->isOpen == 1 || $restaurant->isOpen === 'true' || $restaurant->isOpen === true,
                    'reviewsCount' => $restaurant->reviewsCount ?? 0,
                    'reviewsSum' => $restaurant->reviewsSum ?? 0,
                    'createdAt' => $createdAtFormatted,
                    'createdAtRaw' => $restaurant->createdAt ?? '',
                    'vType' => $restaurant->vType ?? 'restaurant',
                    'walletAmount' => $restaurant->walletAmount ?? 0,
                    'adminCommission' => $adminCommission, // Include adminCommission object with fix_commission
                    'subscriptionPlanId' => $restaurant->subscriptionPlanId ?? null, // Include subscriptionPlanId
                    'subscriptionPlanName' => $planName, // Plan name from subscription_plans table
                    'subscriptionPlanCommission' => $planCommission, // Plan commission (place) from subscription_plans table
                    'subscriptionExpiryDate' => $restaurant->subscriptionExpiryDate ?? null, // Include subscriptionExpiryDate
                    'best' => $restaurant->best == 1 || $restaurant->best === 'true' || $restaurant->best === true, // Include best status
                    'gst' => $restaurant->gst == 1 || $restaurant->gst === 'true' || $restaurant->gst === true, // Include GST status
                    'publish' => $restaurant->publish == 1 || $restaurant->publish === 'true' || $restaurant->publish === true, // Include publish status
                ];

                $data[] = $restaurantData;
            }

            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
                'stats' => [
                    'total' => $totalRestaurants,
                    'active' => $activeRestaurants,
                    'inactive' => $inactiveRestaurants,
                    'new_joined' => $newJoined
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching restaurants data: ' . $e->getMessage());
            return response()->json([
                'draw' => $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Apply global open/close status to restaurants in a given zone (MySQL).
     */
    public function updateGlobalStatus(Request $request)
    {
        $validated = $request->validate([
            'is_open' => 'required|boolean',
            'zone_id' => 'nullable|string',
        ]);

        $zoneId = $validated['zone_id'] ?? $request->input('zoneId');
        if ($zoneId !== null && $zoneId !== '') {
            $zoneId = trim((string) $zoneId);
        } else {
            $zoneId = null;
        }

        try {
            $status = $this->toBoolInt($validated['is_open']);

            $query = Vendor::query();
            if ($zoneId) {
                $query->where('zoneId', $zoneId);
            }

            $updatedCount = $query->update([
                'isOpen' => $status,
                'reststatus' => $status,
            ]);

            return response()->json([
                'success' => true,
                'updated' => $updatedCount,
                'is_open' => (bool) $status,
                'zone_id' => $zoneId,
                'scope' => $zoneId ? 'zone' : 'all',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating global restaurant status', [
                'zone_id' => $zoneId,
                'is_open' => $validated['is_open'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to update restaurants. Please try again later.',
            ], 500);
        }
    }

    /**
     * Get single restaurant data by ID
     */
    public function getRestaurantById($id)
    {
        try {
            // Try to find by string ID column first (Firebase-style ID), then by numeric primary key
            $restaurant = Vendor::where('id', $id)->first();

            if (!$restaurant && is_numeric($id)) {
                // Fallback to numeric primary key if not found by string ID
                $restaurant = Vendor::find($id);
            }


            if (!$restaurant) {
                \Log::warning('Restaurant not found with ID: ' . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found with ID: ' . $id
                ], 404);
            }


            // Fetch subscription plan details directly from subscription_plans table
            $subscriptionPlan = null;
            if (!empty($restaurant->subscriptionPlanId)) {
                $plan = DB::table('subscription_plans')
                    ->where('id', $restaurant->subscriptionPlanId)
                    ->first();
                if ($plan) {
                    // Convert plan to array format expected by frontend
                    $subscriptionPlan = [
                        'id' => $plan->id ?? '',
                        'name' => $plan->name ?? '',
                        'price' => $plan->price ?? 0,
                        'expiryDay' => $plan->expiryDay ?? -1,
                        'type' => $plan->type ?? 'paid',
                        'place' => $plan->place ?? 0, // Commission percentage
                        'orderLimit' => $plan->orderLimit ?? -1,
                        'itemLimit' => $plan->itemLimit ?? -1,
                        'features' => $plan->features ? json_decode($plan->features, true) : [],
                        'image' => $plan->image ?? '',
                    ];
                }
            }

            // Parse and format data
            $restaurantData = [
                'id' => $restaurant->id,
                'title' => $restaurant->title ?? '',
                'description' => $restaurant->description ?? '',
                'location' => $restaurant->location ?? '',
                'latitude' => $restaurant->latitude ?? 0,
                'longitude' => $restaurant->longitude ?? 0,
                'photo' => $restaurant->photo ?? '',
                'photos' => $restaurant->photos ? json_decode($restaurant->photos, true) : [],
                'phonenumber' => $restaurant->phonenumber ?? '',
                'zoneId' => $restaurant->zoneId ?? '',
                'author' => $restaurant->author ?? '',
                'authorName' => $restaurant->authorName ?? '',
                'authorProfilePic' => $restaurant->authorProfilePic ?? '',
                'categoryID' => $restaurant->categoryID ? json_decode($restaurant->categoryID, true) : [],
                'categoryTitle' => $restaurant->categoryTitle ? json_decode($restaurant->categoryTitle, true) : [],
                'cuisineID' => $restaurant->cuisineID ?? '',
                'cuisineTitle' => $restaurant->cuisineTitle ?? '',
                'vendorCuisineID' => $restaurant->vendorCuisineID ?? '',
                'reststatus' => $restaurant->reststatus == 1 || $restaurant->reststatus === 'true' || $restaurant->reststatus === true,
                'isOpen' => $restaurant->isOpen == 1 || $restaurant->isOpen === 'true' || $restaurant->isOpen === true,
                'reviewsCount' => $restaurant->reviewsCount ?? 0,
                'reviewsSum' => $restaurant->reviewsSum ?? 0,
                'workingHours' => $restaurant->workingHours ? json_decode($restaurant->workingHours, true) : [],
                'filters' => $restaurant->filters ? json_decode($restaurant->filters, true) : [],
                'createdAt' => $restaurant->createdAt ?? '',
                'vType' => $restaurant->vType ?? 'restaurant',
                'walletAmount' => $restaurant->walletAmount ?? 0,
                'adminCommission' => $restaurant->adminCommission ? json_decode($restaurant->adminCommission, true) : null,
                'DeliveryCharge' => $restaurant->DeliveryCharge ?? false,
                'specialDiscount' => $restaurant->specialDiscount ? json_decode($restaurant->specialDiscount, true) : [],
                'specialDiscountEnable' => $restaurant->specialDiscountEnable ?? false,
                'hidephotos' => $restaurant->hidephotos ?? false,
                'restaurantCost' => $restaurant->restaurantCost ?? '',
                'restaurantMenuPhotos' => $restaurant->restaurantMenuPhotos ? json_decode($restaurant->restaurantMenuPhotos, true) : [],
                'openDineTime' => $restaurant->openDineTime ?? '',
                'closeDineTime' => $restaurant->closeDineTime ?? '',
                'dine_in_active' => $restaurant->dine_in_active ?? '',
                'enabledDiveInFuture' => $restaurant->enabledDiveInFuture ?? false,
                'subscription_plan' => $subscriptionPlan, // Plan details from subscription_plans table
                'subscriptionPlanId' => $restaurant->subscriptionPlanId ?? '',
                'subscriptionExpiryDate' => $restaurant->subscriptionExpiryDate ?? '',
                'subscriptionTotalOrders' => $restaurant->subscriptionTotalOrders ?? '',
                'bill_status' => $restaurant->bill_status ?? null,
                'coordinates' => [
                    'latitude' => $restaurant->latitude ?? 0,
                    'longitude' => $restaurant->longitude ?? 0
                ],
                'isActive' => $restaurant->isActive ?? true,
                'opentime' => $restaurant->opentime ?? '',
                'closetime' => $restaurant->closetime ?? ''
            ];

            // Load story data if exists
            $story = DB::table('story')->where('vendor_id', $restaurant->id)->first();
            if ($story) {
                // Parse video_url from comma-separated string to array
                $videoUrls = [];
                if (!empty($story->video_url)) {
                    $videoUrls = explode(',', $story->video_url);
                    $videoUrls = array_filter(array_map('trim', $videoUrls));
                }
                $restaurantData['story'] = [
                    'videoThumbnail' => $story->video_thumbnail ?? '',
                    'videoUrl' => $videoUrls
                ];
            } else {
                $restaurantData['story'] = [
                    'videoThumbnail' => '',
                    'videoUrl' => []
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $restaurantData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching restaurant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching restaurant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update restaurant data
     */
    public function updateRestaurant(Request $request, $id)
    {
        try {
            // Try to find by string ID column first, then by numeric primary key
            $restaurant = Vendor::where('id', $id)->first();

            if (!$restaurant && is_numeric($id)) {
                $restaurant = Vendor::find($id);
            }

            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found with ID: ' . $id
                ], 404);
            }

            // Helper function to convert boolean values
            $toBool = function($value) {
                if (is_bool($value)) return $value ? 1 : 0;
                if (is_string($value)) {
                    $value = strtolower($value);
                    return ($value === 'true' || $value === '1') ? 1 : 0;
                }
                return $value ? 1 : 0;
            };

            // Helper function to handle JSON encoding
            $toJson = function($value, $default = []) {
                if (!isset($value)) return json_encode($default);
                return is_string($value) ? $value : json_encode($value);
            };

            // Update restaurant fields
            if ($request->has('title')) $restaurant->title = $request->title;
            if ($request->has('description')) $restaurant->description = $request->description;
            if ($request->has('location')) $restaurant->location = $request->location;
            if ($request->has('latitude')) $restaurant->latitude = floatval($request->latitude);
            if ($request->has('longitude')) $restaurant->longitude = floatval($request->longitude);
            if ($request->has('photo')) $restaurant->photo = $request->photo;
            if ($request->has('photos')) $restaurant->photos = $toJson($request->photos, []);
            if ($request->has('phonenumber')) $restaurant->phonenumber = $request->phonenumber;
            if ($request->has('zoneId')) $restaurant->zoneId = $request->zoneId;
            if ($request->has('categoryID')) $restaurant->categoryID = $toJson($request->categoryID, []);
            if ($request->has('categoryTitle')) $restaurant->categoryTitle = $toJson($request->categoryTitle, []);
            if ($request->has('cuisineID')) $restaurant->cuisineID = $request->cuisineID;
            if ($request->has('vendorCuisineID')) $restaurant->vendorCuisineID = $request->vendorCuisineID;

            // Convert boolean fields properly
            if ($request->has('reststatus')) $restaurant->reststatus = $toBool($request->reststatus);
            if ($request->has('isOpen')) $restaurant->isOpen = $toBool($request->isOpen);
            if ($request->has('specialDiscountEnable')) $restaurant->specialDiscountEnable = $toBool($request->specialDiscountEnable);
            if ($request->has('enabledDiveInFuture')) $restaurant->enabledDiveInFuture = $toBool($request->enabledDiveInFuture);

            if ($request->has('workingHours')) $restaurant->workingHours = $toJson($request->workingHours, []);
            if ($request->has('filters')) $restaurant->filters = $toJson($request->filters, []);
            if ($request->has('vType')) $restaurant->vType = $request->vType;
            if ($request->has('adminCommission')) $restaurant->adminCommission = $toJson($request->adminCommission, []);
            if ($request->has('specialDiscount')) $restaurant->specialDiscount = $toJson($request->specialDiscount, []);
            if ($request->has('restaurantCost')) $restaurant->restaurantCost = $request->restaurantCost;
            if ($request->has('restaurantMenuPhotos')) $restaurant->restaurantMenuPhotos = $toJson($request->restaurantMenuPhotos, []);
            if ($request->has('openDineTime')) $restaurant->openDineTime = $request->openDineTime;
            if ($request->has('closeDineTime')) $restaurant->closeDineTime = $request->closeDineTime;

            $restaurant->save();

            // Save story data if provided
            if ($request->has('storyData') && !empty($request->storyData)) {
                $storyData = $request->storyData;
                $videoThumbnail = $storyData['thumbnail'] ?? '';
                $videoUrls = $storyData['videos'] ?? [];

                if (!empty($videoThumbnail) || !empty($videoUrls)) {
                    // Convert array to comma-separated string
                    $videoUrlString = !empty($videoUrls) ? implode(',', $videoUrls) : null;

                    $firestoreId = 'story_' . Str::uuid()->toString();

                    DB::table('story')->updateOrInsert(
                        ['vendor_id' => $restaurant->id],
                        [
                            'firestore_id' => $firestoreId,
                            'vendor_id' => $restaurant->id,
                            'video_thumbnail' => $videoThumbnail,
                            'video_url' => $videoUrlString,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                } else {
                    // Delete story if both thumbnail and videos are empty
                    DB::table('story')->where('vendor_id', $restaurant->id)->delete();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Restaurant updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating restaurant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating restaurant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle restaurant status
     */
    public function toggleRestaurantStatus($id)
    {
        try {
            // Try to find by string ID column first, then by numeric primary key
            $restaurant = Vendor::where('id', $id)->first();

            if (!$restaurant && is_numeric($id)) {
                $restaurant = Vendor::find($id);
            }

            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found with ID: ' . $id
                ], 404);
            }

            $current = $this->toBoolInt($restaurant->reststatus);
            $restaurant->reststatus = $current ? 0 : 1;
            $restaurant->save();

            return response()->json([
                'success' => true,
                'reststatus' => (bool) $restaurant->reststatus
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling restaurant status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error toggling restaurant status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle restaurant open/close status
     */
    public function toggleRestaurantOpenStatus($id)
    {
        try {
            // Try to find by string ID column first, then by numeric primary key
            $restaurant = Vendor::where('id', $id)->first();

            if (!$restaurant && is_numeric($id)) {
                $restaurant = Vendor::find($id);
            }

            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found with ID: ' . $id
                ], 404);
            }

            $current = $this->toBoolInt($restaurant->isOpen);
            $restaurant->isOpen = $current ? 0 : 1;
            $restaurant->save();

            return response()->json([
                'success' => true,
                'isOpen' => (bool) $restaurant->isOpen
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling restaurant open status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error toggling restaurant open status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle restaurant best status
     * Enforces maximum 9 best restaurants per zone
     */
    public function toggleRestaurantBestStatus($id)
    {
        try {
            // Try to find by string ID column first, then by numeric primary key
            $restaurant = Vendor::where('id', $id)->first();

            if (!$restaurant && is_numeric($id)) {
                $restaurant = Vendor::find($id);
            }

            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found with ID: ' . $id
                ], 404);
            }

            $currentBest = $this->toBoolInt($restaurant->best);
            $newBestStatus = $currentBest ? 0 : 1;

            // If trying to set as best, check zone limit
            if ($newBestStatus == 1) {
                $zoneId = $restaurant->zoneId;

                // Count current best restaurants in the same zone
                $bestCount = Vendor::where('zoneId', $zoneId)
                    ->where('best', 1)
                    ->where('id', '!=', $restaurant->id) // Exclude current restaurant
                    ->count();

                // Maximum 9 best restaurants per zone
                if ($bestCount >= 9) {
                    $zoneName = 'Unknown Zone';
                    if ($zoneId) {
                        $zone = DB::table('zone')->where('id', $zoneId)->first();
                        if ($zone) {
                            $zoneName = $zone->name;
                        }
                    }

                    return response()->json([
                        'success' => false,
                        'message' => "Cannot set as best. Maximum 9 best restaurants allowed per zone. Zone '{$zoneName}' already has 9 best restaurants.",
                        'best_count' => $bestCount,
                        'max_allowed' => 9
                    ], 422);
                }
            }

            // Update best status
            $restaurant->best = $newBestStatus;
            $restaurant->save();

            return response()->json([
                'success' => true,
                'best' => (bool) $restaurant->best,
                'message' => $newBestStatus ? 'Restaurant marked as best' : 'Restaurant unmarked as best'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling restaurant best status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error toggling restaurant best status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle restaurant GST status (accept/unaccept)
     */
    public function toggleRestaurantGstStatus($id)
    {
        try {
            $restaurant = Vendor::find($id);

            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }

            // ✅ ONLY toggle GST flag
            $restaurant->gst = $restaurant->gst ? 0 : 1;
            $restaurant->save();

            // ✅ RE-CALCULATE PRICE (GST + COMMISSION)
            $this->applySubscriptionCommission($restaurant->id);

            return response()->json([
                'success' => true,
                'gst' => (bool) $restaurant->gst,
                'message' => $restaurant->gst
                    ? 'GST accepted – price recalculated'
                    : 'GST unaccepted – price recalculated'
            ]);

        } catch (\Throwable $e) {
            \Log::error('GST Toggle Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function applySubscriptionCommission($vendorId)
    {
        try {
            $vendor = Vendor::find($vendorId);

            if (!$vendor) {
                return false;
            }

            // ---------- CONFIG ----------
            $gstPercent = 5;
            $defaultCommission = 30;

            // ---------- COMMISSION ----------
            $commission = 0; // default = NO commission

            if (empty($vendor->subscriptionPlanId)) {
                // ✅ NO PLAN → Commission Plan
                $commission = $defaultCommission;
            } else {
                $plan = DB::table('subscription_plans')
                    ->where('id', $vendor->subscriptionPlanId)
                    ->select('name')
                    ->first();

                if ($plan && stripos($plan->name, 'commission') !== false) {
                    // ✅ Commission Plan
                    $commission = $defaultCommission;
                }
            }

            // ---------- GST ----------
            // gst = 0 → GST NOT ACCEPTED → apply GST
            $applyGst = !$vendor->gst;

            // ---------- FINAL PRICE ----------
            DB::table('vendor_products')
                ->where('vendorID', $vendor->id)
                ->update([
                    'price' => DB::raw("
                    CASE
                        WHEN MOD(
                            merchant_price
                            + " . ($applyGst ? "(merchant_price * $gstPercent / 100)" : "0") . "
                            + (merchant_price * $commission / 100),
                            1
                        ) = 0.5
                        THEN FLOOR(
                            merchant_price
                            + " . ($applyGst ? "(merchant_price * $gstPercent / 100)" : "0") . "
                            + (merchant_price * $commission / 100)
                        )
                        ELSE ROUND(
                            merchant_price
                            + " . ($applyGst ? "(merchant_price * $gstPercent / 100)" : "0") . "
                            + (merchant_price * $commission / 100)
                        )
                    END
                ")
                ]);

            return true;

        } catch (\Throwable $e) {
            \Log::error('Price Recalculation Error', [
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Toggle restaurant publish status
     */
    public function toggleRestaurantPublishStatus($id)
    {
        try {
            // Try to find by string ID column first, then by numeric primary key
            $restaurant = Vendor::where('id', $id)->first();

            if (!$restaurant && is_numeric($id)) {
                $restaurant = Vendor::find($id);
            }

            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found with ID: ' . $id
                ], 404);
            }

            $currentPublish = $this->toBoolInt($restaurant->publish);
            $newPublishStatus = $currentPublish ? 0 : 1;

            // Update publish status
            $restaurant->publish = $newPublishStatus;
            $restaurant->save();

            return response()->json([
                'success' => true,
                'publish' => (bool) $restaurant->publish,
                'message' => $newPublishStatus ? 'Restaurant published' : 'Restaurant unpublished'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling restaurant publish status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error toggling restaurant publish status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete restaurant
     */
    public function deleteRestaurant($id)
    {
        try {
            // Try to find by string ID column first, then by numeric primary key
            $restaurant = Vendor::where('id', $id)->first();

            if (!$restaurant && is_numeric($id)) {
                $restaurant = Vendor::find($id);
            }

            if (!$restaurant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found with ID: ' . $id
                ], 404);
            }

            // Delete related local records if necessary (customers/vendors/users relations)
            $this->cleanupRestaurantRelations($restaurant->id, $restaurant->author);

            $restaurant->delete();

            return response()->json([
                'success' => true,
                'message' => 'Restaurant deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting restaurant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting restaurant: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'string'
        ]);

        $ids = $request->input('ids', []);

        try {
            DB::beginTransaction();

            foreach ($ids as $id) {
                $restaurant = Vendor::where('id', $id)->first();

                if (!$restaurant && is_numeric($id)) {
                    $restaurant = Vendor::find($id);
                }

                if ($restaurant) {
                    $this->cleanupRestaurantRelations($restaurant->id, $restaurant->author);
                    $restaurant->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Selected restaurants deleted successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk delete restaurants error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error deleting restaurants: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCloneData($id)
    {
        $vendor = Vendor::find($id);

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found with ID: ' . $id
            ], 404);
        }

        $owner = $this->findVendorOwner($vendor);

        return response()->json([
            'success' => true,
            'data' => [
                'vendor' => [
                    'id' => $vendor->id,
                    'title' => $vendor->title,
                ],
                'owner' => $owner ? [
                    'id' => $owner->id,
                    'firebase_id' => $owner->firebase_id,
                    'firstName' => $owner->firstName,
                    'lastName' => $owner->lastName,
                    'email' => $owner->email,
                ] : null,
            ],
        ]);
    }

    public function cloneRestaurant(Request $request)
    {
        $validated = $request->validate([
            'source_vendor_id' => 'required|string',
            'vendor_title' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $sourceVendor = Vendor::find($validated['source_vendor_id']);

        if (!$sourceVendor) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found with ID: ' . $validated['source_vendor_id']
            ], 404);
        }

        if (AppUser::where('email', $validated['email'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists for another user.'
            ], 422);
        }

        $sourceOwner = $this->findVendorOwner($sourceVendor);

        try {
            DB::beginTransaction();

            $firebaseId = (string) Str::uuid();
            $currentTimestamp = '"' . gmdate('Y-m-d\TH:i:s.u\Z') . '"';

            $newUser = new AppUser();
            $newUser->firstName = $validated['first_name'];
            $newUser->lastName = $validated['last_name'];
            $newUser->email = $validated['email'];
            $newUser->password = Hash::make($validated['password']);
            $newUser->phoneNumber = $sourceOwner->phoneNumber ?? null;
            $newUser->countryCode = $sourceOwner->countryCode ?? '';
            $newUser->role = 'vendor';
            $newUser->vType = $sourceVendor->vType ?? 'restaurant';
            $newUser->active = 1;
            $newUser->profilePictureURL = $sourceOwner->profilePictureURL ?? '';
            $newUser->provider = $sourceOwner->provider ?? 'email';
            $newUser->appIdentifier = $sourceOwner->appIdentifier ?? 'web';
            $newUser->isDocumentVerify = $sourceOwner->isDocumentVerify ?? 0;
            $newUser->createdAt = $currentTimestamp;
            $newUser->wallet_amount = 0;
            $newUser->firebase_id = $firebaseId;
            $newUser->_id = $firebaseId;
            $newUser->vendorID = null;
            $newUser->save();

            $newVendorId = $this->generateVendorId();

            /** @var \App\Models\Vendor $newVendor */
            $newVendor = $sourceVendor->replicate();
            $newVendor->id = $newVendorId;
            $newVendor->title = $validated['vendor_title'];
            $newVendor->author = $firebaseId ?: (string) ($newUser->id);
            $newVendor->authorName = trim($validated['first_name'] . ' ' . $validated['last_name']);
            $newVendor->authorProfilePic = $sourceOwner->profilePictureURL ?? $sourceVendor->authorProfilePic ?? '';
            $newVendor->createdAt = $currentTimestamp;
            $newVendor->subscriptionTotalOrders = $sourceVendor->subscriptionTotalOrders ?? null;
            $newVendor->save();

            $newUser->vendorID = $newVendorId;
            $newUser->save();

            $products = VendorProduct::where('vendorID', $sourceVendor->id)->get();
            foreach ($products as $product) {
                /** @var \App\Models\VendorProduct $productClone */
                $productClone = $product->replicate();
                $productClone->id = $this->generateVendorProductId();
                $productClone->vendorID = $newVendorId;
                if (isset($productClone->vendorTitle)) {
                    $productClone->vendorTitle = $validated['vendor_title'];
                }
                if (isset($productClone->createdAt)) {
                    $productClone->createdAt = $currentTimestamp;
                }
                if (isset($productClone->updatedAt)) {
                    $productClone->updatedAt = $currentTimestamp;
                }
                if (isset($productClone->updated_at)) {
                    $productClone->updated_at = now();
                }
                $productClone->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Restaurant cloned successfully.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Error cloning restaurant: ' . $e->getMessage(), [
                'source_vendor_id' => $validated['source_vendor_id'],
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error cloning restaurant: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updateSubscriptionLimits(Request $request, $id)
    {
        $validated = $request->validate([
            'order_limit' => 'required|string',
            'item_limit' => 'required|string',
        ]);

        $vendor = Vendor::find($id);
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found with ID: ' . $id
            ], 404);
        }

        $owner = $this->findVendorOwner($vendor);
        if (!$owner) {
            return response()->json([
                'success' => false,
                'message' => 'Owner not found for this restaurant.'
            ], 404);
        }

        if (empty($vendor->subscription_plan)) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found for this restaurant.'
            ], 422);
        }

        $vendorPlan = json_decode($vendor->subscription_plan, true) ?: [];
        $ownerPlan = json_decode($owner->subscription_plan ?? '[]', true) ?: [];

        $vendorPlan['orderLimit'] = $validated['order_limit'];
        $vendorPlan['itemLimit'] = $validated['item_limit'];
        $ownerPlan['orderLimit'] = $validated['order_limit'];
        $ownerPlan['itemLimit'] = $validated['item_limit'];

        $vendor->subscription_plan = json_encode($vendorPlan);
        $vendor->subscriptionTotalOrders = $validated['order_limit'];
        $vendor->save();

        if (!empty($owner->subscription_plan)) {
            $owner->subscription_plan = json_encode($ownerPlan);
            $owner->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Subscription limits updated successfully.'
        ]);
    }

    private function cleanupRestaurantRelations(string $restaurantId, ?string $authorId): void
    {
        try {
            if ($authorId) {
                AppUser::where('id', $authorId)->update(['vendorID' => null]);
                DB::table('users')->where('id', $authorId)->update(['vendorID' => null]);
            }

            DB::table('wallet')->where('user_id', $authorId)->delete();
            DB::table('vendor_products')->where('vendorID', $restaurantId)->delete();
            DB::table('foods_review')->where('VendorId', $restaurantId)->delete();
            DB::table('favorite_restaurant')->where('restaurant_id', $restaurantId)->delete();
            DB::table('payouts')->where('vendorID', $restaurantId)->delete();
            DB::table('booked_table')->where('vendorID', $restaurantId)->delete();
            DB::table('story')->where('vendorID', $restaurantId)->delete();
            DB::table('favorite_item')->where('store_id', $restaurantId)->delete();
            DB::table('mart_items')->where('vendorID', $restaurantId)->delete();
        } catch (\Exception $e) {
            \Log::warning("Error while cleaning up relations for restaurant {$restaurantId}: {$e->getMessage()}");
        }
    }

    private function findVendorOwner(Vendor $vendor): ?AppUser
    {
        if (!empty($vendor->author)) {
            $owner = AppUser::where('firebase_id', $vendor->author)
                ->orWhere('_id', $vendor->author)
                ->orWhere('id', $vendor->author)
                ->first();

            if ($owner) {
                return $owner;
            }
        }

        return AppUser::where('vendorID', $vendor->id)->first();
    }

    private function generateVendorId(): string
    {
        do {
            $id = 'rest_' . Str::uuid()->toString();
        } while (Vendor::where('id', $id)->exists());

        return $id;
    }

    private function generateVendorProductId(): string
    {
        do {
            $id = 'product_' . Str::uuid()->toString();
        } while (VendorProduct::where('id', $id)->exists());

        return $id;
    }

    /**
     * @param  object|array  $plan
     */
    private function normalizePlanData($plan): array
    {
        $planArray = is_array($plan) ? $plan : (array) $plan;

        foreach (['features', 'plan_points', 'planPoints'] as $field) {
            if (isset($planArray[$field]) && is_string($planArray[$field])) {
                $decoded = json_decode($planArray[$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $planArray[$field] = $decoded;
                }
            }
        }

        return $planArray;
    }

    /**
     * Get categories for restaurant
     */
    public function getCategories()
    {
        try {
            $categories = DB::table('vendor_categories')
                ->orderBy('title', 'asc')
                ->select('id', 'title', 'photo')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cuisines for restaurant
     */
    public function getCuisines()
    {
        try {
            $cuisines = DB::table('vendor_cuisines')
                ->orderBy('title', 'asc')
                ->select('id', 'title', 'photo')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cuisines
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching cuisines: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get restaurant statistics for view page
     */
    public function getRestaurantStats($id)
    {
        try {
            // Get total orders
            $totalOrders = DB::table('restaurant_orders')
                ->where('vendorID', $id)
                ->count();

            // Get total earnings (from completed orders)
            $completedOrders = DB::table('restaurant_orders')
                ->where('vendorID', $id)
                ->where('status', 'Order Completed')
                ->get();

            $totalEarnings = 0;
            foreach ($completedOrders as $order) {
                $discount = $order->discount ?? 0;
                $deliveryCharge = $order->deliveryCharge ?? 0;
                $tip = $order->tip ?? 0;
                $taxAmount = $order->tax ?? 0;

                $orderTotal = ($discount + $deliveryCharge + $tip + $taxAmount);
                $totalEarnings += $orderTotal;
            }

            // Get total payments (from payouts)
            $totalPayments = DB::table('payouts')
                ->where('vendorID', $id)
                ->where('paidDate', '!=', null)
                ->sum('amount');

            // Get remaining balance
            $remainingBalance = $totalEarnings - $totalPayments;

            return response()->json([
                'success' => true,
                'totalOrders' => $totalOrders,
                'totalEarnings' => $totalEarnings,
                'totalPayments' => $totalPayments,
                'remainingBalance' => $remainingBalance
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching restaurant stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching restaurant stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user by ID for restaurant view
     */
    public function getUserById($id)
    {
        try {
            $user = DB::table('users')
                ->where('id', $id)
                ->orWhere('firebase_id', $id)
                ->orWhere('_id', $id)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching user: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add wallet amount
     */
    public function addWalletAmount(Request $request)
    {
        try {
            $userId = $request->user_id;
            $amount = floatval($request->amount);
            $note = $request->note ?? '';

            // Get user
            $user = DB::table('users')
                ->where('id', $userId)
                ->orWhere('firebase_id', $userId)
                ->orWhere('_id', $userId)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Calculate new wallet amount
            $currentWalletAmount = floatval($user->wallet_amount ?? 0);
            $newWalletAmount = $currentWalletAmount + $amount;

            // Update user wallet
            DB::table('users')
                ->where('id', $user->id)
                ->update(['wallet_amount' => $newWalletAmount]);

            // Create wallet transaction
            $walletId = 'wallet_' . time() . '_' . uniqid();
            DB::table('wallet')->insert([
                'id' => $walletId,
                'user_id' => $user->id,
                'amount' => $amount,
                'date' => now(),
                'isTopUp' => 1,
                'order_id' => '',
                'payment_method' => 'Wallet',
                'payment_status' => 'success',
                'note' => $note,
                'transactionUser' => 'vendor',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Wallet amount added successfully',
                'newWalletAmount' => $newWalletAmount,
                'transaction_id' => $walletId,
                'user' => [
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'email' => $user->email
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error adding wallet amount: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding wallet amount: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet balance
     */
    public function getWalletBalance($userId)
    {
        try {
            $user = DB::table('users')
                ->where('id', $userId)
                ->orWhere('firebase_id', $userId)
                ->orWhere('_id', $userId)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $walletBalance = floatval($user->wallet_amount ?? 0);

            return response()->json([
                'success' => true,
                'wallet_balance' => $walletBalance
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching wallet balance: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching wallet balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subscription history for user
     */
    public function getSubscriptionHistory($userId)
    {
        try {
            $history = DB::table('subscription_history')
                ->where('user_id', $userId)
                ->orderBy('createdAt', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'history' => $history
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching subscription history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching subscription history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get email template by type
     */
    public function getEmailTemplate($type)
    {
        try {
            $template = DB::table('email_templates')
                ->where('type', $type)
                ->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email template not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'template' => $template
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching email template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching email template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get vendor categories for API
     */
    public function getVendorCategories()
    {
        try {
            $categories = DB::table('vendor_categories')
                ->where('publish', true)
                ->orderBy('title', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching vendor categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching vendor categories: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get vendor document list data (SQL API)
     */
    public function getVendorDocumentData($id)
    {
        try {
            // Get vendor info
            $vendor = AppUser::where('firebase_id', $id)
                ->orWhere('id', $id)
                ->orWhere('_id', $id)
                ->where('role', 'vendor')
                ->first();

            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vendor not found'
                ], 404);
            }

            // Get enabled documents for restaurant type
            $documents = DB::table('documents')
                ->where('enable', true)
                ->where('type', 'restaurant')
                ->orderBy('title', 'asc')
                ->get();

            // Get vendor's document verifications
            $docVerify = DB::table('documents_verify')
                ->where('id', $id)
                ->first();

            $documentsData = [];
            if ($docVerify && $docVerify->documents) {
                $documentsData = json_decode($docVerify->documents, true) ?: [];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'vendor' => [
                        'id' => $vendor->firebase_id ?? $vendor->_id ?? $vendor->id,
                        'firstName' => $vendor->firstName,
                        'lastName' => $vendor->lastName,
                        'fcmToken' => $vendor->fcmToken ?? ''
                    ],
                    'documents' => $documents,
                    'documentVerifications' => $documentsData
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching vendor document data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching vendor document data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update document status (approve/reject) - SQL API
     */
    public function updateDocumentStatus(Request $request, $vendorId, $docId)
    {
        try {
            $request->validate([
                'status' => 'required|in:approved,rejected'
            ]);

            $status = $request->input('status');

            // Get current document verification
            $docVerify = DB::table('documents_verify')
                ->where('id', $vendorId)
                ->first();

            $documents = [];
            if ($docVerify && $docVerify->documents) {
                $documents = json_decode($docVerify->documents, true) ?: [];
            }

            // Find and update the document status
            $found = false;
            foreach ($documents as &$doc) {
                if ($doc['documentId'] == $docId) {
                    $doc['status'] = $status;
                    $found = true;
                    break;
                }
            }

            // If document not found in verification, add it
            if (!$found) {
                $documents[] = [
                    'documentId' => $docId,
                    'status' => $status,
                    'frontImage' => '',
                    'backImage' => ''
                ];
            }

            // Update or insert document verification
            if ($docVerify) {
                DB::table('documents_verify')
                    ->where('id', $vendorId)
                    ->update([
                        'documents' => json_encode($documents),
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('documents_verify')->insert([
                    'id' => $vendorId,
                    'documents' => json_encode($documents),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Update vendor verification status
            $this->updateVendorVerificationStatus($vendorId);

            return response()->json([
                'success' => true,
                'message' => 'Document status updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating document status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating document status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document details for upload page (SQL API)
     */
    public function getDocumentUploadData($vendorId, $docId)
    {
        try {
            // Get document details
            $document = DB::table('documents')
                ->where('id', $docId)
                ->where('enable', true)
                ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found or disabled'
                ], 404);
            }

            // Get vendor's document verification for this document
            $docVerify = DB::table('documents_verify')
                ->where('id', $vendorId)
                ->first();

            $documentVerification = null;
            $keydata = -1;

            if ($docVerify && $docVerify->documents) {
                $documents = json_decode($docVerify->documents, true) ?: [];
                foreach ($documents as $index => $doc) {
                    if (isset($doc['documentId']) && $doc['documentId'] == $docId) {
                        $documentVerification = $doc;
                        $keydata = $index;
                        break;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'document' => $document,
                    'documentVerification' => $documentVerification,
                    'keydata' => $keydata,
                    'isAdd' => $documentVerification === null
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching document upload data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching document upload data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload/save vendor document (SQL API)
     */
    public function uploadVendorDocument(Request $request, $vendorId, $docId)
    {
        try {

            $request->validate([
                'frontImage' => 'nullable|string', // Base64 image data
                'backImage' => 'nullable|string', // Base64 image data
                'frontImageUrl' => 'nullable|string', // Existing URL
                'backImageUrl' => 'nullable|string', // Existing URL
                'frontSide' => 'nullable|string',
                'backSide' => 'nullable|string',
            ]);

            // Get document details
            $document = DB::table('documents')
                ->where('id', $docId)
                ->where('enable', true)
                ->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found or disabled'
                ], 404);
            }

            // Handle file uploads
            $frontImageUrl = $request->input('frontImageUrl', '');
            $backImageUrl = $request->input('backImageUrl', '');

            // Upload front image if provided as base64 (new upload)
            if ($request->has('frontImage') && $request->input('frontImage') && !empty($request->input('frontImage'))) {
                try {
                    $frontImageUrl = $this->uploadBase64Image($request->input('frontImage'), 'vendor_documents', $vendorId . '_' . $docId . '_front_' . time());
                } catch (\Exception $e) {
                    \Log::error('Error uploading front image: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error uploading front image: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Upload back image if provided as base64 (new upload)
            if ($request->has('backImage') && $request->input('backImage') && !empty($request->input('backImage'))) {
                try {
                    $backImageUrl = $this->uploadBase64Image($request->input('backImage'), 'vendor_documents', $vendorId . '_' . $docId . '_back_' . time());
                } catch (\Exception $e) {
                    \Log::error('Error uploading back image: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Error uploading back image: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Get current document verification
            $docVerify = DB::table('documents_verify')
                ->where('id', $vendorId)
                ->first();

            $documents = [];
            $keydata = -1;

            if ($docVerify && $docVerify->documents) {
                $documents = json_decode($docVerify->documents, true) ?: [];
            }

            // Find existing document or create new
            $found = false;
            foreach ($documents as $index => &$doc) {
                if (isset($doc['documentId']) && $doc['documentId'] == $docId) {
                    // Update existing - only update if new URL provided
                    if ($document->frontSide) {
                        if ($frontImageUrl) {
                            $doc['frontImage'] = $frontImageUrl;
                        } else {
                            // Keep existing if no new upload
                            $doc['frontImage'] = $doc['frontImage'] ?? '';
                        }
                    }
                    if ($document->backSide) {
                        if ($backImageUrl) {
                            $doc['backImage'] = $backImageUrl;
                        } else {
                            // Keep existing if no new upload
                            $doc['backImage'] = $doc['backImage'] ?? '';
                        }
                    }
                    $doc['status'] = 'uploaded';
                    $found = true;
                    $keydata = $index;
                    break;
                }
            }

            // Add new document if not found
            if (!$found) {
                $newDoc = [
                    'documentId' => $docId,
                    'status' => 'uploaded'
                ];
                if ($document->frontSide) {
                    $newDoc['frontImage'] = $frontImageUrl ?: '';
                }
                if ($document->backSide) {
                    $newDoc['backImage'] = $backImageUrl ?: '';
                }
                $documents[] = $newDoc;
                $keydata = count($documents) - 1;
            }

            // Update or insert document verification
            if ($docVerify) {
                DB::table('documents_verify')
                    ->where('id', $vendorId)
                    ->update([
                        'documents' => json_encode($documents),
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('documents_verify')->insert([
                    'id' => $vendorId,
                    'documents' => json_encode($documents),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Update vendor verification status
            $this->updateVendorVerificationStatus($vendorId);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error uploading vendor document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload base64 image or video to storage
     */
    private function uploadBase64Image($base64Data, $folder = 'vendor_documents', $filename = null)
    {
        try {
            // Detect if it's a video or image based on data URL prefix
            $isVideo = false;
            $fileExtension = null;

            // Check for video data URL prefix (e.g., data:video/mp4;base64,)
            if (preg_match('/^data:video\/([a-z0-9]+);base64,/', $base64Data, $matches)) {
                $isVideo = true;
                $fileExtension = $matches[1]; // Extract video format (mp4, webm, etc.)
                $base64Data = preg_replace('/^data:video\/[a-z0-9]+;base64,/', '', $base64Data);
            }
            // Check for image data URL prefix (e.g., data:image/jpeg;base64,)
            elseif (preg_match('/^data:image\/([a-z]+);base64,/', $base64Data, $matches)) {
                $fileExtension = $matches[1]; // Extract image format (jpeg, png, gif, etc.)
                $base64Data = preg_replace('/^data:image\/[a-z]+;base64,/', '', $base64Data);
            }
            // Fallback: try to remove any data URL prefix
            else {
                $base64Data = preg_replace('/^data:[^;]+;base64,/', '', $base64Data);
            }

            // Decode base64
            $fileData = base64_decode($base64Data);

            if (!$fileData) {
                throw new \Exception('Invalid base64 data');
            }

            // Generate filename if not provided
            if (!$filename) {
                $filename = uniqid() . '_' . time();
            }

            // Preserve existing extension if present, otherwise add appropriate extension
            if (!preg_match('/\.(jpg|jpeg|png|gif|mp4|webm|mov|avi)$/i', $filename)) {
                if ($isVideo && $fileExtension) {
                    $filename .= '.' . $fileExtension;
                } elseif ($fileExtension) {
                    // For images, use the detected extension or default to jpg
                    $filename .= '.' . ($fileExtension === 'jpeg' ? 'jpg' : $fileExtension);
                } else {
                    // Default to jpg for images if no extension detected
                    $filename .= '.jpg';
                }
            }

            // Save file using Storage
            $path = $folder . '/' . $filename;
            Storage::disk('public')->put($path, $fileData);

            // Return public URL using asset() to ensure proper domain
            return asset('storage/' . $path);
        } catch (\Exception $e) {
            \Log::error('Error uploading base64 file: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generic image/video upload API endpoint (handles base64 encoded files)
     */
    public function uploadImage(Request $request)
    {
        try {
            $request->validate([
                'image' => 'required|string',
                'folder' => 'nullable|string',
                'filename' => 'nullable|string'
            ]);

            $folder = $request->input('folder', 'uploads');
            $filename = $request->input('filename', null);

            $url = $this->uploadBase64Image($request->input('image'), $folder, $filename);

            return response()->json([
                'success' => true,
                'url' => $url
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in image upload API: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update vendor document verification status
     */
    private function updateVendorVerificationStatus($vendorId)
    {
        try {
            // Get enabled documents count
            $enabledDocCount = DB::table('documents')
                ->where('enable', true)
                ->where('type', 'restaurant')
                ->count();

            // Get approved documents count
            $docVerify = DB::table('documents_verify')
                ->where('id', $vendorId)
                ->first();

            $approvedCount = 0;
            if ($docVerify && $docVerify->documents) {
                $documents = json_decode($docVerify->documents, true) ?: [];
                $approvedCount = count(array_filter($documents, function($doc) {
                    return isset($doc['status']) && $doc['status'] == 'approved';
                }));
            }

            // Update vendor verification status
            $isDocumentVerify = ($approvedCount >= $enabledDocCount) && $enabledDocCount > 0;

            AppUser::where('firebase_id', $vendorId)
                ->orWhere('id', $vendorId)
                ->orWhere('_id', $vendorId)
                ->where('role', 'vendor')
                ->update(['isDocumentVerify' => $isDocumentVerify ? 1 : 0]);
        } catch (\Exception $e) {
            \Log::error('Error updating vendor verification status: ' . $e->getMessage());
        }
    }

    /**
     * Lightweight Story API for restaurants (used by edit page JS)
     * Returns empty data to avoid breaking the page if story feature is unused.
     */
    public function getRestaurantStory($id)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'videoUrl' => [],
                    'videoThumbnail' => ''
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching restaurant story: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching story'
            ], 500);
        }
    }

    public function upsertRestaurantStory(Request $request, $id)
    {
        try {
            // Accept payload and return success (no-op storage for now)
            return response()->json([
                'success' => true,
                'message' => 'Story saved'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving restaurant story: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving story'
            ], 500);
        }
    }

    public function deleteRestaurantStory($id)
    {
        try {
            // No-op delete for now
            return response()->json([
                'success' => true,
                'message' => 'Story deleted'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting restaurant story: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting story'
            ], 500);
        }
    }
    public function uploadVideo(Request $request)
    {
        $file = $request->file('file');
        $folder = $request->folder ?? 'restaurants/stories/videos';

        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs($folder, $filename, 'public');

        return response()->json([
            'url' => asset('storage/' . $path)
        ]);
    }

    /**
     * Get subscription plans for API
     * Returns all enabled plans from subscription_plans table (isEnable = 1 or true)
     * Commission plan is only included if it's enabled
     */
    public function getSubscriptionPlansAPI()
    {
        try {
            // Fetch all enabled plans using the model scope (isEnable = 1)
            // This includes commission plan only if it's enabled
            $plans = SubscriptionPlan::enabled()
                ->orderBy('name', 'asc')
                ->get();

            \Log::info('Fetched subscription plans', [
                'count' => $plans->count(),
                'plan_ids' => $plans->pluck('id')->toArray(),
                'plan_names' => $plans->pluck('name')->toArray()
            ]);

            return response()->json([
                'success' => true,
                'data' => $plans,
                'count' => $plans->count()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching subscription plans: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error fetching subscription plans: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }
    public function assignSubscription(Request $request, $id)
    {
        $request->validate([
            'plan_id' => 'required|string',
            'bill_status' => 'required|in:paid,unpaid',
        ]);

        // 1. Find Vendor / Restaurant
        $vendor = Vendor::find($id);
        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant not found'
            ], 404);
        }

        // 2. Get subscription plan directly from subscription_plans table
        $plan = DB::table('subscription_plans')
            ->where('id', $request->plan_id)
            ->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or disabled subscription plan. Only enabled plans can be assigned.'
            ], 404);
        }

        // 3. Calculate expiry date
        $expiryDate = null;
        if (!empty($plan->expiryDay) && (int)$plan->expiryDay !== -1) {
            $expiryDate = Carbon::now()->addDays((int)$plan->expiryDay);
        }

        // 4. Save plan ID and bill_status in vendors table
        DB::beginTransaction();
        try {
            $vendor->subscriptionPlanId = $plan->id;
            $vendor->subscription_plan = json_encode($plan);
            $vendor->subscriptionExpiryDate = $expiryDate;

            // Save bill_status (paid or unpaid)
            if ($request->has('bill_status') && in_array($request->bill_status, ['paid', 'unpaid'])) {
                $vendor->bill_status = $request->bill_status;
            }

            $vendor->save();

            // 5. Save history (optional but recommended)
            DB::table('subscription_history')->insert([
                'id' => 'subscription_' . Str::uuid(),
                'user_id' => $vendor->author,
                'subscription_plan' => json_encode($plan),
                'expiry_date' => $expiryDate,
                'createdAt' => now(),
                'payment_type' => 'Manual Pay',
            ]);

            // 6. Automatically apply commission from subscription plan to vendor products
            $this->applySubscriptionCommission($vendor->id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan assigned successfully'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Subscription assign error', [
                'vendor_id' => $id,
                'plan_id' => $request->plan_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign subscription'
            ], 500);
        }
    }

    /**
     * Remove subscription plan from restaurant
     */
    public function removeSubscription($id)
    {
        try {
            // Find Vendor / Restaurant
            $vendor = Vendor::find($id);
            if (!$vendor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Restaurant not found'
                ], 404);
            }

            DB::beginTransaction();

            // Clear subscription plan from vendor
            $vendor->subscriptionPlanId = null;
            $vendor->subscription_plan = null;
            $vendor->subscriptionExpiryDate = null;
            $vendor->subscriptionTotalOrders = null;
            $vendor->save();

            // Clear bill_status using direct DB update (handles missing column gracefully)
            try {
                DB::table('vendors')->where('id', $vendor->id)->update(['bill_status' => null]);
            } catch (\Exception $e) {
                // Column might not exist, log and continue
                \Log::warning('Could not clear bill_status from vendors table', [
                    'vendor_id' => $vendor->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Also clear from user if vendor has an author
            if ($vendor->author) {
                $user = AppUser::where('firebase_id', $vendor->author)
                    ->orWhere('_id', $vendor->author)
                    ->orWhere('id', $vendor->author)
                    ->where('role', 'vendor')
                    ->first();

                if ($user) {
                    $user->subscriptionPlanId = null;
                    $user->subscription_plan = null;
                    $user->subscriptionExpiryDate = null;
                    $user->save();

                    // Clear bill_status using direct DB update (handles missing column gracefully)
                    try {
                        DB::table('app_users')->where('id', $user->id)->update(['bill_status' => null]);
                    } catch (\Exception $e) {
                        // Column might not exist, log and continue
                        \Log::warning('Could not clear bill_status from app_users table', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan removed successfully'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Subscription remove error', [
                'vendor_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove subscription: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get subscription plans
     */
    public function getSubscriptionPlans()
    {
        try {
            $plans = SubscriptionPlan::query()
                ->enabled()
                ->orderBy('name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $plans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching subscription plans: ' . $e->getMessage()
            ], 500);
        }
    }
}
