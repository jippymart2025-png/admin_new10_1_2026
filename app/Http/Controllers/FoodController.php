<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\VendorProduct;
use App\Services\ActivityLogger;
use App\Services\FirebaseStorageService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FoodController extends Controller
{
    protected FirebaseStorageService $firebaseStorage;
    public function __construct(FirebaseStorageService $firebaseStorage)
    {
        $this->middleware('auth');
        $this->firebaseStorage = $firebaseStorage;
    }

    public function index($restaurantId = '')
    {
        return view('foods.index', [
            'restaurantId' => $restaurantId,
        ]);
    }

    public function create($restaurantId = '')
    {
        $restaurants = $this->getRestaurants();
        $categories = $this->getCategories();
        $attributes = $this->getAttributes();

        return view('foods.create', [
            'restaurantId' => $restaurantId,
            'restaurants' => $restaurants,
            'categories' => $categories,
            'attributes' => $attributes,
        ]);
    }

    public function createfood()
    {
        return $this->create();
    }

    public function edit($id)
    {
        $food = VendorProduct::findOrFail($id);

        $restaurants = $this->getRestaurants();
        $categories = $this->getCategories();
        $attributes = $this->getAttributes();

        return view('foods.edit', [
            'food' => $food,
            'restaurants' => $restaurants,
            'categories' => $categories,
            'attributes' => $attributes,
        ]);
    }

    protected function getRestaurants()
    {
        return DB::table('vendors')
            ->where('vType', 'restaurant')
            ->whereNotNull('title')
            ->where('title', '!=', '')
            ->orderBy('title')
            ->pluck('title', 'id');
    }

    protected function getCategories()
    {
        return DB::table('vendor_categories')
            ->where('publish', 1)
            ->orderBy('title')
            ->pluck('title', 'id');
    }

    protected function getAttributes()
    {
        return DB::table('vendor_attributes')
            ->orderBy('title')
            ->pluck('title', 'id');
    }

    public function data(Request $request)
    {
        $userPermissions = json_decode(@session('user_permissions'), true) ?: [];
        $canDelete = in_array('foods.delete', $userPermissions);

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));

        $restaurantFilter = $request->input('restaurant');
        $categoryFilter = $request->input('category');
        $foodTypeFilter = $request->input('foodType');
        $restaurantId = $request->input('restaurantId');

        $query = DB::table('vendor_products as vp')
            ->leftJoin('vendors as v', 'v.id', '=', 'vp.vendorID')
            ->leftJoin('vendor_categories as vc', 'vc.id', '=', 'vp.categoryID')
            ->select(
                'vp.id',
                'vp.name',
                'vp.photo',
                'vp.price',
                'vp.merchant_price',
                'vp.disPrice',
                'vp.vendorID',
                'vp.categoryID',
                'vp.description',
                'vp.publish',
                'vp.nonveg',
                'vp.isAvailable',
                'v.title as restaurant_name',
                'vc.title as category_name'
            );

        if ($restaurantId) {
            $query->where('vp.vendorID', $restaurantId);
        }

        if ($restaurantFilter) {
            $query->where('vp.vendorID', $restaurantFilter);
        }

        if ($categoryFilter) {
            $query->where('vp.categoryID', $categoryFilter);
        }

        if ($foodTypeFilter === 'veg') {
            $query->where('vp.nonveg', 0);
        } elseif ($foodTypeFilter === 'non-veg') {
            $query->where('vp.nonveg', 1);
        }


        $total = $query->count();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where(DB::raw('LOWER(vp.name)'), 'like', "%$search%")
                    ->orWhere(DB::raw('LOWER(v.title)'), 'like', "%$search%")
                    ->orWhere(DB::raw('LOWER(vc.title)'), 'like', "%$search%")
                    ->orWhere('vp.price', 'like', "%$search%")
                    ->orWhere('vp.merchant_price', 'like', "%$search%")
                    ->orWhere('vp.vendorID', 'like', "%$search%");
            });
        }

        $recordsFiltered = $query->count();

        $order = $request->input('order.0', ['column' => 1, 'dir' => 'asc']);
        $orderColumnIndex = (int) data_get($order, 'column', 1);
        $orderDir = data_get($order, 'dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $orderableColumns = $canDelete
            ? ['', 'vp.name', 'vp.price', 'vp.merchant_price','vp.disPrice', 'restaurant_name', 'category_name', '', '']
            : ['vp.name', 'vp.price', 'vp.merchant_price', 'vp.disPrice', 'restaurant_name', 'category_name', '', ''];

        $orderBy = $orderableColumns[$orderColumnIndex] ?? 'vp.name';

        if (!empty($orderBy)) {
            $query->orderBy($orderBy, $orderDir);
        }

        $foods = $query->skip($start)->take($length)->get();

        $data = $foods->map(function ($food) {
            return [
                'id' => $food->id,
                'name' => $food->name,
                'photo' => $this->buildPhotoUrl($food->photo),
                'price' => $food->price,
                'merchant_price' => $food->merchant_price !== null ? (float) $food->merchant_price : null,
                'disPrice' => $food->disPrice,
                'vendorID' => $food->vendorID,
                'categoryID' => $food->categoryID,
                'restaurant_name' => $food->restaurant_name,
                'category_name' => $food->category_name,
                'description' => $food->description,
                'publish' => (bool) $food->publish,
                'nonveg' => (bool) $food->nonveg,
                'isAvailable' => (bool) $food->isAvailable,
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    protected function buildPhotoUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://', '//'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public function options(Request $request)
    {
        $type = $request->input('type');

        if ($type === 'restaurants') {
            $restaurants = DB::table('vendors')
                ->where('vType', 'restaurant')
                ->whereNotNull('title')
                ->where('title', '!=', '')
                ->orderBy('title')
                ->get(['id', 'title']);

            return response()->json(['success' => true, 'data' => $restaurants]);
        }

        if ($type === 'categories') {
            $categories = DB::table('vendor_categories')
                ->where('publish', 1)
                ->orderBy('title')
                ->get(['id', 'title']);

            return response()->json(['success' => true, 'data' => $categories]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid type']);
    }

    public function store(Request $request, ActivityLogger $logger)
    {
        $data = $this->validateFood($request);

        $id = Str::uuid()->toString();

//        $photoPath = $this->storeUploadedPhoto($request);

        $vendorTitle = $this->getVendorTitle($data['vendorID']);
        $categoryTitle = $this->getCategoryTitle($data['categoryID']);

        $imageUrl = null;
        if ($request->hasFile('photo')) {
            $imageUrl = $this->firebaseStorage->uploadFile(
                $request->file('photo'),
                'foods/food_' . time() . '.' . $request->file('photo')->getClientOriginalExtension()
            );
        }

        $food = VendorProduct::create([
            'id' => $id,
            'name' => $data['name'],
            'price' => $data['price'],
            'merchant_price' => $data['merchant_price'],
            'disPrice' => $data['disPrice'],
            'description' => $data['description'],
            'vendorID' => $data['vendorID'],
            'vendorTitle' => $vendorTitle,
            'categoryID' => $data['categoryID'],
            'categoryTitle' => $categoryTitle,
            'quantity' => $data['quantity'],
            'publish' => $data['publish'],
            'nonveg' => $data['nonveg'],
            'veg' => !$data['nonveg'],
            'takeawayOption' => $data['takeawayOption'],
            'isAvailable' => $data['isAvailable'],
            'calories' => $data['calories'],
            'grams' => $data['grams'],
            'proteins' => $data['proteins'],
            'fats' => $data['fats'],
            'photo' => $imageUrl,
//            'photos' => $imageUrl ? [$imageUrl] : [],
            'addOnsTitle' => $data['addOnsTitle'],
            'addOnsPrice' => $data['addOnsPrice'],
            'product_specification' => $data['product_specification'],
            'item_attribute' => $data['item_attribute'],
            'variants' => $data['variants'],
            'migratedBy' => 'sql',
            'vType' => 'restaurant',
            'createdAt' => now()->toIso8601String(),
            'updatedAt' => now()->toIso8601String(),
        ]);

        $logger->log(auth()->user(), 'foods', 'created', 'Created food: ' . $data['name'], $request);

        return redirect()->route('foods')
            ->with('success', 'Food created successfully.');
    }

    public function update(Request $request, $id, ActivityLogger $logger)
    {
        $food = VendorProduct::findOrFail($id);

        $data = $this->validateFood($request, true);

//        $photoPath = $food->photo;
        $originalName = $food->name;

        $imageUrl = null;
        if ($request->hasFile('photo')) {
            $imageUrl = $this->firebaseStorage->uploadFile(
                $request->file('photo'),
                'foods/food_' . time() . '.' . $request->file('photo')->getClientOriginalExtension()
            );
        }

        if ($request->boolean('remove_photo')) {
            $this->deleteImage($imageUrl);
            $photoPath = null;
        }

        if ($request->hasFile('photo')) {
            $this->deleteImage($imageUrl);
            $photoPath = $this->storeUploadedPhoto($request);
        }

        $vendorTitle = $this->getVendorTitle($data['vendorID']);
        $categoryTitle = $this->getCategoryTitle($data['categoryID']);

        $food->fill([
            'name' => $data['name'],
            'price' => $data['price'],
            'merchant_price' => $data['merchant_price'],
            'disPrice' => $data['disPrice'],
            'description' => $data['description'],
            'vendorID' => $data['vendorID'],
            'vendorTitle' => $vendorTitle,
            'categoryID' => $data['categoryID'],
            'categoryTitle' => $categoryTitle,
            'quantity' => $data['quantity'],
            'publish' => $data['publish'],
            'nonveg' => $data['nonveg'],
            'veg' => !$data['nonveg'],
            'takeawayOption' => $data['takeawayOption'],
            'isAvailable' => $data['isAvailable'],
            'calories' => $data['calories'],
            'grams' => $data['grams'],
            'proteins' => $data['proteins'],
            'fats' => $data['fats'],
            'photo' => $imageUrl,
//            'photos' => $photoPath ? [$photoPath] : [],
            'addOnsTitle' => $data['addOnsTitle'],
            'addOnsPrice' => $data['addOnsPrice'],
            'product_specification' => $data['product_specification'],
            'item_attribute' => $data['item_attribute'],
            'variants' => $data['variants'],
            'updatedAt' => now()->toIso8601String(),
        ]);

        $food->save();

        $redirectUrl = $request->input('return_url');

        $logger->log(auth()->user(), 'foods', 'updated', 'Updated food: ' . $originalName . ' → ' . $food->name, $request);

        if ($redirectUrl) {
            return redirect($redirectUrl)->with('success', 'Food updated successfully.');
        }

        return redirect()->route('foods')->with('success', 'Food updated successfully.');
    }

    public function destroy(Request $request, $id, ActivityLogger $logger)
    {
        $food = VendorProduct::findOrFail($id);

        $this->deleteImage($food->photo);

        if (!empty($food->photos)) {
            foreach ($food->photos as $photo) {
                if ($photo !== $food->photo) {
                    $this->deleteImage($photo);
                }
            }
        }

        $food->delete();

        $logger->log(auth()->user(), 'foods', 'deleted', 'Deleted food: ' . $food->name, $request);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('foods')->with('success', 'Food deleted successfully.');
    }

    public function deleteMultiple(Request $request, ActivityLogger $logger)
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'No items selected'], 400);
        }

        $foods = VendorProduct::whereIn('id', $ids)->get();

        foreach ($foods as $food) {
            $this->deleteImage($food->photo);
            if (!empty($food->photos)) {
                foreach ($food->photos as $photo) {
                    if ($photo !== $food->photo) {
                        $this->deleteImage($photo);
                    }
                }
            }
        }

        VendorProduct::whereIn('id', $ids)->delete();

        $logger->log(
            auth()->user(),
            'foods',
            'bulk_deleted',
            'Bulk deleted foods: ' . implode(', ', $ids),
            $request
        );

        return response()->json(['success' => true]);
    }

    public function togglePublish(Request $request, $id, ActivityLogger $logger)
    {
        $publish = filter_var($request->input('publish'), FILTER_VALIDATE_BOOLEAN);

        VendorProduct::where('id', $id)->update([
            'publish' => $publish,
            'updatedAt' => now()->toIso8601String(),
        ]);

        $logger->log(
            auth()->user(),
            'foods',
            $publish ? 'published' : 'unpublished',
            ($publish ? 'Published' : 'Unpublished') . ' food ID: ' . $id,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Publish status updated successfully',
        ]);
    }

    public function toggleIsAvailable(Request $request, $id, ActivityLogger $logger)
    {
        $isAvailable = filter_var(
            $request->input('isAvailable'),
            FILTER_VALIDATE_BOOLEAN
        );

        VendorProduct::where('id', $id)->update([
            'isAvailable' => $isAvailable,
            'updatedAt' => now()->toIso8601String(),
        ]);

        $logger->log(
            auth()->user(),
            'foods',
            $isAvailable ? 'available' : 'unavailable',
            ($isAvailable ? 'Marked Available' : 'Marked Unavailable') . ' food ID: ' . $id,
            $request
        );

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Inline update for food price fields
     */
    public function inlineUpdate(Request $request, $id, ActivityLogger $logger)
    {
        try {
            $food = VendorProduct::find($id);

            if (!$food) {
                return response()->json([
                    'success' => false,
                    'message' => 'Food item not found'
                ], 404);
            }

            $field = $request->input('field');
            $value = $request->input('value', 0);

            // Allowed fields
            if (!in_array($field, ['price', 'merchant_price', 'disPrice'], true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field'
                ], 400);
            }

            // Normalize value (remove commas)
            $value = str_replace(',', '', $value);

            // Validate value
            if (!is_numeric($value) || $value < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid value'
                ], 400);
            }

            $value = (int) $value;
            $message = ucfirst(str_replace('_', ' ', $field)) . ' updated successfully';

            // =============================
            // FIELD LOGIC
            // =============================

            // 1️⃣ Regular Price
            if ($field === 'price') {
                $food->price = $value;

                // Reset discount if greater than price
                if ($food->disPrice && $food->disPrice > $value) {
                    $food->disPrice = null;
                    $message = 'Price updated. Discount price was reset because it exceeded the new price.';
                }

                // Reset merchant price if greater than price
                if ($food->merchant_price && $food->merchant_price > $value) {
                    $food->merchant_price = $value;
                }
            }

            // 2️⃣ Merchant Price
            elseif ($field === 'merchant_price') {

                // Merchant price cannot exceed main price
                if ($food->price && $value > $food->price) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Merchant price cannot be greater than regular price'
                    ], 400);
                }

                $food->merchant_price = $value;
            }

            // 3️⃣ Discount Price
            elseif ($field === 'disPrice') {

                // Reset discount
                if ($value === 0) {
                    $food->disPrice = null;
                    $message = 'Discount price removed';
                }

                // Discount cannot exceed price
                elseif ($food->price && $value > $food->price) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Discount price cannot be greater than regular price'
                    ], 400);
                }

                else {
                    $food->disPrice = $value;
                }
            }

            // Update timestamp
            $food->updatedAt = now()->toIso8601String();
            $food->save();

            // Log activity
            $logger->log(
                auth()->user(),
                'foods',
                'inline_updated',
                'Inline updated ' . $field . ' for food: ' . $food->name,
                $request
            );

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'price' => $food->price,
                    'merchant_price' => $food->merchant_price,
                    'disPrice' => $food->disPrice,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in food inlineUpdate: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update price'
            ], 500);
        }
    }

    public function import(Request $request, ActivityLogger $logger)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $spreadsheet = IOFactory::load($request->file('file'));
        $rows = $spreadsheet->getActiveSheet()->toArray();

        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file is empty or missing data.']);
        }

        $headers = array_map('trim', array_shift($rows));

        $imported = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = array_combine($headers, $row);

            if (!$data || empty($data['name'])) {
                continue;
            }

            try {
                $name = trim($data['name']);
                $price = $this->parseNumber($data['price'] ?? null);
                $vendorInput = trim($data['vendorID'] ?? $data['vendorName'] ?? '');
                $categoryInput = trim($data['categoryID'] ?? $data['categoryName'] ?? '');

                if (!$name || $price === null || !$vendorInput || !$categoryInput) {
                    $errors[] = "Row $rowNumber: Missing required fields (name, price, vendorID, categoryID)";
                    continue;
                }

                $vendorId = $this->resolveVendorId($vendorInput);
                if (!$vendorId) {
                    $errors[] = "Row $rowNumber: Vendor '{$vendorInput}' not found.";
                    continue;
                }

                $categoryId = $this->resolveCategoryId($categoryInput);
                if (!$categoryId) {
                    $errors[] = "Row $rowNumber: Category '{$categoryInput}' not found.";
                    continue;
                }

                $discount = $this->parseNumber($data['disPrice'] ?? null);

                if ($discount !== null && $discount > $price) {
                    $errors[] = "Row $rowNumber: Discount price cannot be higher than price.";
                    continue;
                }

                $photo = trim($data['photo'] ?? '');

                $food = VendorProduct::create([
                    'id' => Str::uuid()->toString(),
                    'name' => $name,
                    'price' => $price,
                    'merchant_price' => $vendorInput,
                    'disPrice' => $discount,
                    'description' => trim($data['description'] ?? ''),
                    'vendorID' => $vendorId,
                    'vendorTitle' => $this->getVendorTitle($vendorId),
                    'categoryID' => $categoryId,
                    'categoryTitle' => $this->getCategoryTitle($categoryId),
                    'quantity' => -1,
                    'publish' => $this->parseBoolean($data['publish'] ?? true),
                    'nonveg' => $this->parseBoolean($data['nonveg'] ?? false),
                    'veg' => !$this->parseBoolean($data['nonveg'] ?? false),
                    'isAvailable' => $this->parseBoolean($data['isAvailable'] ?? true),
                    'takeawayOption' => false,
                    'calories' => 0,
                    'grams' => 0,
                    'proteins' => 0,
                    'fats' => 0,
                    'photo' => $this->normalizePhotoPath($photo),
                    'photos' => $photo ? [$this->normalizePhotoPath($photo)] : [],
                    'addOnsTitle' => [],
                    'addOnsPrice' => [],
                    'product_specification' => null,
                    'item_attribute' => null,
                    'variants' => [],
                    'migratedBy' => 'excel_import',
                    'vType' => 'restaurant',
                    'createdAt' => now()->toIso8601String(),
                    'updatedAt' => now()->toIso8601String(),
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row $rowNumber: " . $e->getMessage();
            }
        }

        if ($imported === 0) {
            $message = 'No valid rows were found to import.';
            if (!empty($errors)) {
                $message .= ' Details: ' . implode('; ', $errors);
            }
            return back()->withErrors(['file' => $message]);
        }

        $message = "Foods imported successfully! ({$imported} rows)";

        if (!empty($errors)) {
            $message .= ' Some rows were skipped: ' . implode('; ', $errors);
        }

        $logger->log(
            auth()->user(),
            'foods',
            'imported',
            "Imported {$imported} foods via bulk upload" . (!empty($errors) ? ' (with warnings)' : ''),
            $request
        );

        return back()->with('success', $message);
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/foods_import_template.xlsx');
        $templateDir = dirname($filePath);

        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }


            $this->generateTemplate($filePath);


        return response()->download($filePath, 'foods_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="foods_import_template.xlsx"',
        ]);
    }

    private function generateTemplate($filePath)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Correct column headers (NO extra text!)
        $headers = [
            'A1' => 'name',
            'B1' => 'price',
            'C1' => 'description',
            'D1' => 'vendorID',
            'E1' => 'categoryID',
            'F1' => 'disPrice',
            'G1' => 'publish',
            'H1' => 'nonveg',
            'I1' => 'isAvailable',
            'J1' => 'photo',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Generate REAL vendor & category IDs
        $vendorId = DB::table('vendors')->value('id') ?? Str::uuid()->toString();
        $categoryId = DB::table('vendor_categories')->value('id') ?? Str::uuid()->toString();

        $sampleData = [
            'A2' => 'Sample Food Item',
            'B2' => '150',
            'C2' => 'This is a sample food item description',
            'D2' => $vendorId,
            'E2' => $categoryId,
            'F2' => '120',
            'G2' => 'true',
            'H2' => 'false',
            'I2' => 'true',
            'J2' => 'https://example.com/sample-food.jpg',
        ];

        foreach ($sampleData as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Auto-size columns
        foreach (range('A', 'J') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Save Excel file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    protected function validateFood(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'merchant_price' => 'required|numeric|min:0',
            'disPrice' => 'nullable|numeric|min:0|lte:price',
            'vendorID' => 'required|exists:vendors,id',
            'categoryID' => 'required|exists:vendor_categories,id',
            'quantity' => 'nullable|integer|min:-1',
            'description' => 'required|string',
            'calories' => 'nullable|integer|min:0',
            'grams' => 'nullable|integer|min:0',
            'proteins' => 'nullable|integer|min:0',
            'fats' => 'nullable|integer|min:0',
            'addOnsTitle' => 'nullable|array',
            'addOnsTitle.*' => 'nullable|string|max:255',
            'addOnsPrice' => 'nullable|array',
            'addOnsPrice.*' => 'nullable|string|max:255',
            'product_specification' => 'nullable|array',
            'product_specification.*' => 'nullable|string|max:255',
            'item_attribute' => 'nullable|array',
            'variants' => 'nullable|array',
            'publish' => 'sometimes|boolean',
            'nonveg' => 'sometimes|boolean',
            'takeawayOption' => 'sometimes|boolean',
            'isAvailable' => 'sometimes|boolean',
        ];

        if (!$isUpdate) {
            $rules['photo'] = 'nullable|image|max:2048';
        } else {
            $rules['photo'] = 'nullable|image|max:2048';
        }

        $validated = $request->validate($rules);

        $validated['publish'] = $request->boolean('publish');
        $validated['nonveg'] = $request->boolean('nonveg');
        $validated['takeawayOption'] = $request->boolean('takeawayOption');
        $validated['isAvailable'] = $request->boolean('isAvailable');
        $validated['quantity'] = $validated['quantity'] ?? -1;
        $validated['disPrice'] = $validated['disPrice'] ?? null;
        $validated['calories'] = $validated['calories'] ?? null;
        $validated['grams'] = $validated['grams'] ?? null;
        $validated['proteins'] = $validated['proteins'] ?? null;
        $validated['fats'] = $validated['fats'] ?? null;
        $validated['addOnsTitle'] = $validated['addOnsTitle'] ?? [];
        $validated['addOnsPrice'] = $validated['addOnsPrice'] ?? [];
        $validated['product_specification'] = $validated['product_specification'] ?? [];
        $validated['item_attribute'] = $validated['item_attribute'] ?? [];
        $validated['variants'] = $validated['variants'] ?? [];

        return $validated;
    }

    protected function storeUploadedPhoto(Request $request): ?string
    {
        if (!$request->hasFile('photo')) {
            return null;
        }

        return $request->file('photo')->store('foods', 'public');
    }

    protected function deleteImage(?string $path): void
    {
        if (!$path || Str::startsWith($path, ['http://', 'https://', '//'])) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    protected function getVendorTitle(string $vendorId): string
    {
        return DB::table('vendors')->where('id', $vendorId)->value('title') ?? '';
    }

    protected function getCategoryTitle(string $categoryId): string
    {
        return DB::table('vendor_categories')
            ->where('id', $categoryId)
            ->where('publish', 1)
            ->value('title') ?? '';
    }

    protected function parseNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric(str_replace(',', '', $value))) {
            return null;
        }

        return (float) str_replace(',', '', $value);
    }

    protected function parseBoolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    protected function resolveVendorId(string $input): ?string
    {
        if (DB::table('vendors')->where('id', $input)->exists()) {
            return $input;
        }

        $exactMatch = DB::table('vendors')
            ->where('vType', 'restaurant')
            ->whereRaw('LOWER(title) = ?', [strtolower($input)])
            ->value('id');

        if ($exactMatch) {
            return $exactMatch;
        }

        return DB::table('vendors')
            ->where('vType', 'restaurant')
            ->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($input) . '%'])
            ->orderBy('title')
            ->value('id');
    }

    protected function resolveCategoryId(string $input): ?string
    {
        // 1️⃣ Direct ID match (ONLY if published)
        if (
            DB::table('vendor_categories')
                ->where('id', $input)
                ->where('publish', 1)
                ->exists()
        ) {
            return $input;
        }

        $exactMatch = DB::table('vendor_categories')
            ->where('publish', 1)
            ->whereRaw('LOWER(title) = ?', [strtolower($input)])
            ->value('id');

        if ($exactMatch) {
            return $exactMatch;
        }

        return DB::table('vendor_categories')
            ->where('publish', 1)
            ->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($input) . '%'])
            ->orderBy('title')
            ->value('id');
    }

    protected function normalizePhotoPath(string $photo): ?string
    {
        if (!$photo) {
            return null;
        }

        if (Str::startsWith($photo, ['http://', 'https://', '//'])) {
            return $photo;
        }

        return ltrim($photo, '/');
    }
    private function exportFoodCSV($foods)
    {
        return new StreamedResponse(function () use ($foods) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Food Name',
                'Restaurant',
                'Category',
                'Price',
                'Merchant Price',
                'Discount Price',
                'Type',
                'Available',
                'Published',
                'Created At'
            ]);

            foreach ($foods as $food) {
                fputcsv($handle, [
                    $food->name,
                    $food->restaurant_name,
                    $food->category_name,
                    $food->price,
                    $food->merchant_price,
                    $food->disPrice,
                    $food->nonveg ? 'Non-Veg' : 'Veg',
                    $food->isAvailable ? 'Yes' : 'No',
                    $food->publish ? 'Yes' : 'No',
                    $food->createdAt,
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="foods.csv"',
        ]);
    }

