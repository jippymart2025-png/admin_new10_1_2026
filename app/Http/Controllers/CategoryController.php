<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\VendorCategory;

class CategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

	  public function index()
    {
        return view("categories.index");

    }

     public function edit($id)
    {
        $category = VendorCategory::find($id);

        // If not found in vendor_categories, check if it's a mart category
        if (!$category) {
            \Log::warning("Category not found in vendor_categories: $id");

            // Check if this is a mart category
            $martCategory = DB::table('mart_categories')->where('id', $id)->first();
            if ($martCategory) {
                \Log::info("Found in mart_categories, redirecting...");
                // Redirect to mart-categories edit page
                return redirect()->route('mart-categories.edit', $id)
                    ->with('message', 'This is a Mart Category. Redirected to correct page.');
            }

            // Not found in either table
            abort(404, 'Category not found in either vendor_categories or mart_categories');
        }

        return view('categories.edit', ['id' => $id, 'category' => $category]);
    }

    public function create()
    {
        return view('categories.create');
    }

    public function data(Request $request)
    {
        $userPermissions = json_decode(@session('user_permissions'), true) ?: [];
        $canDelete = in_array('category.delete', $userPermissions);

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));
        $order = $request->input('order.0', ['column' => 0, 'dir' => 'asc']);
        $orderColumnIndex = (int) data_get($order, 'column', 0);
        $orderDir = data_get($order, 'dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $orderableColumns = $canDelete ? ['', 'title', 'totalProducts', '', ''] : ['title', 'totalProducts', '', ''];
        $orderBy = $orderableColumns[$orderColumnIndex] ?? 'title';

        $baseQuery = DB::table('vendor_categories');
        $total = $baseQuery->count();

        $filteredQuery = DB::table('vendor_categories');
        if ($search !== '') {
            $filteredQuery->where(function($q) use ($search){
                $q->where('title','like','%'.$search.'%')
                  ->orWhere('description','like','%'.$search.'%');
            });
        }

        if (in_array($orderBy, ['title'])) {
            $filteredQuery->orderBy($orderBy, $orderDir);
        } else {
            $filteredQuery->orderBy('title','asc');
        }

        $pageRows = $filteredQuery->offset($start)->limit($length)->get();
        $filtered = ($search==='') ? $total : (clone $filteredQuery)->count();

        // Get placeholder image from settings
        $placeholder = $this->getPlaceholderImage();

        $data = [];
        foreach ($pageRows as $row) {
            // Count foods for this category (supports exact and JSON-like storage)
            $foodsCount = DB::table('vendor_products')
                ->where(function($qq) use ($row){
                    $qq->where('categoryID', $row->id)
                       ->orWhere('categoryID','like','%"'.$row->id.'"%')
                       ->orWhere('categoryID','like','%'.$row->id.'%');
                })
                ->count();

            $imageUrl = $row->photo ?: $placeholder;
            $imageHtml = '<img alt="" width="100%" style="width:70px;height:70px;" src="'.e($imageUrl).'" onerror="this.onerror=null;this.src=\''.$placeholder.'\'" alt="image">';
            $editUrl = route('categories.edit', $row->id);
            $titleHtml = $imageHtml.'<a href="'.$editUrl.'">'.e($row->title).'</a>';
            $totalProductsLink = '<a href="'.url('foods?categoryID='.$row->id).'">'.$foodsCount.'</a>';
            $publishHtml = $row->publish ? '<label class="switch"><input type="checkbox" checked data-id="'.$row->id.'" class="toggle-publish"><span class="slider round"></span></label>' : '<label class="switch"><input type="checkbox" data-id="'.$row->id.'" class="toggle-publish"><span class="slider round"></span></label>';
            $actionsHtml = '<span class="action-btn"><a href="'.$editUrl.'"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>'.($canDelete ? ' <a href="'.route('categories.delete',$row->id).'" class="delete-btn"><i class="mdi mdi-delete"></i></a>' : '').'</span>';

            $rowArr = $canDelete ? [
                '<td class="delete-all"><input type="checkbox" class="is_open" dataId="'.$row->id.'"><label class="col-3 control-label"></label></td>',
                $titleHtml,
                $totalProductsLink,
                $publishHtml,
                $actionsHtml,
            ] : [
                $titleHtml,
                $totalProductsLink,
                $publishHtml,
                $actionsHtml,
            ];
            $data[] = $rowArr;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
            'stats' => [
                'total' => $total,
                'filtered' => $filtered
            ]
        ]);
    }

    /**
     * Get placeholder image from settings
     */
    private function getPlaceholderImage()
    {
        try {
            $setting = DB::table('settings')
                ->where('document_name', 'placeHolderImage')
                ->first();

            if ($setting && !empty($setting->fields)) {
                $fields = is_string($setting->fields) ? json_decode($setting->fields, true) : $setting->fields;
                if (isset($fields['image'])) {
                    return $fields['image'];
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching placeholder image: ' . $e->getMessage());
        }

        // Fallback to default placeholder
        return asset('assets/images/placeholder-image.png');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'photo' => 'nullable|image',
        ]);

        $id = (string) Str::uuid();
        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/uploads/categories');
            $photoUrl = asset('storage/' . str_replace('public/', '', $path));
        }

        VendorCategory::create([
            'id' => $id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'photo' => $photoUrl,
            'review_attributes' => json_encode($request->input('review_attributes', [])),
            'publish' => $request->boolean('item_publish') || $request->boolean('publish') ? 1 : 0,
            'show_in_homepage' => $request->boolean('show_in_homepage') ? 1 : 0,
        ]);

        // Log activity
        \Log::info('✅ Category created:', ['id' => $id, 'title' => $request->input('title')]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $category = VendorCategory::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'photo' => 'nullable|image',
        ]);

        $photoUrl = $category->photo;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/uploads/categories');
            $photoUrl = asset('storage/' . str_replace('public/', '', $path));
        }

        $category->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'photo' => $photoUrl,
            'review_attributes' => json_encode($request->input('review_attributes', [])),
            'publish' => $request->boolean('item_publish') || $request->boolean('publish') ? 1 : 0,
            'show_in_homepage' => $request->boolean('show_in_homepage') ? 1 : 0,
        ]);

        // Log activity
        \Log::info('✅ Category updated:', ['id' => $id, 'title' => $request->input('title')]);

        return response()->json(['success' => true]);
    }

    public function togglePublish(Request $request, $id)
    {
        $category = VendorCategory::findOrFail($id);
        $oldStatus = $category->publish;
        $newStatus = $request->boolean('publish') ? 1 : 0;
        $category->publish = $newStatus;
        $category->save();

        // Log activity
        \Log::info('✅ Category publish toggled:', ['id' => $id, 'title' => $category->title, 'publish' => $newStatus]);

        return response()->json(['success' => true, 'publish' => $newStatus]);
    }

    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            \Log::info('📁 Starting category import from file: ' . $request->file('file')->getClientOriginalName());

            $spreadsheet = IOFactory::load($request->file('file'));
            $rows = $spreadsheet->getActiveSheet()->toArray();

            \Log::info('📊 Excel file loaded, total rows: ' . count($rows));

            if (empty($rows) || count($rows) < 2) {
                \Log::warning('❌ Excel file is empty or has no data rows');
                return back()->withErrors(['file' => 'The uploaded file is empty or missing data.']);
            }

            $headers = array_map('trim', array_shift($rows));
            \Log::info('📋 Headers found: ' . implode(', ', $headers));

            $imported = 0;
            $errors = [];

            foreach ($rows as $rowIndex => $row) {
                try {
                    // Skip completely empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    $data = array_combine($headers, $row);

                    // Title is required
                    if (empty($data['title']) || trim($data['title']) === '') {
                        $errors[] = "Row " . ($rowIndex + 2) . ": Title is required";
                        continue;
                    }

                    // Parse boolean values
                    $publish = in_array(strtolower(trim($data['publish'] ?? '')), ['true', '1', 'yes'], true) ? 1 : 0;
                    $showInHomepage = in_array(strtolower(trim($data['show_in_homepage'] ?? '')), ['true', '1', 'yes'], true) ? 1 : 0;

                    VendorCategory::create([
                        'id' => (string) Str::uuid(),
                        'title' => trim($data['title']),
                        'description' => trim($data['description'] ?? ''),
                        'photo' => trim($data['photo'] ?? ''),
                        'publish' => $publish,
                        'show_in_homepage' => $showInHomepage,
                        'restaurant_id' => trim($data['restaurant_id'] ?? ''),
                        'review_attributes' => trim($data['review_attributes'] ?? ''),
                        'migratedBy' => 'bulk_import',
                    ]);

                    $imported++;
                    \Log::info('✅ Imported category: ' . trim($data['title']));

                } catch (\Exception $e) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                    \Log::error('❌ Error importing row ' . ($rowIndex + 2) . ': ' . $e->getMessage());
                }
            }

            // Log activity
            if ($imported > 0) {
                \Log::info('✅ Category bulk import completed:', ['imported' => $imported, 'errors' => count($errors)]);
            }

            if ($imported === 0) {
                $errorMessage = 'No valid rows were found to import.';
                if (!empty($errors)) {
                    $errorMessage .= ' Errors: ' . implode(', ', array_slice($errors, 0, 5));
                }
                return back()->withErrors(['file' => $errorMessage]);
            }

            $successMessage = "Categories imported successfully! ($imported rows)";
            if (!empty($errors)) {
                $successMessage .= " with " . count($errors) . " errors. Check logs for details.";
            }

            return back()->with('success', $successMessage);

        } catch (\Exception $e) {
            \Log::error('❌ Category import failed: ' . $e->getMessage());
            return back()->withErrors(['file' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/categories_import_template.xlsx');
        $templateDir = dirname($filePath);

        // Create template directory if it doesn't exist
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateTemplate($filePath);
        }

        return response()->download($filePath, 'categories_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="categories_import_template.xlsx"'
        ]);
    }

    public function delete($id)
    {
        try {
            $cat = VendorCategory::find($id);
            if(!$cat){
                return redirect()->back()->with('error','Category not found.');
            }

            $categoryTitle = $cat->title;
            $cat->delete();

            // Log activity
            \Log::info('✅ Category deleted:', ['id' => $id, 'title' => $categoryTitle]);

            return redirect()->back()->with('success','Category deleted successfully.');
        } catch (\Throwable $e) {
            \Log::error('❌ Error deleting category:', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error','Error deleting category: '.$e->getMessage());
        }
    }

    /**
     * Generate Excel template for category import
     */
    private function generateTemplate($filePath)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'A1' => 'title',
                'B1' => 'description',
                'C1' => 'photo',
                'D1' => 'publish',
                'E1' => 'show_in_homepage',
                'F1' => 'restaurant_id',
                'G1' => 'review_attributes'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }

            // Add sample data
            $sampleData = [
                'Fast Food',
                'Quick and delicious meals',
                'https://example.com/images/fast-food.jpg',
                'true',
                'true',
                '',
                'Taste,Quality,Service'
            ];

            $sheet->fromArray([$sampleData], null, 'A2');

            // Auto-size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

        } catch (\Exception $e) {
            \Log::error('Failed to generate categories template: ' . $e->getMessage());
            abort(500, 'Failed to generate template');
        }
    }
}


