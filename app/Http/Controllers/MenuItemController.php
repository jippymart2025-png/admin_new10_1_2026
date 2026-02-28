<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\VendorProduct;
use App\Models\Zone;
use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\MenuItem;

class MenuItemController extends Controller
{
    protected FirebaseStorageService $firebaseStorage;

    public function __construct(FirebaseStorageService $firebaseStorage)
    {
        $this->middleware('auth');
        $this->firebaseStorage = $firebaseStorage;
    }

    public function index()
    {
        return view('settings.menu_items.index');
    }

    public function create()
    {
        return view('settings.menu_items.create');
    }

    public function edit($id)
    {
        return view('settings.menu_items.edit')->with('id', $id);
    }


    // DataTables provider (SQL)
    public function data(Request $request)
    {
        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));
        $zoneFilter = (string) $request->input('zoneId', '');

        // Base query
        $baseQ = MenuItem::query();
        $totalRecords = $baseQ->count();

        // Filtered query
        $q = MenuItem::query()->select(
            'id',
            'title',
            'position',
            'photo',
            'is_publish',
            'zoneId',
        );

        // Apply zone filter
        if ($zoneFilter !== '') {
            $q->where('zoneId', $zoneFilter);
        }

        // Apply search filter
        if ($search !== '') {
            $q->where(function($qq) use ($search){
                $qq->where('title','like','%'.$search.'%')
                    ->orWhere('position','like','%'.$search.'%')
                    ->orWhere('zoneTitle','like','%'.$search.'%');
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

    public function json($id)
    {
        $doc = MenuItem::find($id);
        if (!$doc) {
            return response()->json(['error' => 'Not found'], 404);
        }
        return response()->json($doc);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'set_order' => 'nullable|integer',
            'position' => 'nullable|string|max:255',
            'photo' => 'nullable|image',
            'redirect_type' => 'nullable|string|max:255',
            'redirect_id' => 'nullable|string|max:2000',
            'zoneId' => 'nullable|string|max:255',
            'zoneTitle' => 'nullable|string|max:255',
        ]);
        $id = (string) Str::uuid();
        $imageUrl = null;
        if ($request->hasFile('photo')) {
            $imageUrl = $this->firebaseStorage->uploadFile(
                $request->file('photo'),
                'menu-items/banner_' . time() . '.' . $request->file('photo')->getClientOriginalExtension()
            );
        }
        MenuItem::create([
            'id' => $id,
            'title' => $request->input('title',''),
            'set_order' => (int) $request->input('set_order',0),
            'position' => $request->input('position','top'),
            'photo' => $imageUrl,
            'redirect_type' => $request->input('redirect_type','external_link'),
            'redirect_id' => $request->input('redirect_id'),
            'is_publish' => $request->boolean('is_publish') ? 1 : 0,
            'zoneId' => $request->input('zoneId'),
            'zoneTitle' => $request->input('zoneTitle'),
        ]);


        return response()->json([
            'message' => 'banner created successfully',
            'success'=>true,
            'id'=>$id
        ]);
    }

    public function update(Request $request, $id)
    {
        $mi = MenuItem::findOrFail($id);
        $request->validate([
            'title' => 'required|string|max:255',
            'set_order' => 'nullable|integer',
            'position' => 'nullable|string|max:255',
            'photo' => 'nullable|image',
            'redirect_type' => 'nullable|string|max:255',
            'redirect_id' => 'nullable|string|max:2000',
            'zoneId' => 'nullable|string|max:255',
            'zoneTitle' => 'nullable|string|max:255',
        ]);
        $imageUrl = $mi->photo;
        if ($request->hasFile('photo')) {
            $imageUrl = $this->firebaseStorage->uploadFile(
                $request->file('photo'),
                'mart-banners/banner_' . time() . '.' . $request->file('photo')->getClientOriginalExtension()
            );
        }
        $mi->update([
            'title' => $request->input('title',''),
            'set_order' => (int) $request->input('set_order',0),
            'position' => $request->input('position','top'),
            'photo' => $imageUrl,
            'redirect_type' => $request->input('redirect_type','external_link'),
            'redirect_id' => $request->input('redirect_id'),
            'is_publish' => $request->boolean('is_publish') ? 1 : 0,
            'zoneId' => $request->input('zoneId'),
            'zoneTitle' => $request->input('zoneTitle'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully'
        ]);
    }

    public function togglePublish($id)
    {
        $doc = MenuItem::find($id);
        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }
        $doc->is_publish = $doc->is_publish ? 0 : 1;
        $doc->save();

        $action = $doc->is_publish ? 'published' : 'unpublished';

        return response()->json(['success' => true, 'is_publish' => (bool) $doc->is_publish]);
    }

    public function destroy($id)
    {
        $doc = MenuItem::find($id);
        if (!$doc) {
            return response()->json(['success' => false, 'message' => 'Menu item not found'], 404);
        }

        $title = $doc->title;
        $doc->delete();


        return response()->json(['success' => true, 'message' => 'Menu item deleted successfully']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No ids'], 400);
        }

        $count = MenuItem::whereIn('id', $ids)->delete();


        return response()->json(['success' => true, 'message' => $count . ' menu items deleted successfully', 'count' => $count]);
    }

    /**
     * Get all stores from vendors table (for dropdown)
     */
    public function getStores(Request $request)
    {
        try {
            $zoneId = $request->input('zoneId', '');

            $cacheKey = $zoneId
                ? "stores:list:zone:$zoneId"
                : "stores:list:all";

            $stores = Cache::remember($cacheKey, 600, function () use ($zoneId) {
                $q = Vendor::query()
                    ->select('id', 'title', 'zoneId')
                    ->orderBy('title', 'asc');

                if ($zoneId) {
                    $q->where('zoneId', $zoneId);
                }

                return $q->get(); // ✅ DATA is cached
            });

            return response()->json([
                'success' => true,
                'cached' => true,
                'data' => $stores
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching stores: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stores'
            ], 500);
        }
    }

    /**
     * Get all products from vendor_products table (for dropdown)
     */
//    public function getProducts(Request $request)
//    {
//        try {
//            $storeId = $request->input('storeId', '');
//
//            $query = Cache::remember('products', 600, function () {
//                return VendorProduct::query()
//                    ->select('id', 'name', 'vendorID')
//                    ->where('publish', 1)
//                    ->orderBy('name', 'asc');
//            });
//
//            if ($storeId) {
//                $query->where('vendorID', $storeId);
//            }
//
//            $products = $query->get();
//
//            return response()->json([
//                'success' => true,
//                'data' => $products
//            ]);
//        } catch (\Exception $e) {
//            \Log::error('Error fetching products: ' . $e->getMessage());
//            return response()->json([
//                'success' => false,
//                'message' => 'Error fetching products'
//            ], 500);
//        }
//    }
    public function getProducts(Request $request)
    {
        try {
            $storeId = $request->input('storeId', '');

            $cacheKey = $storeId
                ? "products:list:vendor:$storeId"
                : "products:list:all";

            $products = Cache::remember($cacheKey, 600, function () use ($storeId) {
                $q = VendorProduct::query()
                    ->select('id', 'name', 'vendorID')
                    ->where('publish', 1)
                    ->orderBy('name', 'asc');

                if ($storeId) {
                    $q->where('vendorID', $storeId);
                }

                return $q->get();
            });

            return response()->json([
                'success' => true,
                'cached' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching products: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching products'
            ], 500);
        }
    }


    /**
     * Get all zones for dropdown
     */
    public function getZones()
    {
        try {
            $zones = Cache::remember('bannerZones:list', now()->addMinutes(10), function () {
                return Zone::query()
                    ->select('id', 'name')
                    ->where('publish', 1)
                    ->orderBy('name', 'asc')
                    ->get();
            });

            return response()->json([
                'success' => true,
                'cached' => true,
                'data' => $zones
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching zones', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching zones'
            ], 500);
        }
    }

}
