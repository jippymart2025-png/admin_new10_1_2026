<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Document;

class DocumentController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Log permissions for debugging
        $userPermissions = json_decode(session('user_permissions'), true) ?: [];
        \Log::info('📋 Documents index accessed:', [
            'user_id' => auth()->id(),
            'permissions' => $userPermissions,
            'has_documents' => in_array('documents', $userPermissions),
            'has_documents_delete' => in_array('documents.delete', $userPermissions)
        ]);

        return view('documents.index');
    }
    public function create()
    {
        return view("documents.create");
    }
    public function edit($id)
    {
        return view("documents.edit")->with('id',$id);
    }

    // DataTables
    public function data(Request $request)
    {
        try {
            \Log::info('📡 Documents data request received:', ['user' => auth()->id(), 'params' => $request->all()]);

            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $draw = (int) $request->input('draw', 1);
            $search = strtolower((string) data_get($request->input('search'), 'value', ''));

            // Base query for total
            $baseQ = DB::table('documents');
            $totalRecords = $baseQ->count();

            // Filtered query
            $q = DB::table('documents');
            if ($search !== '') {
                $q->where(function($qq) use ($search){
                    $qq->where('title','like','%'.$search.'%')
                       ->orWhere('type','like','%'.$search.'%');
                });
            }

            $filteredRecords = (clone $q)->count();
            $rows = $q->orderBy('title','asc')->offset($start)->limit($length)->get();

            $canDelete = in_array('documents.delete', json_decode(@session('user_permissions'), true) ?: []);
            $data = [];
            foreach ($rows as $r) {
                $editUrl = route('documents.edit', $r->id);
                $title = '<a href="'.$editUrl.'" class="redirecttopage">'.e($r->title ?: '').'</a>';
                $type = e($r->type ?: '');
                $toggle = $r->enable ? '<label class="switch"><input type="checkbox" checked data-id="'.$r->id.'" class="toggle-enable"><span class="slider round"></span></label>'
                                     : '<label class="switch"><input type="checkbox" data-id="'.$r->id.'" class="toggle-enable"><span class="slider round"></span></label>';
                $actionsHtml = '<span class="action-btn"><a href="'.$editUrl.'"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>';
                if ($canDelete) {
                    $actionsHtml .= ' <a href="javascript:void(0)" data-id="'.$r->id.'" class="delete-document"><i class="mdi mdi-delete" title="Delete"></i></a>';
                }
                $actionsHtml .= '</span>';
                if ($canDelete) {
                    $select = '<td class="delete-all"><input type="checkbox" class="is_open" dataId="'.$r->id.'"><label class="col-3 control-label"></label></td>';
                    $data[] = [ $select, $title, $type, $toggle, $actionsHtml ];
                } else {
                    $data[] = [ $title, $type, $toggle, $actionsHtml ];
                }
            }

            \Log::info('✅ Documents data loaded successfully:', ['total' => $totalRecords, 'filtered' => $filteredRecords]);

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
            \Log::error('❌ Error loading documents data:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function json($id)
    {
        $d = Document::find($id);
        if(!$d) return response()->json(['error'=>'Not found'],404);
        return response()->json($d);
    }

    public function store(Request $request)
    {
        \Log::info('📥 Document store request:', $request->all());

        $request->validate([
            'title'=>'required|string|max:255',
            'type'=>'required|string|in:restaurant,driver',
            'frontSide'=>'nullable|boolean',
            'backSide'=>'nullable|boolean',
        ]);

        $id = (string) Str::uuid();
        Document::create([
            'id'=>$id,
            'title'=>$request->input('title'),
            'type'=>$request->input('type'),
            'frontSide'=>$request->boolean('frontSide')?1:0,
            'backSide'=>$request->boolean('backSide')?1:0,
            'enable'=>$request->boolean('enable')?1:0,
        ]);

        // Log activity
        \Log::info('✅ Document created:', ['id' => $id, 'title' => $request->input('title')]);

        return response()->json(['success'=>true,'id'=>$id]);
    }

    public function update(Request $request, $id)
    {
        \Log::info('📥 Document update request:', ['id' => $id, 'data' => $request->all()]);

        $d = Document::findOrFail($id);

        $request->validate([
            'title'=>'required|string|max:255',
            'type'=>'required|string|in:restaurant,driver',
            'frontSide'=>'nullable|boolean',
            'backSide'=>'nullable|boolean',
        ]);

        $d->update([
            'title'=>$request->input('title'),
            'type'=>$request->input('type'),
            'frontSide'=>$request->boolean('frontSide')?1:0,
            'backSide'=>$request->boolean('backSide')?1:0,
            'enable'=>$request->boolean('enable')?1:0,
        ]);

        // Log activity
        \Log::info('✅ Document updated:', ['id' => $id, 'title' => $request->input('title')]);

        return response()->json(['success'=>true]);
    }

    public function toggle($id, Request $request)
    {
        $d = Document::findOrFail($id);
        $newStatus = $request->boolean('enable') ? 1 : 0;
        $d->enable = $newStatus;
        $d->save();

        // Log activity
        $action = $newStatus ? 'enabled' : 'disabled';
        \Log::info("✅ Document $action:", ['id' => $id, 'title' => $d->title, 'status' => $newStatus]);

        return response()->json(['success'=>true,'id'=>$d->id,'enable'=>(bool)$d->enable]);
    }

    public function destroy($id)
    {
        $d = Document::find($id);
        if(!$d) {
            \Log::error('❌ Document not found for deletion:', ['id' => $id]);
            return response()->json(['success'=>false, 'message'=>'Document not found'],404);
        }

        $title = $d->title;
        $d->delete();

        // Log activity
        \Log::info('✅ Document deleted:', ['id' => $id, 'title' => $title]);

        return response()->json(['success'=>true, 'message'=>'Document deleted successfully']);
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        if (empty($ids)) return response()->json(['success'=>false,'message'=>'No ids'],400);

        $deleted = Document::whereIn('id',$ids)->delete();

        // Log activity
        \Log::info('✅ Documents bulk deleted:', ['count' => $deleted, 'ids' => $ids]);

        return response()->json(['success'=>true, 'deleted'=>$deleted]);
    }
}
