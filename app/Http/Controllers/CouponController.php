<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Coupon;
use App\Models\Vendor;
use App\Models\Currency;

class CouponController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id='')
    {
        return view("coupons.index")->with('id',$id);
    }

    public function edit($id)
    {
        $vendors = Vendor::select('id','title','vType')->orderBy('title','asc')->get();
        return view('coupons.edit')->with(['id'=>$id,'vendors'=>$vendors]);
    }

    public function create($id='')
    {
        $vendors = Vendor::select('id','title','vType')->orderBy('title','asc')->get();
        return view('coupons.create')->with(['id'=>$id,'vendors'=>$vendors]);
    }

    // DataTables
    public function data(Request $request)
    {
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $draw = (int) $request->input('draw', 1);
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));
        $vendorId = (string) $request->input('vendorId','');
        $filterType = (string) $request->input('couponType','');

        // Base query for total
        $baseQ = DB::table('coupons');
        $totalRecords = $baseQ->count();

        // Filtered query
        $q = DB::table('coupons as c')
            ->leftJoin('vendors as v','v.id','=','c.resturant_id')
            ->select('c.*','v.title as vendorTitle','v.vType as vendorType');

        if ($vendorId !== '') {
            $q->where(function($qq) use ($vendorId){
                $qq->where('c.resturant_id',$vendorId)->orWhere('c.resturant_id','ALL');
            });
        }
        if ($filterType !== '') {
            $q->where('c.cType',$filterType);
        }
        if ($search !== '') {
            $q->where(function($qq) use ($search){
                $qq->where('c.code','like','%'.$search.'%')
                   ->orWhere('c.description','like','%'.$search.'%')
                   ->orWhere('v.title','like','%'.$search.'%')
                   ->orWhere('c.cType','like','%'.$search.'%')
                   ->orWhere('c.discount','like','%'.$search.'%');
            });
        }

        $filteredRecords = (clone $q)->count();
        $rows = $q->orderBy('c.expiresAt','desc')->offset($start)->limit($length)->get();

        $canDelete = in_array('coupons.delete', json_decode(@session('user_permissions'), true) ?: []);
        $currency = Currency::where('isActive',1)->first();
        $symbol = $currency->symbol ?? '';
        $symbolAtRight = (bool) ($currency->symbolAtRight ?? false);

        $eid = $vendorId; // for back link
        $data = [];
        foreach ($rows as $r) {
            $editUrl = route('coupons.edit',$r->id) . ($eid ? ('?eid='.$eid) : '');
            $code = '<a href="'.$editUrl.'" class="redirecttopage">'.e($r->code ?: '').'</a>';
            $discount = (string) ($r->discount ?? '0');
            if (strcasecmp($r->discountType,'Percentage')===0 || strcasecmp($r->discountType,'Percent')===0) {
                $discountText = $discount.'%';
            } else {
                $discountText = $symbolAtRight ? ($discount.$symbol) : ($symbol.$discount);
            }
            $itemValue = $r->item_value !== null ? (string)$r->item_value : '';
            $usageLimit = '<span style="display:none;">'.(int)($r->usageLimit ?? 0).'</span>';
            $privacy = $r->isPublic ? '<td class="success"><span class="badge badge-success py-2 px-3">'.trans('lang.public').'</span></td>'
                                    : '<td class="danger"><span class="badge badge-danger py-2 px-3">'.trans('lang.private').'</span></td>';
            $ctype = e($r->cType ?: '');
            $restaurant = 'All';
            if (($r->resturant_id ?? '') === 'ALL') {
                $restaurant = ($ctype==='mart') ? 'All marts' : 'All Restaurants';
            } else {
                $restaurant = e($r->vendorTitle ?: '');
            }

            // Format date like: Oct 19, 2025 11:27 PM
            $expires = '';
            if ($r->expiresAt) {
                try {
                    $date = new \DateTime($r->expiresAt);
                    $expires = $date->format('M j, Y g:i A');
                } catch (\Exception $e) {
                    $expires = e($r->expiresAt);
                }
            }

            $expired = strtotime($r->expiresAt ?? '') !== false ? (time() > strtotime($r->expiresAt)) : false;
            $toggle = $r->isEnabled ? '<label class="switch"><input type="checkbox" '.($expired?'disabled ':'').'checked data-id="'.$r->id.'" class="toggle-enable"><span class="slider round"></span></label>'
                                    : '<label class="switch"><input type="checkbox" '.($expired?'disabled ':'').'data-id="'.$r->id.'" class="toggle-enable"><span class="slider round"></span></label>';
            $desc = e($r->description ?: '');
            $actionsHtml = '<span class="action-btn"><a href="'.$editUrl.'"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>';
            if ($canDelete) {
                $actionsHtml .= ' <a href="javascript:void(0)" data-id="'.$r->id.'" class="delete-coupon"><i class="mdi mdi-delete" title="Delete"></i></a>';
            }
            $actionsHtml .= '</span>';
            if ($canDelete) {
                $select = '<td class="delete-all"><input type="checkbox" class="is_open" dataId="'.$r->id.'"><label class="col-3 control-label"></label></td>';
                $data[] = [ $select, $code, $discountText, $itemValue, $usageLimit, $privacy, $ctype, $restaurant, $expires, $toggle, $desc, $actionsHtml ];
            } else {
                $data[] = [ $code, $discountText, $itemValue, $usageLimit, $privacy, $ctype, $restaurant, $expires, $toggle, $desc, $actionsHtml ];
            }
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
    }

    public function json($id)
    {
        $c = Coupon::find($id);
        if(!$c) return response()->json(['error'=>'Not found'],404);
        return response()->json($c);
    }

    public function store(Request $request)
    {
        \Log::info('📥 Coupon store request:', $request->all());

        $request->validate([
            'code'=>'required|string|max:255|unique:coupons,code',
            'discount'=>'required|numeric',
            'discountType'=>'required|string',
            'expiresAt'=>'required|string',
            'cType'=>'required|string',
            'resturant_id'=>'required|string',
            'item_value'=>'nullable|integer|min:0',
            'usageLimit'=>'nullable|integer|min:0',
            'image'=>'nullable|image',
        ]);

        // ✅ CUSTOM RULE: item_value must be greater than discount
        if ((float)$request->item_value <= (float)$request->discount) {
            return response()->json([
                'success' => false,
                'message' => 'Item value must be greater than discount value'
            ], 422);
        }


        $id = (string) Str::uuid();
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/coupons', 'public');
            $imageUrl = $path;
        }

        Coupon::create([
            'id'=>$id,
            'code'=>$request->input('code'),
            'description'=>$request->input('description',''),
            'discount'=>$request->input('discount'),
            'expiresAt'=>$request->input('expiresAt'),
            'discountType'=>$request->input('discountType'),
            'image'=>$imageUrl,
            'resturant_id'=>$request->input('resturant_id'),
            'cType'=>$request->input('cType'),
            'item_value'=>$request->input('item_value',0),
            'usageLimit'=>$request->input('usageLimit',0),
            'usedCount'=>0,
            'usedBy'=>'',
            'isPublic'=>$request->boolean('isPublic')?1:0,
            'isEnabled'=>$request->boolean('isEnabled')?1:0,
        ]);

        // Log activity
        \Log::info('✅ Coupon created:', ['id' => $id, 'code' => $request->input('code')]);

        return response()->json(['success'=>true,'id'=>$id]);
    }

    public function update(Request $request, $id)
    {
        \Log::info('📥 Coupon update request:', ['id' => $id, 'data' => $request->all()]);

        $c = Coupon::findOrFail($id);

        $request->validate([
            'code'=>'required|string|max:255|unique:coupons,code,'.$c->id.',id',
            'discount'=>'required|numeric',
            'discountType'=>'required|string',
            'expiresAt'=>'required|string',
            'cType'=>'required|string',
            'resturant_id'=>'required|string',
            'item_value'=>'nullable|integer|min:0',
            'usageLimit'=>'nullable|integer|min:0',
            'image'=>'nullable|image',
        ]);

        $imageUrl = $c->image;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/coupons', 'public');
            $imageUrl = $path;
        }

        $c->update([
            'code'=>$request->input('code'),
            'description'=>$request->input('description',''),
            'discount'=>$request->input('discount'),
            'expiresAt'=>$request->input('expiresAt'),
            'discountType'=>$request->input('discountType'),
            'image'=>$imageUrl,
            'resturant_id'=>$request->input('resturant_id'),
            'cType'=>$request->input('cType'),
            'item_value'=>$request->input('item_value',0),
            'usageLimit'=>$request->input('usageLimit',0),
            'isPublic'=>$request->boolean('isPublic')?1:0,
            'isEnabled'=>$request->boolean('isEnabled')?1:0,
        ]);

        // Log activity
        \Log::info('✅ Coupon updated:', ['id' => $id, 'code' => $request->input('code')]);

        return response()->json(['success'=>true]);
    }

    public function toggle($id, Request $request)
    {
        $c = Coupon::findOrFail($id);
        $newStatus = $request->boolean('isEnabled') ? 1 : 0;
        $c->isEnabled = $newStatus;
        $c->save();

        // Log activity
        $action = $newStatus ? 'enabled' : 'disabled';
        \Log::info("✅ Coupon $action:", ['id' => $id, 'code' => $c->code, 'status' => $newStatus]);

        return response()->json(['success'=>true,'id'=>$c->id,'isEnabled'=>(bool)$c->isEnabled]);
    }

    public function destroy($id)
    {
        $c = Coupon::find($id);
        if(!$c) {
            \Log::error('❌ Coupon not found for deletion:', ['id' => $id]);
            return response()->json(['success'=>false, 'message'=>'Coupon not found'],404);
        }

        $code = $c->code;
        $c->delete();

        // Log activity
        \Log::info('✅ Coupon deleted:', ['id' => $id, 'code' => $code]);

        return response()->json(['success'=>true, 'message'=>'Coupon deleted successfully']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        if (empty($ids)) return response()->json(['success'=>false,'message'=>'No ids'],400);

        $deleted = Coupon::whereIn('id',$ids)->delete();

        // Log activity
        \Log::info('✅ Coupons bulk deleted:', ['count' => $deleted, 'ids' => $ids]);

        return response()->json(['success'=>true, 'deleted'=>$deleted]);
    }
}


