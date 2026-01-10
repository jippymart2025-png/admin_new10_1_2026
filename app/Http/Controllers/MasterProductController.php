<?php

namespace App\Http\Controllers;

use App\Models\MasterProducts;
use App\Services\ActivityLogger;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MasterProductController extends Controller
{
    protected FirebaseStorageService $firebaseStorage;

    public function __construct(FirebaseStorageService $firebaseStorage)
    {
        $this->middleware('auth');
        $this->firebaseStorage = $firebaseStorage;
    }

    public function index()
    {
        return view('master_products.index');
    }

    public function create(Request $request)
    {
        $selectedCategoryId = $request->input('category_id');
        $vendorCategories = $this->getVendorCategories();
        $selectedCategory = null;

        if ($selectedCategoryId) {
            $selectedCategory = DB::table('vendor_categories')
                ->where('id', $selectedCategoryId)
                ->first(['id', 'title']);
        }

        // If AJAX request, return JSON
        if ($request->ajax() || $request->has('ajax')) {
            return response()->json([
                'success' => true,
                'category' => $selectedCategory,
                'categoryId' => $selectedCategoryId,
            ]);
        }

        return view('master_products.create', [
            'vendorCategories' => $vendorCategories,
            'selectedCategory' => $selectedCategory,
            'selectedCategoryId' => $selectedCategoryId,
        ]);
    }

    public function store(Request $request, ActivityLogger $logger)
    {
        $data = $this->validateMasterProduct($request);

        $photoPath = $this->storeUploadedPhoto($request);

        $categoryTitle = $this->getVendorCategoryTitle($data['categoryID']);

        $productData = [
            'categoryID' => $data['categoryID'],
            'categoryTitle' => $categoryTitle,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'photo' => $photoPath,
            'photos' => $photoPath ? [$photoPath] : [],
            'thumbnail' => $photoPath,
            'veg' => !($data['nonveg'] ?? false),
            'nonveg' => $data['nonveg'] ?? false,
            'food_type' => $data['food_type'] ?? null,
            'cuisine_type' => $data['cuisine_type'] ?? null,
            'calories' => $data['calories'] ?? 0,
            'proteins' => $data['proteins'] ?? 0,
            'fats' => $data['fats'] ?? 0,
            'carbs' => $data['carbs'] ?? 0,
            'grams' => $data['grams'] ?? 0,
            'suggested_price' => $data['suggested_price'] ?? null,
            'dis_price' => $data['dis_price'] ?? null,
            'tags' => $data['tags'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'is_recommended' => $data['is_recommended'] ?? false,
            'isAvailable' => $data['isAvailable'] ?? true,
            'publish' => $data['publish'] ?? true,
        ];

        $product = MasterProducts::create($productData);

        $logger->log(auth()->user(), 'master-products', 'created', 'Created master product: ' . $data['name'], $request);

        return redirect()->route('master-products.index')
            ->with('success', 'Master product created successfully.');
    }

    public function edit($id)
    {
        $product = MasterProducts::findOrFail($id);
        $vendorCategories = $this->getVendorCategories();

        return view('master_products.edit', [
            'product' => $product,
            'vendorCategories' => $vendorCategories,
        ]);
    }

    public function update(Request $request, $id, ActivityLogger $logger)
    {
        $product = MasterProducts::findOrFail($id);
        $data = $this->validateMasterProduct($request, true);

        $photoPath = $product->photo;
        $originalName = $product->name;

        if ($request->boolean('remove_photo')) {
            $this->deleteImage($photoPath);
            $photoPath = null;
        }

        if ($request->hasFile('photo')) {
            $this->deleteImage($photoPath);
            $photoPath = $this->storeUploadedPhoto($request);
        }

        $categoryTitle = $this->getVendorCategoryTitle($data['categoryID']);

        $product->fill([
            'categoryID' => $data['categoryID'],
            'categoryTitle' => $categoryTitle,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'photo' => $photoPath,
            'photos' => $photoPath ? [$photoPath] : [],
            'thumbnail' => $photoPath,
            'veg' => !($data['nonveg'] ?? false),
            'nonveg' => $data['nonveg'] ?? false,
            'food_type' => $data['food_type'] ?? null,
            'cuisine_type' => $data['cuisine_type'] ?? null,
            'calories' => $data['calories'] ?? 0,
            'proteins' => $data['proteins'] ?? 0,
            'fats' => $data['fats'] ?? 0,
            'carbs' => $data['carbs'] ?? 0,
            'grams' => $data['grams'] ?? 0,
            'suggested_price' => $data['suggested_price'] ?? null,
            'dis_price' => $data['dis_price'] ?? null,
            'tags' => $data['tags'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
            'is_recommended' => $data['is_recommended'] ?? false,
            'isAvailable' => $data['isAvailable'] ?? true,
            'publish' => $data['publish'] ?? true,
        ]);

        $product->save();

        $logger->log(
            auth()->user(),
            'master-products',
            'updated',
            'Updated master product: ' . $originalName . ' → ' . $product->name,
            $request
        );

        return redirect()->route('master-products.index')
            ->with('success', 'Master product updated successfully.');
    }

    public function destroy(Request $request, $id, ActivityLogger $logger)
    {
        $product = DB::table('master_products')->where('id', $id)->first(['id', 'name', 'photo', 'thumbnail']);

        if (!$product) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Product not found'], 404);
            }
            return redirect()->route('master-products.index')
                ->with('error', 'Product not found');
        }

        // Delete images
        if ($product->photo) {
            $this->deleteImage($product->photo);
        }
        if ($product->thumbnail && $product->thumbnail !== $product->photo) {
            $this->deleteImage($product->thumbnail);
        }

        DB::table('master_products')->where('id', $id)->delete();

        $logger->log(
            auth()->user(),
            'master-products',
            'deleted',
            'Deleted master product: ' . $product->name,
            $request
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('master-products.index')
            ->with('success', 'Master product deleted successfully.');
    }

    public function data(Request $request)
    {
        $userPermissions = json_decode(@session('user_permissions'), true) ?: [];
        $canDelete = in_array('foods.delete', $userPermissions);

        $draw = (int)$request->input('draw', 1);
        $start = (int)$request->input('start', 0);
        $length = (int)$request->input('length', 10);
        $search = strtolower((string)data_get($request->input('search'), 'value', ''));

        $categoryFilter = $request->input('category') ?? $request->input('categoryId');
        $foodTypeFilter = $request->input('foodType');

        $query = DB::table('master_products as mp')
            ->leftJoin('vendor_categories as vc', 'vc.id', '=', 'mp.categoryID')
            ->select(
                'mp.id',
                'mp.name',
                'mp.photo',
                'mp.thumbnail',
                'mp.description',
                'mp.suggested_price',
                'mp.dis_price',
                'mp.categoryID',
                'mp.categoryTitle',
                'mp.veg',
                'mp.nonveg',
                'mp.food_type',
                'mp.cuisine_type',
                'mp.calories',
                'mp.proteins',
                'mp.fats',
                'mp.carbs',
                'mp.grams',
                'mp.tags',
                'mp.display_order',
                'mp.is_recommended',
                'mp.isAvailable',
                'mp.publish',
                'vc.title as category_name'
            );

        if ($categoryFilter) {
            $query->where('mp.categoryID', $categoryFilter);
        }


        if ($foodTypeFilter === 'veg') {
            $query->where('mp.nonveg', 0);
        } elseif ($foodTypeFilter === 'non-veg') {
            $query->where('mp.nonveg', 1);
        }

        $total = $query->count();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where(DB::raw('LOWER(mp.name)'), 'like', "%$search%")
                    ->orWhere(DB::raw('LOWER(mp.description)'), 'like', "%$search%")
                    ->orWhere(DB::raw('LOWER(vc.title)'), 'like', "%$search%")
                    ->orWhere('mp.suggested_price', 'like', "%$search%")
                    ->orWhere('mp.dis_price', 'like', "%$search%");
            });
        }

        $recordsFiltered = $query->count();

        $order = $request->input('order.0', ['column' => 1, 'dir' => 'asc']);
        $orderColumnIndex = (int)data_get($order, 'column', 1);
        $orderDir = data_get($order, 'dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $orderableColumns = $canDelete
            ? ['', 'mp.name', 'mp.suggested_price', 'mp.dis_price', 'mp.categoryTitle', '', '']
            : ['mp.name', 'mp.suggested_price', 'mp.dis_price', 'mp.categoryTitle', '', ''];

        $orderBy = $orderableColumns[$orderColumnIndex] ?? 'mp.name';

        if (!empty($orderBy)) {
            $query->orderBy($orderBy, $orderDir);
        }

        $products = $query->skip($start)->take($length)->get();

        $data = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'photo' => $this->buildPhotoUrl($product->photo ?? $product->thumbnail),
                'thumbnail' => $this->buildPhotoUrl($product->thumbnail),
                'description' => $product->description,
                'suggested_price' => $product->suggested_price,
                'dis_price' => $product->dis_price,
                'categoryID' => $product->categoryID,
                'categoryTitle' => $product->categoryTitle ?? $product->category_name,
                'veg' => (bool)$product->veg,
                'nonveg' => (bool)$product->nonveg,
                'food_type' => $product->food_type,
                'cuisine_type' => $product->cuisine_type,
                'calories' => $product->calories,
                'proteins' => $product->proteins,
                'fats' => $product->fats,
                'carbs' => $product->carbs,
                'grams' => $product->grams,
                'tags' => $product->tags,
                'display_order' => $product->display_order,
                'is_recommended' => (bool)$product->is_recommended,
                'isAvailable' => (bool)($product->isAvailable ?? false),
                'publish' => (bool)($product->publish ?? false),
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function options(Request $request)
    {
        $type = $request->input('type');

        if ($type === 'categories') {
            $categories = DB::table('vendor_categories')
                ->orderBy('title')
                ->get(['id', 'title']);

            return response()->json(['success' => true, 'data' => $categories]);
        }

        if ($type === 'subcategories') {
            $categoryId = $request->input('categoryId') ?? $request->input('category');
            $foodTypeFilter = $request->input('foodType');

            // Vendor categories don't have subcategories, return empty array
            return response()->json([
                'success' => true,
                'data' => []
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid type']);
    }

    public function togglePublish(Request $request, $id, ActivityLogger $logger)
    {
        $isPublish = filter_var($request->input('publish'), FILTER_VALIDATE_BOOLEAN);

        DB::table('master_products')->where('id', $id)->update([
            'publish' => $isPublish,
            'updated_at' => now(),
        ]);

        $logger->log(
            auth()->user(),
            'master-products',
            $isPublish ? 'published' : 'unpublished',
            ($isPublish ? 'Published' : 'Unpublished') . ' master product ID: ' . $id,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Publish status updated successfully',
        ]);
    }

    public function toggleAvailable(Request $request, $id, ActivityLogger $logger)
    {
        $isAvailable = filter_var($request->input('isAvailable'), FILTER_VALIDATE_BOOLEAN);

        DB::table('master_products')->where('id', $id)->update([
            'isAvailable' => $isAvailable,
            'updated_at' => now(),
        ]);

        $logger->log(
            auth()->user(),
            'master-products',
            $isAvailable ? 'marked_available' : 'unmarked_available',
            ($isAvailable ? 'Marked Available' : 'Unmarked Available') . ' master product ID: ' . $id,
            $request
        );

        return response()->json([
            'success' => true,
            'message' => 'Available status updated successfully',
        ]);
    }

    public function deleteMultiple(Request $request, ActivityLogger $logger)
    {
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'No items selected'], 400);
        }

        $products = DB::table('master_products')->whereIn('id', $ids)->get(['id', 'name', 'photo', 'thumbnail']);

        foreach ($products as $product) {
            if ($product->photo) {
                $this->deleteImage($product->photo);
            }
            if ($product->thumbnail && $product->thumbnail !== $product->photo) {
                $this->deleteImage($product->thumbnail);
            }
        }

        DB::table('master_products')->whereIn('id', $ids)->delete();

        $logger->log(
            auth()->user(),
            'master-products',
            'bulk_deleted',
            'Bulk deleted master products: ' . implode(', ', $ids),
            $request
        );

        return response()->json(['success' => true]);
    }

    // Helper Methods
    protected function getVendorCategories()
    {
        return DB::table('vendor_categories')
            ->orderBy('title')
            ->get(['id', 'title']);
    }

    protected function getMasterCategories()
    {
        return DB::table('mart_categories')
            ->orderBy('title')
            ->pluck('title', 'id');
    }


    protected function getVendorCategoryTitle(string $categoryId): string
    {
        return DB::table('vendor_categories')->where('id', $categoryId)->value('title') ?? '';
    }

    protected function getMasterCategoryTitle(string $categoryId): string
    {
        return DB::table('mart_categories')->where('id', $categoryId)->value('title') ?? '';
    }


    protected function validateMasterProduct(Request $request, bool $isUpdate = false): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'categoryID' => 'required|exists:vendor_categories,id',
            'description' => 'nullable|string',
            'suggested_price' => 'nullable|numeric|min:0',
            'dis_price' => 'nullable|numeric|min:0',
            'calories' => 'nullable|integer|min:0',
            'proteins' => 'nullable|integer|min:0',
            'fats' => 'nullable|integer|min:0',
            'carbs' => 'nullable|integer|min:0',
            'grams' => 'nullable|integer|min:0',
            'food_type' => 'nullable|string|max:50',
            'cuisine_type' => 'nullable|string|max:100',
            'tags' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer',
            'nonveg' => 'sometimes|boolean',
            'is_recommended' => 'sometimes|boolean',
            'publish' => 'sometimes|boolean',
            'isAvailable' => 'sometimes|boolean',
        ];

        if (!$isUpdate) {
            $rules['photo'] = 'nullable|image|max:2048';
        } else {
            $rules['photo'] = 'nullable|image|max:2048';
            $rules['remove_photo'] = 'sometimes|boolean';
        }

        $validated = $request->validate($rules);

        // Set defaults
        $validated['nonveg'] = $request->boolean('nonveg', false);
        $validated['is_recommended'] = $request->boolean('is_recommended', false);
        $validated['publish'] = $request->boolean('publish', true);
        $validated['isAvailable'] = $request->boolean('isAvailable', true);
        $validated['calories'] = $validated['calories'] ?? 0;
        $validated['proteins'] = $validated['proteins'] ?? 0;
        $validated['fats'] = $validated['fats'] ?? 0;
        $validated['carbs'] = $validated['carbs'] ?? 0;
        $validated['grams'] = $validated['grams'] ?? 0;
        $validated['display_order'] = $validated['display_order'] ?? 0;

        return $validated;
    }

    protected function storeUploadedPhoto(Request $request): ?string
    {
        if (!$request->hasFile('photo')) {
            return null;
        }

        return $this->firebaseStorage->uploadFile(
            $request->file('photo'),
            'master-products/product_' . time() . '.' . $request->file('photo')->getClientOriginalExtension()
        );
    }

    protected function deleteImage(?string $path): void
    {
        if (!$path || Str::startsWith($path, ['http://', 'https://', '//'])) {
            return;
        }

        Storage::disk('public')->delete($path);
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
    public function inlineUpdateSuggestedPrice(Request $request, $id)
    {
        try {
            $product = MasterProducts::findOrFail($id);

            $price = $request->input('suggested_price');

            if (!is_numeric($price) || $price < 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid price'
                ], 400);
            }

            $product->suggested_price = round($price, 2);
            $product->updated_at = now();
            $product->save();

            return response()->json([
                'success' => true,
                'value' => $product->suggested_price,
                'formatted' => number_format($product->suggested_price, 2)
            ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update suggested price'
            ], 500);
        }
    }

    public function import(Request $request, ActivityLogger $logger)
    {
        // Check if ZipArchive extension is available
        if (!extension_loaded('zip') || !class_exists('ZipArchive')) {
            return back()->withErrors(['file' => 'PHP ZipArchive extension is not installed. Please enable php_zip extension in your PHP configuration (php.ini).']);
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file'));
            $rows = $spreadsheet->getActiveSheet()->toArray();
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Error loading Excel file: ' . $e->getMessage()]);
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file is empty or missing data.']);
        }

        $headers = array_map('trim', array_shift($rows));

        // Preload existing master products for duplicate checking
        $existingProducts = [];
        DB::table('master_products')
            ->select('id', 'name', 'categoryID')
            ->get()
            ->each(function ($product) use (&$existingProducts) {
                if (!empty($product->name) && !empty($product->categoryID)) {
                    $key = strtolower(trim($product->name)) . '|' . strtolower(trim($product->categoryID));
                    $existingProducts[$key] = $product->id;
                }
            });

        $imported = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = array_combine($headers, $row);

            if (!$data || empty($data['name'])) {
                continue;
            }

            try {
                $name = trim($data['name']);
                $price = $this->parseNumber($data['suggested_price'] ?? $data['price'] ?? null);
                $categoryInput = trim($data['categoryID'] ?? $data['categoryName'] ?? '');

                if (!$name || $price === null || !$categoryInput) {
                    $failed++;
                    $errors[] = "Row $rowNumber: Missing required fields (name, suggested_price, categoryID)";
                    continue;
                }

                $categoryId = $this->resolveCategoryId($categoryInput);
                if (!$categoryId) {
                    $failed++;
                    $errors[] = "Row $rowNumber: Category '{$categoryInput}' not found.";
                    continue;
                }

                // Check for duplicate product (name + categoryID combination)
                $duplicateKey = strtolower(trim($name)) . '|' . strtolower(trim($categoryId));
                $isUpdate = false;
                $product = null;

                if (isset($existingProducts[$duplicateKey])) {
                    $existingId = $existingProducts[$duplicateKey];

                    // Check if we're updating (if ID is provided in the data and matches existing ID)
                    // Normalize both IDs to strings for comparison
                    $providedId = !empty($data['id']) ? (string)trim($data['id']) : null;
                    $existingIdStr = (string)$existingId;

                    if ($providedId && $providedId === $existingIdStr) {
                        // Update existing product - allowed (ID matches)
                        $product = MasterProducts::find($existingId);
                        if (!$product) {
                            $failed++;
                            $errors[] = "Row $rowNumber: Master product with ID '{$existingId}' not found for update.";
                            continue;
                        }
                        $isUpdate = true;
                    } else {
                        // Duplicate found - not allowed to create new product
                        $failed++;
                        $categoryTitle = $this->getVendorCategoryTitle($categoryId);
                        $errors[] = "Row $rowNumber: Master product with name '{$name}' and category '{$categoryTitle}' already exists (ID: {$existingId})";
                        continue;
                    }
                }

                $discount = $this->parseNumber($data['dis_price'] ?? null);

                if ($discount !== null && $price !== null && $discount > $price) {
                    $failed++;
                    $errors[] = "Row $rowNumber: Discount price cannot be higher than suggested price.";
                    continue;
                }

                $photo = trim($data['photo'] ?? '');
                $categoryTitle = $this->getVendorCategoryTitle($categoryId);

                if ($isUpdate && $product) {
                    // Update existing product
                    $product->update([
                        'categoryID' => $categoryId,
                        'categoryTitle' => $categoryTitle,
                        'name' => $name,
                        'description' => trim($data['description'] ?? ''),
                        'suggested_price' => $price,
                        'dis_price' => $discount,
                        'photo' => $this->normalizePhotoPath($photo) ?: $product->photo,
                        'photos' => $photo ? [$this->normalizePhotoPath($photo)] : ($product->photos ?? []),
                        'thumbnail' => $photo ? $this->normalizePhotoPath($photo) : $product->thumbnail,
                        'veg' => !$this->parseBoolean($data['nonveg'] ?? false),
                        'nonveg' => $this->parseBoolean($data['nonveg'] ?? false),
                        'isAvailable' => $this->parseBoolean($data['isAvailable'] ?? true),
                        'publish' => $this->parseBoolean($data['publish'] ?? true),
                        'is_recommended' => $this->parseBoolean($data['is_recommended'] ?? false),
                        'calories' => $this->parseNumber($data['calories'] ?? 0) ?? 0,
                        'proteins' => $this->parseNumber($data['proteins'] ?? 0) ?? 0,
                        'fats' => $this->parseNumber($data['fats'] ?? 0) ?? 0,
                        'carbs' => $this->parseNumber($data['carbs'] ?? 0) ?? 0,
                        'grams' => $this->parseNumber($data['grams'] ?? 0) ?? 0,
                        'display_order' => $this->parseNumber($data['display_order'] ?? 0) ?? 0,
                        'food_type' => trim($data['food_type'] ?? ''),
                        'cuisine_type' => trim($data['cuisine_type'] ?? ''),
                        'tags' => trim($data['tags'] ?? ''),
                    ]);

                    $updated++;
                } else {
                    // Create new product
                    $product = MasterProducts::create([
                        'categoryID' => $categoryId,
                        'categoryTitle' => $categoryTitle,
                        'name' => $name,
                        'description' => trim($data['description'] ?? ''),
                        'suggested_price' => $price,
                        'dis_price' => $discount,
                        'photo' => $this->normalizePhotoPath($photo),
                        'photos' => $photo ? [$this->normalizePhotoPath($photo)] : [],
                        'thumbnail' => $photo ? $this->normalizePhotoPath($photo) : null,
                        'veg' => !$this->parseBoolean($data['nonveg'] ?? false),
                        'nonveg' => $this->parseBoolean($data['nonveg'] ?? false),
                        'isAvailable' => $this->parseBoolean($data['isAvailable'] ?? true),
                        'publish' => $this->parseBoolean($data['publish'] ?? true),
                        'is_recommended' => $this->parseBoolean($data['is_recommended'] ?? false),
                        'calories' => $this->parseNumber($data['calories'] ?? 0) ?? 0,
                        'proteins' => $this->parseNumber($data['proteins'] ?? 0) ?? 0,
                        'fats' => $this->parseNumber($data['fats'] ?? 0) ?? 0,
                        'carbs' => $this->parseNumber($data['carbs'] ?? 0) ?? 0,
                        'grams' => $this->parseNumber($data['grams'] ?? 0) ?? 0,
                        'display_order' => $this->parseNumber($data['display_order'] ?? 0) ?? 0,
                        'food_type' => trim($data['food_type'] ?? ''),
                        'cuisine_type' => trim($data['cuisine_type'] ?? ''),
                        'tags' => trim($data['tags'] ?? ''),
                    ]);

                    // Add to existing products array to prevent duplicates within the same import
                    $existingProducts[$duplicateKey] = $product->id;
                    $imported++;
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = "Row $rowNumber: " . $e->getMessage();
                \Log::error('Master product bulk import error', [
                    'row' => $rowNumber,
                    'exception' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $msg = "Master product created: $imported, updated: $updated, failed: $failed";

        if (!empty($errors)) {
            $msg .= "<br>" . implode('<br>', array_unique($errors));
        }

        if ($failed > 0 && $imported === 0 && $updated === 0) {
            return back()->withErrors(['file' => $msg]);
        }

        if ($failed > 0) {
            return back()->withErrors(['file' => $msg]);
        }

        $logger->log(
            auth()->user(),
            'master-products',
            'imported',
            "Bulk import: created {$imported}, updated {$updated}, failed {$failed}",
            $request
        );

        return back()->with('success', $msg);
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/master_products_import_template.xlsx');
        $templateDir = dirname($filePath);

        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        $this->generateTemplate($filePath);

        return response()->download($filePath, 'master_products_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="master_products_import_template.xlsx"',
        ]);
    }

    private function generateTemplate($filePath)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Column headers
        $headers = [
            'A1' => 'name',
            'B1' => 'suggested_price',
            'C1' => 'description',
            'D1' => 'categoryID',
            'E1' => 'dis_price',
            'F1' => 'publish',
            'G1' => 'nonveg',
            'H1' => 'isAvailable',
            'I1' => 'photo',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Generate REAL category ID
        $categoryId = DB::table('vendor_categories')->value('id') ?? '';

        $sampleData = [
            'A2' => 'Sample Master Product',
            'B2' => '150',
            'C2' => 'This is a sample master product description',
            'D2' => $categoryId,
            'E2' => '120',
            'F2' => 'true',
            'G2' => 'false',
            'H2' => 'true',
            'I2' => 'https://example.com/sample-product.jpg',
        ];

        foreach ($sampleData as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Auto-size columns
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Save Excel file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    protected function parseNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric(str_replace(',', '', $value))) {
            return null;
        }

        return (float)str_replace(',', '', $value);
    }

    protected function parseBoolean($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    protected function resolveCategoryId(string $input): ?string
    {
        if (DB::table('vendor_categories')->where('id', $input)->exists()) {
            return $input;
        }

        $exactMatch = DB::table('vendor_categories')
            ->whereRaw('LOWER(title) = ?', [strtolower($input)])
            ->value('id');

        if ($exactMatch) {
            return $exactMatch;
        }

        return DB::table('vendor_categories')
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
}
