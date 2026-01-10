<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DynamicNotificationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("dynamic_notifications.index");
    }


    public function save($id = null)
    {
        return view('dynamic_notifications.create')->with('id', $id);
    }

    /**
     * Data for DataTables (SQL-based)
     */
    public function data(Request $request)
    {
        try {
            $draw   = (int) $request->input('draw', 1);
            $start  = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $search = strtolower((string) data_get($request->input('search'), 'value', ''));

            $base = DB::table('dynamic_notification');
            $total = $base->count();

            $q = DB::table('dynamic_notification')->select('id','type','subject','message','createdAt');
            if ($search !== '') {
                $q->where(function($qq) use ($search){
                    $qq->whereRaw('LOWER(type) LIKE ?', ['%'.$search.'%'])
                       ->orWhereRaw('LOWER(subject) LIKE ?', ['%'.$search.'%'])
                       ->orWhereRaw('LOWER(message) LIKE ?', ['%'.$search.'%']);
                });
            }
            $q->orderBy('createdAt','desc');
            $rows = $q->offset($start)->limit($length)->get();
            
            // Get filtered count (need to clone query before offset/limit)
            $filteredQuery = DB::table('dynamic_notification');
            if ($search !== '') {
                $filteredQuery->where(function($qq) use ($search){
                    $qq->whereRaw('LOWER(type) LIKE ?', ['%'.$search.'%'])
                       ->orWhereRaw('LOWER(subject) LIKE ?', ['%'.$search.'%'])
                       ->orWhereRaw('LOWER(message) LIKE ?', ['%'.$search.'%']);
                });
            }
            $filtered = $filteredQuery->count();

            $data = [];
            foreach ($rows as $row) {
                $rowArr = [];
                
                // Format type with translations (matching buildHTML function logic)
                $type = '';
                $title = '';
                $typeValue = $row->type ?? '';
                
                // Use try-catch for translations to prevent errors if keys don't exist
                try {
                    if ($typeValue == "restaurant_rejected") {
                        $type = trans('lang.order_rejected_by_restaurant', [], 'en');
                        $title = trans('lang.order_reject_notification', [], 'en');
                    } else if ($typeValue == "restaurant_accepted") {
                        $type = trans('lang.order_accepted_by_restaurant', [], 'en');
                        $title = trans('lang.order_accept_notification', [], 'en');
                    } else if ($typeValue == "takeaway_completed") {
                        $type = trans('lang.takeaway_order_completed', [], 'en');
                        $title = trans('lang.takeaway_order_complete_notification', [], 'en');
                    } else if ($typeValue == "driver_completed") {
                        $type = trans('lang.driver_completed_order', [], 'en');
                        $title = trans('lang.order_complete_notification', [], 'en');
                    } else if ($typeValue == "driver_accepted") {
                        $type = trans('lang.driver_accepted_order', [], 'en');
                        $title = trans('lang.driver_accept_order_notification', [], 'en');
                    } else if ($typeValue == "dinein_canceled") {
                        $type = trans('lang.dine_order_book_canceled', [], 'en');
                        $title = trans('lang.dinein_cancel_notification', [], 'en');
                    } else if ($typeValue == "dinein_accepted") {
                        $type = trans('lang.dine_order_book_accepted', [], 'en');
                        $title = trans('lang.dinein_accept_notification', [], 'en');
                    } else if ($typeValue == "order_placed") {
                        $type = trans('lang.new_order_place', [], 'en');
                        $title = trans('lang.order_placed_notification', [], 'en');
                    } else if ($typeValue == "dinein_placed") {
                        $type = trans('lang.new_dine_booking', [], 'en');
                        $title = trans('lang.dinein_order_place_notification', [], 'en');
                    } else if ($typeValue == "schedule_order") {
                        $type = trans('lang.shedule_order', [], 'en');
                        $title = trans('lang.schedule_order_notification', [], 'en');
                    } else if ($typeValue == "payment_received") {
                        $type = trans('lang.pament_received', [], 'en');
                        $title = trans('lang.payment_receive_notification', [], 'en');
                    } else if ($typeValue == "driver_reached_doorstep") {
                        $type = "Driver reached your Doorstep";
                        $title = "Driver reached your Doorstep notification";
                    } else {
                        $type = $typeValue; // Use original value if no translation found
                        $title = ($typeValue ? $typeValue . ' notification' : 'Notification');
                    }
                } catch (\Exception $e) {
                    // Fallback if translation fails
                    $type = $typeValue;
                    $title = ($typeValue ? $typeValue . ' notification' : 'Notification');
                }
                
                $rowArr[] = e($type);
                $rowArr[] = e($row->subject ?? '');
                $rowArr[] = e($row->message ?? '');
                
                // Format date in Asia/Kolkata timezone - Format: "Thu Jul 24 2025 12:00:00 AM"
                $createdAt = '';
                if ($row->createdAt) {
                    try {
                        // Handle ISO 8601 format strings like "2025-07-23T18:30:00.306000Z"
                        $dateString = $row->createdAt;
                        
                        // Handle JSON-encoded strings (remove quotes)
                        if (is_string($dateString)) {
                            // Remove surrounding quotes if present
                            $dateString = trim($dateString, '"\'');
                            
                            // Try to decode if it's JSON
                            if (preg_match('/^["\'].*["\']$/', $dateString)) {
                                $decoded = json_decode($dateString, true);
                                if ($decoded !== null && !is_array($decoded)) {
                                    $dateString = $decoded;
                                }
                            }
                        }
                        
                        // Parse the date - Carbon can handle ISO 8601 format
                        $date = Carbon::parse($dateString);
                        
                        // Convert to Asia/Kolkata timezone
                        $date->setTimezone('Asia/Kolkata');
                        
                        // Format: "Thu Jul 24 2025 12:00:00 AM"
                        // D = Day name (Thu), M = Month (Jul), d = Day (24), Y = Year (2025)
                        // h = 12-hour format, i = minutes, s = seconds, A = AM/PM
                        $createdAt = $date->format('D M d Y h:i:s A');
                    } catch (\Exception $e) {
                        \Log::error('Date parsing error for createdAt: ' . ($row->createdAt ?? 'null') . ' - ' . $e->getMessage());
                        \Log::error('Stack trace: ' . $e->getTraceAsString());
                        // Fallback: try to format the original string or return as-is
                        $createdAt = $row->createdAt ?? '-';
                    }
                } else {
                    $createdAt = '-';
                }
                $rowArr[] = $createdAt;
                
                // Build actions with proper tooltip
                $editUrl = route('dynamic-notification.save', $row->id);
                $rowArr[] = '<span class="action-btn"><i class="text-dark fs-12 fa-solid fa fa-info" data-toggle="tooltip" title="' . e($title) . '" aria-describedby="tippy-3"></i><a href="' . $editUrl . '"><i class="mdi mdi-lead-pencil" title="Edit"></i></a></span>';
                
                $data[] = $rowArr;
            }

            return response()->json([
                'draw' => $draw,
                'recordsTotal' => $total,
                'recordsFiltered' => $filtered,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            \Log::error('DynamicNotification data error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'draw' => (int) $request->input('draw', 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading notifications'
            ], 500);
        }
    }

    /** Create or update record */
    public function upsert(Request $request)
    {
        $id = $request->input('id');
        $subject = $request->input('subject');
        $message = $request->input('message');
        $type = $request->input('type');

        if (!$subject || !$message) {
            return response()->json(['success'=>false,'message'=>'Subject and message are required'], 422);
        }

        if ($id) {
            DB::table('dynamic_notification')->where('id',$id)->update([
                'subject' => $subject,
                'message' => $message,
                'type'    => $type,
            ]);
            return response()->json(['success'=>true,'message'=>'Notification updated']);
        } else {
            $newId = (string) Str::uuid();
            DB::table('dynamic_notification')->insert([
                'id' => $newId,
                'subject' => $subject,
                'message' => $message,
                'type'    => $type,
                'createdAt' => now()->toIso8601String(),
            ]);
            return response()->json(['success'=>true,'message'=>'Notification created','id'=>$newId]);
        }
    }

    /**
     * Get single notification for editing (API endpoint)
     */
    public function show($id)
    {
        $notification = DB::table('dynamic_notification')
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        return response()->json([
            'success' => true,
            'id' => $notification->id,
            'type' => $notification->type,
            'subject' => $notification->subject,
            'message' => $notification->message,
            'createdAt' => $notification->createdAt
        ]);
    }

    public function delete($id)
    {
        DB::table('dynamic_notification')->where('id',$id)->delete();
        return response()->json(['success'=>true]);
    }

}
