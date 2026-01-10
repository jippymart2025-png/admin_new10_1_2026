<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\GiftCard;

class GiftCardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        return view("gift_card.index");

    }

    public function save($id="")
    {
        return view('gift_card.save')->with('id', $id);
    }
    public function edit($id)
    {
        return view('gift_card.save')->with('id', $id);
    }

    // Get placeholder image from settings
    private function getPlaceholderImage()
    {
        try {
            $placeholder = DB::table('settings')
                ->where('document_name', 'placeholder_image')
                ->value('image');

            if ($placeholder) {
                // Check if it's already a full URL
                if (strpos($placeholder, 'http') === 0) {
                    return $placeholder;
                }
                // Otherwise, treat it as a storage path
                return asset('storage/' . ltrim($placeholder, '/'));
            }

            // Fallback to default placeholder
            return asset('images/placeholder.png');
        } catch (\Exception $e) {
            \Log::error('Error getting placeholder image:', ['error' => $e->getMessage()]);
            return asset('images/placeholder.png');
        }
    }

    // DataTables
    public function data(Request $request)
    {
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $draw = (int) $request->input('draw', 1);
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));

        // Base query for total
        $baseQ = DB::table('gift_cards');
        $totalRecords = $baseQ->count();

        // Filtered query
        $q = DB::table('gift_cards as g');
        if ($search !== '') {
            $q->where(function($qq) use ($search){
                $qq->where('g.title','like','%'.$search.'%')
                   ->orWhere('g.message','like','%'.$search.'%')
                   ->orWhere('g.expiryDay','like','%'.$search.'%');
            });
        }

        $filteredRecords = (clone $q)->count();
        $rows = $q->orderBy('g.title','asc')->offset($start)->limit($length)->get();

        $canDelete = in_array('gift-card.delete', json_decode(@session('user_permissions'), true) ?: []);
        $placeholder = $this->getPlaceholderImage();

        $data = [];
        foreach ($rows as $r) {
            $editUrl = route('gift-card.edit', $r->id);

            // Use placeholder if no image
            $img = $r->image ?: $placeholder;
            if ($r->image && strpos($r->image, 'http') !== 0 && strpos($r->image, 'storage/') !== 0) {
                // If it's a relative path, convert it
                $img = asset('storage/' . ltrim($r->image, '/'));
            }

            $imgHtml = '<img onerror="this.onerror=null;this.src=\''.$placeholder.'\'" class="rounded" width="100%" style="width:70px;height:70px;" src="'.$img.'" alt="image">';
            $titleHtml = $imgHtml.'<a href="'.$editUrl.'">'.e($r->title ?: '').'</a>';
            $expiry = e($r->expiryDay ?: 0).' Days';
            $toggle = $r->isEnable ? '<label class="switch"><input type="checkbox" checked data-id="'.$r->id.'" class="toggle-enable"><span class="slider round"></span></label>'
                                   : '<label class="switch"><input type="checkbox" data-id="'.$r->id.'" class="toggle-enable"><span class="slider round"></span></label>';
            $actionsHtml = '<span class="action-btn"><a href="'.$editUrl.'"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>';
            if ($canDelete) {
                $actionsHtml .= ' <a href="javascript:void(0)" data-id="'.$r->id.'" class="delete-gift"><i class="mdi mdi-delete" title="Delete"></i></a>';
            }
            $actionsHtml .= '</span>';
            if ($canDelete) {
                $select = '<td class="delete-all"><input type="checkbox" class="is_open" dataId="'.$r->id.'"><label class="col-3 control-label"></label></td>';
                $data[] = [ $select, $titleHtml, $expiry, $toggle, $actionsHtml ];
            } else {
                $data[] = [ $titleHtml, $expiry, $toggle, $actionsHtml ];
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
        $g = GiftCard::find($id);
        if(!$g) return response()->json(['error'=>'Not found'],404);
        return response()->json($g);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'=>'required|string|max:255',
            'message'=>'required|string',
            'expiryDay'=>'required|integer|min:1',
            'image'=>'nullable|image',
        ]);
        $id = (string) Str::uuid();
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/gift_cards', 'public');
            $imageUrl = $path;
        }
        GiftCard::create([
            'id'=>$id,
            'title'=>$request->input('title'),
            'message'=>$request->input('message'),
            'expiryDay'=>$request->input('expiryDay'),
            'isEnable'=>$request->boolean('isEnable')?1:0,
            'image'=>$imageUrl,
            'createdAt'=>now()->format('Y-m-d H:i:s'),
        ]);

        // Log activity
        \Log::info('✅ Gift card created:', ['id' => $id, 'title' => $request->input('title')]);

        return response()->json(['success'=>true,'id'=>$id]);
    }

    public function update(Request $request, $id)
    {
        $g = GiftCard::findOrFail($id);
        $request->validate([
            'title'=>'required|string|max:255',
            'message'=>'required|string',
            'expiryDay'=>'required|integer|min:1',
            'image'=>'nullable|image',
        ]);
        $imageUrl = $g->image;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('uploads/gift_cards', 'public');
            $imageUrl = $path;
        }
        $g->update([
            'title'=>$request->input('title'),
            'message'=>$request->input('message'),
            'expiryDay'=>$request->input('expiryDay'),
            'isEnable'=>$request->boolean('isEnable',$g->isEnable)?1:0,
            'image'=>$imageUrl,
        ]);

        // Log activity
        \Log::info('✅ Gift card updated:', ['id' => $id, 'title' => $request->input('title')]);

        return response()->json(['success'=>true]);
    }

    public function toggle($id, Request $request)
    {
        $g = GiftCard::findOrFail($id);
        $newStatus = $request->boolean('isEnable') ? 1 : 0;
        $g->isEnable = $newStatus;
        $g->save();

        // Log activity
        $action = $newStatus ? 'enabled' : 'disabled';
        \Log::info("✅ Gift card $action:", ['id' => $id, 'title' => $g->title, 'status' => $newStatus]);

        return response()->json(['success'=>true,'id'=>$g->id,'isEnable'=>(bool)$g->isEnable]);
    }

    public function destroy($id)
    {
        $g = GiftCard::find($id);
        if(!$g) {
            \Log::error('❌ Gift card not found for deletion:', ['id' => $id]);
            return response()->json(['success'=>false, 'message'=>'Gift card not found'],404);
        }

        $title = $g->title;
        $g->delete();

        // Log activity
        \Log::info('✅ Gift card deleted:', ['id' => $id, 'title' => $title]);

        return response()->json(['success'=>true, 'message'=>'Gift card deleted successfully']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        if (empty($ids)) return response()->json(['success'=>false,'message'=>'No ids'],400);

        $deleted = GiftCard::whereIn('id',$ids)->delete();

        // Log activity
        \Log::info('✅ Gift cards bulk deleted:', ['count' => $deleted, 'ids' => $ids]);

        return response()->json(['success'=>true, 'deleted'=>$deleted]);
    }

}
