<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Models\VendorCuisine;
use Carbon\Carbon;

// Note: Cuisines table doesn't have timestamps yet
// Run migration 2025_11_03_000001_add_timestamps_to_vendor_cuisines_table if needed

class CuisineController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("cuisines.index");
    }

     public function edit($id)
    {
        $cuisine = VendorCuisine::find($id);
        return view('cuisines.edit', [ 'id' => $id, 'cuisine' => $cuisine ]);
    }

    public function create()
    {
        return view('cuisines.create');
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

        $baseQuery = DB::table('vendor_cuisines');
        $total = $baseQuery->count();

        $filteredQuery = DB::table('vendor_cuisines')
            ->select('id', 'title', 'description', 'photo', 'publish');

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
        $placeholderImage = $this->getPlaceholderImage();

        $data = [];
        foreach ($pageRows as $row) {
            $imageUrl = $row->photo ?: $placeholderImage;
            $imageHtml = '<img alt="" width="100%" style="width:70px;height:70px;" src="'.e($imageUrl).'" onerror="this.onerror=null;this.src=\''.$placeholderImage.'\'" alt="image">';
            $editUrl = route('cuisines.edit', $row->id);
            $titleHtml = $imageHtml.'<a href="'.$editUrl.'">'.e($row->title).'</a>';
            $publishHtml = $row->publish ? '<label class="switch"><input type="checkbox" checked data-id="'.$row->id.'" name="isSwitch" class="toggle-publish"><span class="slider round"></span></label>' : '<label class="switch"><input type="checkbox" data-id="'.$row->id.'" name="isSwitch" class="toggle-publish"><span class="slider round"></span></label>';
            $actionsHtml = '<span class="action-btn"><a href="'.$editUrl.'"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>'.($canDelete ? ' <a href="'.route('cuisines.delete',$row->id).'" class="delete-btn"><i class="mdi mdi-delete"></i></a>' : '').'</span>';

            $rowArr = $canDelete ? [
                '<td class="delete-all"><input type="checkbox" class="is_open" dataId="'.$row->id.'"><label class="col-3 control-label"></label></td>',
                $titleHtml,
                e($row->description ?? ''),
                $publishHtml,
                $actionsHtml,
            ] : [
                $titleHtml,
                e($row->description ?? ''),
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

        $id = Str::uuid()->toString();
        $photoUrl = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/uploads/cuisines');
            $photoUrl = asset('storage/' . str_replace('public/', '', $path));
        }

        VendorCuisine::create([
            'id' => $id,
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'photo' => $photoUrl,
            'review_attributes' => json_encode($request->input('review_attributes', [])),
            'publish' => $request->boolean('publish') ? 1 : 0,
            'show_in_homepage' => $request->boolean('show_in_homepage') ? 1 : 0,
        ]);

        // Log activity
        \Log::info('✅ Cuisine created:', ['id' => $id, 'title' => $request->input('title')]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $cuisine = VendorCuisine::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'photo' => 'nullable|image',
        ]);

        $photoUrl = $cuisine->photo;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('public/uploads/cuisines');
            $photoUrl = asset('storage/' . str_replace('public/', '', $path));
        }

        $cuisine->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'photo' => $photoUrl,
            'review_attributes' => json_encode($request->input('review_attributes', [])),
            'publish' => $request->boolean('publish') ? 1 : 0,
            'show_in_homepage' => $request->boolean('show_in_homepage') ? 1 : 0,
        ]);

        // Log activity
        \Log::info('✅ Cuisine updated:', ['id' => $id, 'title' => $request->input('title')]);

        return response()->json(['success' => true]);
    }

    public function togglePublish(Request $request, $id)
    {
        $cuisine = VendorCuisine::findOrFail($id);
        $oldStatus = $cuisine->publish;
        $newStatus = $request->boolean('publish') ? 1 : 0;
        $cuisine->publish = $newStatus;
        $cuisine->save();

        // Log activity
        \Log::info('✅ Cuisine publish toggled:', ['id' => $id, 'title' => $cuisine->title, 'publish' => $newStatus]);

        return response()->json(['success' => true, 'publish' => $newStatus]);
    }

    public function import(Request $request)
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
        foreach ($rows as $row) {
            $data = array_combine($headers, $row);
            if (empty($data['title'])) {
                continue;
            }
            VendorCuisine::create([
                'id' => (string) Str::uuid(),
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'photo' => $data['photo'] ?? '',
                'publish' => strtolower($data['publish'] ?? '') === 'true' ? 1 : 0,
                'show_in_homepage' => 0,
            ]);
            $imported++;
        }
        if ($imported === 0) {
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }
        return back()->with('success', "Cuisines imported successfully! ($imported rows)");
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/cuisines_import_template.xlsx');
        $templateDir = dirname($filePath);

        // Create template directory if it doesn't exist
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateTemplate($filePath);
        }

        return response()->download($filePath, 'cuisines_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="cuisines_import_template.xlsx"'
        ]);
    }

    /**
     * Generate Excel template for cuisine import
     */
    private function generateTemplate($filePath)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'A1' => 'title',
                'B1' => 'photo'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }

            // Add sample data
            $sampleData = [
                'Italian',
                'https://example.com/images/italian.jpg'
            ];

            $sheet->fromArray([$sampleData], null, 'A2');

            // Auto-size columns
            foreach (range('A', 'B') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

        } catch (\Exception $e) {
            \Log::error('Failed to generate cuisines template: ' . $e->getMessage());
            abort(500, 'Failed to generate template');
        }
    }

    public function delete($id)
    {
        try {
            $c = VendorCuisine::find($id);
            if (!$c) {
                return redirect()->back()->with('error', 'Cuisine not found.');
            }

            $cuisineTitle = $c->title;
            $c->delete();

            // Log activity
            \Log::info('✅ Cuisine deleted:', ['id' => $id, 'title' => $cuisineTitle]);

            return redirect()->back()->with('success', 'Cuisine deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting cuisine: ' . $e->getMessage());
        }
    }
}


