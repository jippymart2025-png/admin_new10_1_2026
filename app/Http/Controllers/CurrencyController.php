<?php


namespace App\Http\Controllers;

use App\Models\Currency;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CurrencyController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

	    public function index()
    {
       return view("settings.currencies.index");
    }

    public function data(Request $request)
    {
        $userPermissions = json_decode(@session('user_permissions'), true) ?: [];
        $canDelete = in_array('currency.delete', $userPermissions);

        $draw = (int) $request->input('draw', 1);
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $searchValue = strtolower((string) data_get($request->input('search'), 'value', ''));
        $orderColumnIdx = (int) data_get($request->input('order'), '0.column', 0);
        $orderDir = strtolower((string) data_get($request->input('order'), '0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        // Map DataTables column index to DB columns (considering optional delete checkbox)
        // Columns when delete allowed: [checkbox], name, symbol, code, symbolAtRight, isActive, actions
        // Columns when delete not allowed: name, symbol, code, symbolAtRight, isActive, actions
        $columnMapWithDelete = [null, 'name', 'symbol', 'code', 'symbolAtRight', 'isActive'];
        $columnMapNoDelete = ['name', 'symbol', 'code', 'symbolAtRight', 'isActive'];
        $columnMap = $canDelete ? $columnMapWithDelete : $columnMapNoDelete;
        $orderByColumn = $columnMap[$orderColumnIdx] ?? 'name';

        $baseQuery = Currency::query();

        $total = (clone $baseQuery)->count();

        if ($searchValue !== '') {
            $baseQuery->where(function($q) use ($searchValue) {
                $q->where(DB::raw('LOWER(name)'), 'like', "%{$searchValue}%")
                  ->orWhere(DB::raw('LOWER(code)'), 'like', "%{$searchValue}%")
                  ->orWhere(DB::raw('LOWER(symbol)'), 'like', "%{$searchValue}%");
            });
        }

        $filtered = (clone $baseQuery)->count();

        if (in_array($orderByColumn, ['name','code','symbol','symbolAtRight','isActive'])) {
            $baseQuery->orderBy($orderByColumn, $orderDir);
        } else {
            $baseQuery->orderBy('name', 'asc');
        }

        $rows = $baseQuery->skip($start)->take($length)->get();

        $data = [];
        foreach ($rows as $row) {
            $editUrl = route('currencies.edit', $row->id);
            $nameHtml = '<a href="'.$editUrl.'">'.e($row->name).'</a>';
            $symbolRightHtml = $row->symbolAtRight ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>';
            $activeSwitch = '<label class="switch"><input type="checkbox" '.($row->isActive ? 'checked ' : '').'id="'.$row->id.'" name="isSwitch"><span class="slider round"></span></label>';
            $actionsHtml = '<span class="action-btn"><a href="'.$editUrl.'"><i class="mdi mdi-lead-pencil" title="Edit"></i></a>'
                .($canDelete ? ' <a href="'.route('currencies.delete',$row->id).'" class="delete-btn"><i class="mdi mdi-delete"></i></a>' : '')
                .'</span>';

            if ($canDelete) {
                $checkbox = '<td class="delete-all"><input type="checkbox" class="is_open" dataId="'.$row->id.'"><label class="col-3 control-label"></label></td>';
                $data[] = [
                    $checkbox,
                    $nameHtml,
                    e($row->symbol),
                    e($row->code),
                    $symbolRightHtml,
                    $activeSwitch,
                    $actionsHtml,
                ];
            } else {
                $data[] = [
                    $nameHtml,
                    e($row->symbol),
                    e($row->code),
                    $symbolRightHtml,
                    $activeSwitch,
                    $actionsHtml,
                ];
            }
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

  public function edit($id)
    {
        $currency = Currency::findOrFail($id);
    	return view('settings.currencies.edit', ['id' => $id, 'currency' => $currency]);
    }

    public function create(){
       return view('settings.currencies.create');

    }

    public function store(Request $request, ActivityLogger $logger)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'symbol' => 'required|string|max:255',
            'decimal_degits' => 'nullable|integer|min:0',
            'isActive' => 'nullable|boolean',
            'symbolAtRight' => 'nullable|boolean',
        ]);

        $id = (string) Str::uuid();

        DB::transaction(function() use (&$data, $id) {
            if (!empty($data['isActive'])) {
                Currency::query()->update(['isActive' => false]);
            }
            Currency::create([
                'id' => $id,
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'symbol' => $data['symbol'],
                'decimal_degits' => $data['decimal_degits'] ?? 0,
                'isActive' => (bool) ($data['isActive'] ?? false),
                'symbolAtRight' => (bool) ($data['symbolAtRight'] ?? false),
            ]);
        });

        $logger->log(auth()->user(), 'currencies', 'created', 'Created new currency: '.$data['name'], $request);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function update(Request $request, $id, ActivityLogger $logger)
    {
        $currency = Currency::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'symbol' => 'required|string|max:255',
            'decimal_degits' => 'nullable|integer|min:0',
            'isActive' => 'nullable|boolean',
            'symbolAtRight' => 'nullable|boolean',
        ]);

        $activate = (bool) ($data['isActive'] ?? false);

        DB::transaction(function() use ($currency, $data, $activate) {
            if ($activate) {
                Currency::where('id', '!=', $currency->id)->update(['isActive' => false]);
            } else {
                // Prevent disabling the last active currency
                $activeCount = Currency::where('isActive', true)->count();
                if ($activeCount === 1 && $currency->isActive) {
                    abort(response()->json(['message' => "You can't disable all currencies. At least one currency must be active."], 422));
                }
            }

            $currency->update([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'symbol' => $data['symbol'],
                'decimal_degits' => $data['decimal_degits'] ?? 0,
                'isActive' => $activate,
                'symbolAtRight' => (bool) ($data['symbolAtRight'] ?? false),
            ]);
        });

        $logger->log(auth()->user(), 'currencies', 'updated', 'Updated currency: '.$data['name'], $request);

        return response()->json(['success' => true]);
    }

    public function delete($id, ActivityLogger $logger)
    {
        $currency = Currency::findOrFail($id);

        if ($currency->isActive && Currency::where('isActive', true)->count() === 1) {
            return redirect()->back()->withErrors(["You can't delete the only active currency."]);
        }

        $currencyName = $currency->name;
        $currency->delete();

        $logger->log(auth()->user(), 'currencies', 'deleted', 'Deleted currency: '.$currencyName, request());

        return redirect()->back()->with('success', 'Currency deleted successfully.');
    }

    public function getActiveCurrency()
    {
        $currency = Currency::where('isActive', true)->first();

        if (!$currency) {
            return response()->json([
                'success' => false,
                'message' => 'No active currency found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'name' => $currency->name,
                'symbol' => $currency->symbol,
                'code' => $currency->code,
                'symbolAtRight' => (bool) $currency->symbolAtRight,
                'decimal_degits' => (int) ($currency->decimal_degits ?? 0),
            ],
        ]);
    }

    public function toggle(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);
        $isActive = $request->boolean('isActive');

        if ($isActive) {
            DB::transaction(function() use ($currency) {
                Currency::where('id', '!=', $currency->id)->update(['isActive' => false]);
                $currency->update(['isActive' => true]);
            });
            return response()->json(['success' => true, 'activated' => $currency->id]);
        }

        // Disabling: ensure at least one stays active
        if (Currency::where('isActive', true)->count() === 1 && $currency->isActive) {
            return response()->json(['success' => false, 'message' => 'Can not disable all currency'], 422);
        }

        $currency->update(['isActive' => false]);
        return response()->json(['success' => true]);
    }
}
