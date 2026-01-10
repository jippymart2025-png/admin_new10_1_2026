<?php

namespace App\Http\Controllers;

use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\MartBanner;

class MartBannerController extends Controller
{
    protected FirebaseStorageService $firebaseStorage;

    public function __construct(FirebaseStorageService $firebaseStorage)
    {
        $this->middleware('auth');
        $this->firebaseStorage = $firebaseStorage;
    }

    // Views
    public function index()
    {
        return view('martBanners.index');
    }

    public function create()
    {
        return view('martBanners.create');
    }

    public function edit($id)
    {
        return view('martBanners.edit')->with('id', $id);
    }

    // DataTables provider (SQL)
    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));

        // Base query for total records
        $baseQ = DB::table('mart_banners');
        $totalRecords = $baseQ->count();

        // Filtered query
        $q = DB::table('mart_banners');

        // Apply search filter
        if ($search !== '') {
            $q->where(function($qq) use ($search){
                $qq->where('title','like','%'.$search.'%')
                   ->orWhere('position','like','%'.$search.'%')
                   ->orWhere('zoneTitle','like','%'.$search.'%')
                   ->orWhere('screen','like','%'.$search.'%');
            });
        }

        $filteredRecords = (clone $q)->count();
        $rows = $q->orderBy('set_order','asc')->orderBy('title','asc')->offset($start)->limit($length)->get();

        $items = [];
        foreach ($rows as $r) {
            $items[] = [
                'id' => $r->id,
                'title' => (string) ($r->title ?? ''),
                'position' => (string) ($r->position ?? ''),
                'zoneTitle' => (string) ($r->zoneTitle ?? 'No Zone'),
                'set_order' => (int) ($r->set_order ?? 0),
                'is_publish' => (bool) ($r->is_publish ?? 0),
                'photo' => (string) ($r->photo ?? ''),
            ];
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $items,
            'stats' => [
                'total' => $totalRecords,
                'filtered' => $filteredRecords
            ]
        ]);
    }

    // JSON for single banner
    public function json($id)
    {
        $b = MartBanner::find($id);
        if(!$b) return response()->json(['error'=>'Not found'],404);
        return response()->json($b);
    }

    // Create (SQL)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'set_order' => 'nullable|integer',
            'position' => 'nullable|string|max:255',
            'screen' => 'nullable|string|max:255',
            'photo' => 'nullable|image',
            'redirect_type' => 'nullable|string|max:255',
            'external_link' => 'nullable|string|max:2000',
            'ads_link' => 'nullable|string|max:2000',
        ]);