//    private function exportFoodPDF($foods)
//    {
//        try {
//            $pdf = Pdf::loadView('exports.foods-pdf', ['foods' => $foods])
//                ->setPaper('A4', 'portrait')
//                ->setOption('enable-local-file-access', true);
//
//            return $pdf->download('foods.pdf');
//        } catch (\Exception $e) {
//            \Log::error('PDF Export Error: ' . $e->getMessage());
//            abort(500, 'Failed to generate PDF: ' . $e->getMessage());
//        }
//    }

    private function exportFoodPDF($foods)
    {
        return \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'exports.foods-pdf',
            ['foods' => $foods]
        )
            ->setPaper('A4', 'landscape')
            ->download('foods.pdf');
    }



    private function exportFoodExcel($foods)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\FoodsExport($foods),
            'foods.xlsx'
        );
    }

    public function export(Request $request)
    {
        $query = DB::table('vendor_products as vp')
            ->leftJoin('vendors as v', 'v.id', '=', 'vp.vendorID')
            ->leftJoin('vendor_categories as vc', 'vc.id', '=', 'vp.categoryID')
            ->select(
                'vp.name',
                'vp.price',
                'vp.merchant_price',
                'vp.disPrice',
                'vp.nonveg',
                'vp.isAvailable',
                'vp.publish',
                'vp.createdAt',
                'v.title as restaurant_name',
                'vc.title as category_name'
            );

        // Filters
        if ($request->restaurantId) {
            $query->where('vp.vendorID', $request->restaurantId);
        }

        if ($request->categoryId) {
            $query->where('vp.categoryID', $request->categoryId);
        }

        if ($request->foodType === 'veg') {
            $query->where('vp.nonveg', 0);
        } elseif ($request->foodType === 'non-veg') {
            $query->where('vp.nonveg', 1);
        }

        if ($request->search) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(vp.name) LIKE ?', ["%$search%"])
                    ->orWhereRaw('LOWER(v.title) LIKE ?', ["%$search%"])
                    ->orWhereRaw('LOWER(vc.title) LIKE ?', ["%$search%"]);
            });
        }



        $foods = $query->orderByDesc('vp.createdAt')->get();

        if ($request->type === 'pdf' && $foods->count() > 200) {
            return response()->json([
                'success' => false,
                'message' => 'PDF export is limited to 200 records. Use Excel or CSV for full data.'
            ], 422);
        }


        return match ($request->type) {
            'csv'   => $this->exportFoodCSV($foods),
            'pdf'   => $this->exportFoodPDF($foods),
            'excel' => $this->exportFoodExcel($foods),
            default => abort(404),
        };
    }
}
