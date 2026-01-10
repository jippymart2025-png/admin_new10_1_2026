<?php

namespace App\Http\Controllers;

use App\Services\FirebaseStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanController extends Controller
{

    public function __construct(FirebaseStorageService $firebaseStorage)
    {
        $this->middleware('auth');
        $this->firebaseStorage = $firebaseStorage;
    }

    public function index()
    {
        return view("subscription_plans.index");
    }

    public function save($id='')
    {
        return view("subscription_plans.save")->with('id',$id);
    }

    /**
     * DataTables server-side data for subscription plans
     */
    public function data(Request $request)
    {
        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $draw = (int) $request->input('draw', 1);
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));

        $q = DB::table('subscription_plans');

        if ($search !== '') {
            $q->where(function($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                      ->orWhere('price', 'like', '%'.$search.'%')
                      ->orWhere('expiryDay', 'like', '%'.$search.'%');
            });
        }

        $total = (clone $q)->count();

        // Order by place first (to ensure free plan is first), then by name
        $rows = $q->orderBy('place', 'asc')
                  ->orderBy('name', 'asc')
                  ->offset($start)
                  ->limit($length)
                  ->get();

        $canDelete = in_array('subscription-plans.delete', json_decode(@session('user_permissions'), true) ?: []);

        $data = [];
        foreach ($rows as $r) {
            $row = [];

            // Get subscriber count
            $subscriberCount = DB::table('vendors')
                ->where('subscriptionPlanId', $r->id)
                ->count();

            $editUrl = route('subscription-plans.save', $r->id);
            $subscriberUrl = route('current-subscriber.list', $r->id);

            // Image
            $imageHtml = $r->image
                ? '<img alt="" width="70" height="70" src="'.$r->image.'">'
                : '<img alt="" width="70" height="70" src="">';

            // Checkbox column (if has delete permission and not free plan)
            if ($canDelete) {
                if ($r->id != 'J0RwvxCWhZzQQD7Kc2Ll') {
                    $row[] = '<input type="checkbox" class="is_open" dataId="'.$r->id.'">';
                } else {
                    $row[] = '';
                }
            }

            // Name with image
            $row[] = $imageHtml . '<a href="'.$subscriberUrl.'">'.e($r->name).'</a>';

            // Price
            if ($r->type == 'free') {
                $row[] = '<span style="color:red;">Free</span>';
            } else {
                $row[] = '₹' . number_format($r->price, 2);
            }

            // Duration
            $row[] = $r->expiryDay == '-1' ? 'Unlimited' : $r->expiryDay . ' Days';

            // ✅ Commission (place column)
            $row[] = ($r->place !== null && $r->place !== '')
                ? $r->place . '%'
                : '-';

//            $row[] = $r->zone;

            $zoneText = '-';

            if (!empty($r->zone)) {
                $zoneIds = json_decode($r->zone, true);

                if (is_array($zoneIds) && count($zoneIds) > 0) {
                    $zoneNames = DB::table('zone')
                        ->whereIn('id', $zoneIds)
                        ->pluck('name')
                        ->toArray();

                    if (!empty($zoneNames)) {
                        $zoneText = implode(', ', $zoneNames);
                    }
                }
            }

            $row[] = e($zoneText);



            // Subscribers
            $row[] = '<a href="'.$subscriberUrl.'">'.$subscriberCount.'</a>';

            // Status toggle (not for free plan)
            if ($r->id != 'J0RwvxCWhZzQQD7Kc2Ll') {
                $checked = $r->isEnable ? 'checked' : '';
                $row[] = '<label class="switch"><input type="checkbox" '.$checked.' class="plan-toggle" data-id="'.$r->id.'"><span class="slider round"></span></label>';
            } else {
                $row[] = '';
            }

            // Actions
            $actions = '<span class="action-btn"><a href="'.$editUrl.'"><i class="mdi mdi-lead-pencil"></i></a>';
            if ($canDelete && $r->id != 'J0RwvxCWhZzQQD7Kc2Ll') {
                $actions .= ' <a href="javascript:void(0)" class="delete-plan" data-id="'.$r->id.'"><i class="mdi mdi-delete"></i></a>';
            }
            $actions .= '</span>';
            $row[] = $actions;

            $data[] = $row;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }

    /**
     * Get subscription plan by ID (JSON)
     */
    public function showJson($id)
    {
        $plan = DB::table('subscription_plans')->where('id', $id)->first();
        if (!$plan) return response()->json(['error'=>'Not found'], 404);
        return response()->json($plan);
    }

    /**
     * Create  subscription plan
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required',
//            'type' => 'required|in:free,paid',
            'plan_type' => 'required|in:commission,subscription',
            'expiryDay' => 'nullable',
            'photo' => 'nullable|image|max:2048',
            'zone' => 'nullable|string|max:255',
        ]);

        $id = (string) \Illuminate\Support\Str::uuid();

        $imageUrl = null;
        if ($request->hasFile('photo')) {
            $imageUrl = $this->firebaseStorage->uploadFile(
                $request->file('photo'),
                'subscription-plans/plan_' . time() . '.' .
                $request->file('photo')->getClientOriginalExtension()
            );
        }
        $planType = $request->input('plan_type', 'subscription');

        DB::table('subscription_plans')->insert([
            'id' => $id,
            'name' => $request->name,
            'price' => $request->price,
//            'type' => $request->type,
            'plan_type'   => $planType,
            'expiryDay' => $request->expiryDay,
            'description' => $request->description ?? '',
            'image' => $imageUrl,
            'features' => $request->features ?? '',
            'plan_points' => $request->plan_points ?? '',
            'itemLimit' => $request->itemLimit,
            'orderLimit' => $request->orderLimit,
            'isEnable' => $request->boolean('isEnable', true),
            'place' => $request->place ?? 0,
            'zone' => $request->zone ?? '',
            'createdAt' => now()->format('Y-m-d H:i:s'),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
//            'type' => 'required|in:free,paid',
            'price' => 'required|numeric|min:0',
            'plan_type' => 'required|in:commission,subscription',
            'expiryDay' => 'required',
            'photo' => 'nullable|image|max:2048',
            'zone' => 'nullable|string|max:255',
        ]);

        $plan = DB::table('subscription_plans')->where('id', $id)->first();
        if (!$plan) {
            return response()->json(['success' => false], 404);
        }

        $imageUrl = $plan->image;

        if ($request->hasFile('photo')) {
            $imageUrl = $this->firebaseStorage->uploadFile(
                $request->file('photo'),
                'subscription-plans/plan_' . time() . '.' .
                $request->file('photo')->getClientOriginalExtension()
            );
        }

        $planType = $request->input('plan_type');

        DB::table('subscription_plans')
            ->where('id', $id)
            ->update([
                'name' => $request->name,
//                'type' => $request->type,
                'plan_type'   => $planType,
                'price' => $request->type === 'free' ? 0 : $request->price,
                'expiryDay' => $request->expiryDay,
                'description' => $request->description ?? '',
                'image' => $imageUrl,
                'features' => $request->features ?? '',
                'plan_points' => $request->plan_points ?? '',
                'zone' => $request->zone ?? '',
                'itemLimit' => $request->filled('itemLimit') ? (int) $request->itemLimit : 0,
                'orderLimit' => $request->filled('orderLimit') ? (int) $request->orderLimit : 0,
                'isEnable' => $request->boolean('isEnable', true),
                'place' => $request->place ?? 0,
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Toggle plan status
     */
    public function toggleStatus(Request $request, $id)
    {
        $isEnable = $request->boolean('isEnable');

        // If disabling, check if at least one other plan is enabled
        if (!$isEnable) {
            $enabledCount = DB::table('subscription_plans')
                ->where('isEnable', 1)
                ->where('id', '!=', 'J0RwvxCWhZzQQD7Kc2Ll') // Exclude free plan
                ->where('id', '!=', $id)
                ->count();

            if ($enabledCount == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'At least one subscription plan should be active'
                ], 422);
            }
        }

        DB::table('subscription_plans')->where('id', $id)->update(['isEnable' => $isEnable]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete subscription plan
     */
    public function destroy($id)
    {
        // Don't allow deleting the free plan
        if ($id == 'J0RwvxCWhZzQQD7Kc2Ll') {
            return response()->json(['success' => false, 'message' => 'Cannot delete free plan'], 422);
        }

        DB::table('subscription_plans')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Get subscription earnings by plan
     * Table: subscription_history with subscription_plan as TEXT/JSON
     */
    public function getEarnings($planId)
    {
        try {
            $total = 0;
            $records = DB::table('subscription_history')
                ->whereRaw("subscription_plan LIKE ?", ['%"id":"'.$planId.'"%'])
                ->get();

            foreach ($records as $record) {
                $planData = json_decode($record->subscription_plan, true);
                if ($planData && isset($planData['price'])) {
                    $total += floatval($planData['price']);
                }
            }

            return response()->json(['total' => $total]);
        } catch (\Exception $e) {
            \Log::error('Error calculating earnings: ' . $e->getMessage());
            return response()->json(['total' => 0]);
        }
    }

    /**
     * Get overview data for all plans
     * Table: subscription_history with subscription_plan as TEXT/JSON
     */
    public function getOverview()
    {
        try {
            $plans = DB::table('subscription_plans')
//                ->where('id', '!=', 'd5883a92-dfd3-11f0-827a-10ffe083f1e8')
                ->get();

            $overview = [];
            foreach ($plans as $plan) {
                $earnings = 0;
                $records = DB::table('subscription_history')
                    ->whereRaw("subscription_plan LIKE ?", ['%"id":"'.$plan->id.'"%'])
                    ->get();

                foreach ($records as $record) {
                    $planData = json_decode($record->subscription_plan, true);
                    if ($planData && isset($planData['price'])) {
                        $earnings += floatval($planData['price']);
                    }
                }

                $overview[] = [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'image' => $plan->image,
                    'earnings' => $earnings
                ];
            }

            return response()->json($overview);
        } catch (\Exception $e) {
            \Log::error('Error getting overview: ' . $e->getMessage());
            return response()->json([]);
        }
    }

}