//        $id = (string) Str::uuid();
        $imageUrl = null;
        if ($request->hasFile('photo')) {
            $imageUrl = $this->firebaseStorage->uploadFile(
                $request->file('photo'),
                'mart-banners/banner_' . time() . '.' . $request->file('photo')->getClientOriginalExtension()
            );
        }
        MartBanner::create([
//            'id' => $id,
            'title' => $request->input('title',''),
            'description' => $request->input('description',''),
            'text' => $request->input('text',''),
            'photo' => $imageUrl,
            'position' => $request->input('position','top'),
            'screen' => $request->input('screen','home'),
            'redirect_type' => $request->input('redirect_type','external_link'),
            'storeId' => $request->input('storeId'),
            'productId' => $request->input('productId'),
            'martCategoryId' => $request->input('martCategoryId'),
            'ads_link' => $request->input('ads_link'),
            'external_link' => $request->input('external_link'),
            'is_publish' => $request->boolean('is_publish') ? 1 : 0,
            'set_order' => (int) $request->input('set_order', 0),
            'zoneId' => $request->input('zoneId') ?? $request->input('zone_select'),
            'zoneTitle' => $request->input('zoneTitle') ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Log activity
        \Log::info('✅ Mart banner created:', ['title' => $request->input('title')]);

        return response()->json(['success'=>true]);
    }

    // Update (SQL)
    public function update(Request $request, $id)
    {
        $b = MartBanner::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'set_order' => 'nullable|integer',
            'position' => 'nullable|string|max:255',
            'screen' => 'nullable|string|max:255',
            'photo' => 'nullable|image',
            'redirect_type' => 'nullable|string|max:255',
            'external_link' => 'nullable|string|max:2000',
            'ads_link' => 'nullable|string|max:2000',
        ]);
        $imageUrl = $b->photo;
        if ($request->hasFile('photo')) {
            $imageUrl = $this->firebaseStorage->uploadFile(
                $request->file('photo'),
                'mart-banners/banner_' . time() . '.' . $request->file('photo')->getClientOriginalExtension()
            );
        }
        $b->update([
            'title' => $request->input('title',''),
            'description' => $request->input('description',''),
            'text' => $request->input('text',''),
            'photo' => $imageUrl,
            'position' => $request->input('position','top'),
            'screen' => $request->input('screen','home'),
            'redirect_type' => $request->input('redirect_type','external_link'),
            'storeId' => $request->input('storeId'),
            'productId' => $request->input('productId'),
            'martCategoryId' => $request->input('martCategoryId'),
            'ads_link' => $request->input('ads_link'),
            'external_link' => $request->input('external_link'),
            'is_publish' => $request->boolean('is_publish') ? 1 : 0,
            'set_order' => (int) $request->input('set_order', 0),
            'zoneId' => $request->input('zoneId') ?? $request->input('zone_select'),
            'zoneTitle' => $request->input('zoneTitle') ?? '',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Log activity
        \Log::info('✅ Mart banner updated:', ['id' => $id, 'title' => $request->input('title')]);

        return response()->json(['success'=>true]);
    }

    // Toggle publish
    public function togglePublish($id)
    {
        $b = MartBanner::find($id);
        if(!$b) return response()->json(['success'=>false,'message'=>'Not found'],404);
        $b->is_publish = $b->is_publish ? 0 : 1;
        $b->updated_at = date('Y-m-d H:i:s');
        $b->save();

        // Log activity
        $action = $b->is_publish ? 'published' : 'unpublished';
        \Log::info('✅ Mart banner ' . $action . ':', ['id' => $id, 'title' => $b->title]);

        return response()->json(['success'=>true,'is_publish'=>(bool)$b->is_publish]);
    }

    // Delete
    public function destroy($id)
    {
        $b = MartBanner::find($id);
        if(!$b) return response()->json(['success'=>false,'message'=>'Mart banner not found'],404);

        $title = $b->title;
        $b->delete();

        // Log activity
        \Log::info('✅ Mart banner deleted:', ['id' => $id, 'title' => $title]);

        return response()->json(['success'=>true,'message'=>'Mart banner deleted successfully']);
    }

    // Bulk delete
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success'=>false,'message'=>'No ids'],400);
        }

        $count = MartBanner::whereIn('id',$ids)->delete();

        // Log activity
        \Log::info('✅ Mart banners bulk deleted:', ['count' => $count, 'ids' => $ids]);

        return response()->json(['success'=>true,'message'=>$count.' mart banners deleted successfully','count'=>$count]);
    }

    /**
     * Get all zones for dropdown
     */
    public function getZones()
    {
        try {
            $zones = DB::table('zone')
                ->where('publish', 1)
                ->orderBy('name', 'asc')
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $zones
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching zones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching zones'
            ], 500);
        }
    }

    /**
     * Get mart stores (vendors with vType='mart')
     */
    public function getStores()
    {
        try {
            $stores = DB::table('vendors')
                ->where('vType', 'mart')
                ->orderBy('title', 'asc')
                ->get(['id', 'title']);

            return response()->json([
                'success' => true,
                'data' => $stores
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching mart stores: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stores'
            ], 500);
        }
    }

    /**
     * Get mart products
     */
    public function getProducts(Request $request)
    {
        try {
            $storeId = $request->input('storeId');
            $search  = trim((string) $request->input('q', ''));

            /**
             * 🔎 Debug: detect orphaned mart items (vendorID not found in vendors)
             */
            $orphanedItemIds = DB::table('mart_items')
                ->leftJoin('vendors', 'mart_items.vendorID', '=', 'vendors.id')
                ->whereNull('vendors.id')
                ->pluck('mart_items.id')
                ->toArray();

            if (!empty($orphanedItemIds)) {
                \Log::warning('⚠️ Orphaned mart items detected (no vendor match)', [
                    'count' => count($orphanedItemIds),
                    'ids'   => $orphanedItemIds,
                ]);
            }

            /**
             * 🛒 Main mart items query
             */
            $itemsQuery = DB::table('mart_items')
                ->join('vendors', 'mart_items.vendorID', '=', 'vendors.id')
                ->where('vendors.vType', 'mart')
                ->where(function ($q) {
                    $q->whereNull('mart_items.publish')
                        ->orWhere('mart_items.publish', 1);
                });

            // Filter by store/vendor
            if (!empty($storeId)) {
                $itemsQuery->where('mart_items.vendorID', $storeId);
            }

            // Search filter
            if ($search !== '') {
                $itemsQuery->where(function ($q) use ($search) {
                    $q->where('mart_items.name', 'like', '%' . $search . '%')
                        ->orWhere('vendors.title', 'like', '%' . $search . '%');
                });
            }

            $items = $itemsQuery
                ->orderBy('mart_items.name', 'asc')
                ->get([
                    'mart_items.id',
                    'mart_items.name',
                    'mart_items.vendorID',
                    'vendors.title as vendorTitle',
                ]);

            return response()->json([
                'success' => true,
                'data'    => $items
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching mart items: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error fetching mart items'
            ], 500);
        }
    }


    /**
     * Get mart categories
     */
    public function getCategories()
    {
        try {
            $categories = DB::table('mart_categories')
                ->where('publish', 1)
                ->orderBy('title', 'asc')
                ->get(['id', 'title']);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching mart categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching categories'
            ], 500);
        }
    }
}
