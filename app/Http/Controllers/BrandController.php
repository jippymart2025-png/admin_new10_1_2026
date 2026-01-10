<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Brand;

class BrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("brands.index");
    }

    public function edit($id)
    {
        return view('brands.edit')->with('id', $id);
    }

    public function create()
    {
        return view('brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $existing = DB::table('brands')->where('name', trim($request->name))->exists();
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'Brand name already exists'], 422);
            }

            $slug = $request->slug;
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->name)));
            }

            $logoUrl = '';
            if ($request->hasFile('logo')) {
                $logoUrl = $this->uploadLogo($request->file('logo'));
            }

            $id = (string) Str::uuid();
            $now = now()->format('Y-m-d H:i:s');
            DB::table('brands')->insert([
                'id' => $id,
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description ?? '',
                'status' => $request->boolean('status') ? 1 : 0,
                'logo_url' => $logoUrl,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Log activity
            \Log::info('✅ Brand created:', ['id' => $id, 'name' => $request->name]);

            return response()->json([
                'success' => true,
                'message' => 'Brand created successfully!',
                'id' => $id,
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error creating brand:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error creating brand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        try {
            $exists = DB::table('brands')->where('id', $id)->exists();
            if (!$exists) {
                return response()->json(['success' => false, 'message' => 'Brand not found'], 404);
            }

            $duplicate = DB::table('brands')
                ->where('name', trim($request->name))
                ->where('id', '!=', $id)
                ->exists();
            if ($duplicate) {
                return response()->json(['success' => false, 'message' => 'Brand name already exists'], 422);
            }

            $slug = $request->slug;
            if (empty($slug)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $request->name)));
            }

            $row = DB::table('brands')->where('id', $id)->first();
            $logoUrl = $row->logo_url ?? '';
            if ($request->hasFile('logo')) {
                $logoUrl = $this->uploadLogo($request->file('logo'));
            }

            DB::table('brands')->where('id', $id)->update([
                'name' => $request->name,
                'slug' => $slug,
                'description' => $request->description ?? '',
                'status' => $request->boolean('status') ? 1 : 0,
                'logo_url' => $logoUrl,
                'updated_at' => now()->format('Y-m-d H:i:s'),
            ]);

            // Log activity
            \Log::info('✅ Brand updated:', ['id' => $id, 'name' => $request->name]);

            return response()->json([
                'success' => true,
                'message' => 'Brand updated successfully!'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error updating brand:', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error updating brand: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $inUse = DB::table('mart_items')->where('brandID', $id)->exists();
            if ($inUse) {
                return redirect()->route('brands')->with('error', 'Cannot delete brand. It is being used by one or more items.');
            }
            DB::table('brands')->where('id', $id)->delete();
            return redirect()->route('brands')->with('success', 'Brand deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('brands')->with('error', 'Error deleting brand: ' . $e->getMessage());
        }
    }

    public function getData(Request $request)
    {
        try {
            $start = (int) ($request->input('start', 0));
            $length = (int) ($request->input('length', 10));
            $draw = (int) ($request->input('draw', 1));
            $search = strtolower((string) data_get($request->input('search'), 'value', ''));
            $withDelete = (bool) $request->boolean('withDelete');

            // Base query for total
            $baseQ = DB::table('brands');
            $totalRecords = $baseQ->count();

            // Filtered query
            $q = DB::table('brands');
            if ($search !== '') {
                $q->where(function($qq) use ($search){
                    $qq->where('name','like','%'.$search.'%')
                       ->orWhere('slug','like','%'.$search.'%')
                       ->orWhere('description','like','%'.$search.'%');
                });
            }

            $filteredRecords = (clone $q)->count();
            $rows = $q->orderBy('name','asc')->offset($start)->limit($length)->get();

            $placeholder = asset('images/placeholder.png');
            $data = [];
            foreach ($rows as $r) {
                $editUrl = route('brands.edit', $r->id);
                $nameHtml = '<a href="'.$editUrl.'">'.e($r->name ?: '').'</a>';
                $imgSrc = $r->logo_url ?: $placeholder;
                $imgHtml = '<img alt="" onerror="this.onerror=null;this.src=\''.$placeholder.'\'" style="width:70px;height:70px;" src="'.$imgSrc.'">';
                $toggleHtml = '<label class="switch"><input type="checkbox" '.($r->status ? 'checked' : '').' data-id="'.$r->id.'" name="isSwitch"><span class="slider round"></span></label>';
                $actions = '<span class="action-btn">'
                    .(in_array('brands.edit', json_decode(@session('user_permissions'), true) ?? []) ? '<a href="'.$editUrl.'"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>' : '')
                    .(in_array('brands.delete', json_decode(@session('user_permissions'), true) ?? []) ? ' <a href="javascript:void(0)" class="brand-delete" data-id="'.$r->id.'"><i class="mdi mdi-delete"></i></a>' : '')
                    .'</span>';

                $row = [];
                if ($withDelete) {
                    $checkbox = '<td class="delete-all"><input type="checkbox" id="is_open_'.$r->id.'" class="is_open" dataId="'.$r->id.'"><label class="col-3 control-label" for="is_open_'.$r->id.'"></label></td>';
                    $row[] = $checkbox;
                }
                $row[] = $nameHtml;
                $row[] = e($r->slug ?: '-');
                $row[] = $imgHtml;
                $row[] = e($r->description ?: '-');
                $row[] = $toggleHtml;
                $row[] = $actions;
                $data[] = $row;
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
                'stats' => [
                    'total' => $totalRecords,
                    'filtered' => $filteredRecords
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error fetching data: ' . $e->getMessage()
            ]);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        \Log::info('🔄 Starting brand import...');

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        if ($extension === 'csv') {
            // Handle CSV files
            $rows = [];
            if (($handle = fopen($file->getPathname(), 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            // Handle Excel files (prioritized)
            try {
                $spreadsheet = IOFactory::load($file);
                $rows = $spreadsheet->getActiveSheet()->toArray();
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'ZipArchive') !== false) {
                    return back()->withErrors(['file' => 'Excel import requires ZipArchive extension. Please use CSV format or enable ZipArchive in your PHP configuration.']);
                }
                return back()->withErrors(['file' => 'Error reading Excel file: ' . $e->getMessage()]);
            }
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file is empty or missing data.']);
        }

        $headers = array_map('trim', array_shift($rows));

        // Validate headers
        $requiredHeaders = ['name'];
        $missingHeaders = array_diff($requiredHeaders, $headers);

        if (!empty($missingHeaders)) {
            return back()->withErrors(['file' => 'Missing required columns: ' . implode(', ', $missingHeaders) .
                '. Please use the template provided by the "Download Template" button.']);
        }

        \Log::info('📊 Processing ' . count($rows) . ' rows from import file');

        $imported = 0;
        $updated = 0;
        $errors = [];
        $now = now()->format('Y-m-d H:i:s');

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed
            $data = array_combine($headers, $row);

            // Skip completely empty rows
            if ($this->isEmptyRow($row)) {
                continue;
            }

            try {
                // Validate required fields
                if (empty($data['name'])) {
                    $errors[] = "Row $rowNumber: Missing required field (name)";
                    \Log::warning("⚠️ Row $rowNumber: Missing name field");
                    continue;
                }

                $name = trim($data['name']);

                // Generate slug if not provided
                $slug = trim($data['slug'] ?? '');
                if (empty($slug)) {
                    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                }

                // Parse status (handle various formats)
                $statusValue = strtolower(trim($data['status'] ?? 'true'));
                $status = in_array($statusValue, ['true', '1', 'yes', 'active']) ? 1 : 0;

                // Prepare brand data for MySQL
                $brandData = [
                    'name' => $name,
                    'slug' => $slug,
                    'description' => trim($data['description'] ?? ''),
                    'status' => $status,
                    'logo_url' => trim($data['logo_url'] ?? ''),
                    'updated_at' => $now,
                ];

                // Check if brand already exists by name (MySQL)
                $existingBrand = DB::table('brands')->where('name', $name)->first();

                if ($existingBrand) {
                    // Update existing brand
                    DB::table('brands')->where('id', $existingBrand->id)->update($brandData);
                    $updated++;
                    \Log::info("✅ Row $rowNumber: Updated brand '$name'");
                } else {
                    // Create new brand
                    $brandData['id'] = (string) Str::uuid();
                    $brandData['created_at'] = $now;
                    DB::table('brands')->insert($brandData);
                    $imported++;
                    \Log::info("✅ Row $rowNumber: Created brand '$name'");
                }
            } catch (\Exception $e) {
                $errorMsg = "Row $rowNumber: " . $e->getMessage();
                $errors[] = $errorMsg;
                \Log::error("❌ $errorMsg");
            }
        }

        if ($imported === 0 && $updated === 0) {
            \Log::warning('⚠️ No valid rows were imported');
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }

        $message = "Brands processed successfully! Created: $imported, Updated: $updated";
        if (!empty($errors)) {
            $message .= " Errors: " . count($errors) . " rows failed.";
            \Log::warning("⚠️ Import completed with errors: " . count($errors) . " rows failed");
        } else {
            \Log::info("✅ Import completed successfully: $imported created, $updated updated");
        }

        // Log activity
        \Log::info('✅ Brand import completed:', [
            'imported' => $imported,
            'updated' => $updated,
            'errors' => count($errors)
        ]);

        return back()->with('success', $message);
    }

    public function downloadTemplate(Request $request)
    {
        $format = $request->get('format', 'excel'); // Default to Excel format

        if ($format === 'csv') {
            return $this->downloadCsvTemplate();
        } else {
            return $this->downloadExcelTemplate();
        }
    }

    private function downloadCsvTemplate()
    {
        $filePath = storage_path('app/templates/brands_import_template.csv');
        $templateDir = dirname($filePath);

        // Create template directory if it doesn't exist
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Generate CSV template
        $csvContent = "name,slug,description,status,logo_url\n";
        $csvContent .= "Nike,nike,Sportswear and footwear brand,true,https://example.com/nike-logo.png\n";
        $csvContent .= "Adidas,adidas,German sportswear brand,true,https://example.com/adidas-logo.png\n";

        file_put_contents($filePath, $csvContent);

        return response()->download($filePath, 'brands_import_template.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="brands_import_template.csv"'
        ]);
    }

    private function downloadExcelTemplate()
    {
        $filePath = storage_path('app/templates/brands_import_template.xlsx');
        $templateDir = dirname($filePath);

        // Create template directory if it doesn't exist
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateTemplate($filePath);
        }

        return response()->download($filePath, 'brands_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="brands_import_template.xlsx"'
        ]);
    }

    /**
     * Generate Excel template for brand import
     */
    private function generateTemplate($filePath)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers with proper styling
            $headers = [
                'A1' => 'name',
                'B1' => 'slug',
                'C1' => 'description',
                'D1' => 'status',
                'E1' => 'logo_url'
            ];

            // Set header values and styling
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
                $sheet->getStyle($cell)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFE0E0E0');
            }

            // Add sample data with multiple examples
            $sampleData = [
                ['Nike', 'nike', 'Sportswear and footwear brand', 'true', 'https://example.com/nike-logo.png'],
                ['Adidas', 'adidas', 'German sportswear brand', 'true', 'https://example.com/adidas-logo.png'],
                ['Puma', 'puma', 'Sports and lifestyle brand', 'false', 'https://example.com/puma-logo.png']
            ];

            $row = 2;
            foreach ($sampleData as $data) {
                $col = 'A';
                foreach ($data as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }

            // Add instructions in a separate section
            $instructionRow = $row + 2;
            $sheet->setCellValue('A' . $instructionRow, 'Instructions:');
            $sheet->getStyle('A' . $instructionRow)->getFont()->setBold(true);

            $instructions = [
                'name' => 'Required: Brand name (e.g., Nike, Adidas)',
                'slug' => 'Optional: URL-friendly version (auto-generated if empty)',
                'description' => 'Optional: Brand description',
                'status' => 'Required: true/false (active/inactive)',
                'logo_url' => 'Optional: Full URL to brand logo image'
            ];

            $row = $instructionRow + 1;
            foreach ($instructions as $field => $instruction) {
                $sheet->setCellValue('A' . $row, $field . ': ' . $instruction);
                $row++;
            }

            // Auto-size columns
            foreach (range('A', 'E') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Set column widths for better readability
            $sheet->getColumnDimension('A')->setWidth(20); // name
            $sheet->getColumnDimension('B')->setWidth(15); // slug
            $sheet->getColumnDimension('C')->setWidth(40); // description
            $sheet->getColumnDimension('D')->setWidth(10); // status
            $sheet->getColumnDimension('E')->setWidth(50); // logo_url

            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

        } catch (\Exception $e) {
            throw new \Exception('Failed to generate template: ' . $e->getMessage());
        }
    }

    /**
     * Upload logo to storage
     */
    private function uploadLogo($file)
    {
        try {
            // Generate unique filename
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Store file in public storage
            $path = $file->storeAs('brands/logos', $filename, 'public');

            // Return full URL
            return asset('storage/' . $path);
        } catch (\Exception $e) {
            throw new \Exception('Failed to upload logo: ' . $e->getMessage());
        }
    }

    public function json($id)
    {
        $row = DB::table('brands')->where('id', $id)->first();
        if (!$row) return response()->json(['error' => 'Not found'], 404);
        return response()->json($row);
    }

    public function toggle($id)
    {
        $row = DB::table('brands')->where('id', $id)->first();
        if (!$row) return response()->json(['success' => false], 404);
        $new = $row->status ? 0 : 1;
        DB::table('brands')->where('id', $id)->update(['status' => $new, 'updated_at' => now()->format('Y-m-d H:i:s')]);

        // Log activity
        $action = $new ? 'activated' : 'deactivated';
        \Log::info("✅ Brand $action:", ['id' => $id, 'name' => $row->name, 'status' => $new]);

        return response()->json(['success' => true, 'status' => $new]);
    }

    public function destroy($id)
    {
        $brand = DB::table('brands')->where('id', $id)->first();
        if (!$brand) {
            \Log::error('❌ Brand not found for deletion:', ['id' => $id]);
            return response()->json(['success' => false, 'message' => 'Brand not found'], 404);
        }

        $inUse = DB::table('mart_items')->where('brandID', $id)->exists();
        if ($inUse) {
            \Log::warning('⚠️ Cannot delete brand (in use):', ['id' => $id, 'name' => $brand->name]);
            return response()->json(['success' => false, 'message' => 'Brand is used by items'], 422);
        }

        $brandName = $brand->name;
        DB::table('brands')->where('id', $id)->delete();

        // Log activity
        \Log::info('✅ Brand deleted:', ['id' => $id, 'name' => $brandName]);

        return response()->json(['success' => true, 'message' => 'Brand deleted successfully']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        if (empty($ids)) return response()->json(['success' => false, 'message' => 'No brands selected'], 422);

        // Exclude brands that are in use
        $inUseIds = DB::table('mart_items')->whereIn('brandID', $ids)->distinct()->pluck('brandID')->all();
        $deletable = array_values(array_diff($ids, $inUseIds));

        if (!empty($deletable)) {
            DB::table('brands')->whereIn('id', $deletable)->delete();
            \Log::info('✅ Brands bulk deleted:', ['count' => count($deletable), 'blocked' => count($inUseIds)]);
        }

        return response()->json(['success' => true, 'deleted' => count($deletable), 'blocked' => $inUseIds]);
    }
    /**
     * Check if a row is completely empty
     */
    private function isEmptyRow($row)
    {
        return empty(array_filter($row, function($value) {
            return !is_null($value) && $value !== '';
        }));
    }
}
