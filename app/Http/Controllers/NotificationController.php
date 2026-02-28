<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\VendorUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Google\Client as Google_Client;

class NotificationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id='')
    {
        return view("notification.index")->with('id',$id);
    }

    public function send($id='')
    {
        return view('notification.send')->with('id',$id);
    }

    /**
     * Server-side data for DataTables (SQL based)
     */
    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = strtolower((string) data_get($request->input('search'), 'value', ''));

        $base = DB::table('notifications');
        $total = $base->count();

        $q = DB::table('notifications')
            ->select('id', 'subject', 'message', 'role', 'createdAt');

        if ($search !== '') {
            $q->where(function($qq) use ($search){
                $qq->where('subject','like','%'.$search.'%')
                   ->orWhere('message','like','%'.$search.'%')
                   ->orWhere('role','like','%'.$search.'%');
            });
        }

        // Order by createdAt (not created_at)
        $q->orderBy('createdAt', 'desc');
        $rows = $q->offset($start)->limit($length)->get();
        $filtered = ($search==='') ? $total : (clone $q)->count();

        $canDelete = in_array('notification.delete', json_decode(@session('user_permissions'), true) ?: []);
        $data = [];
        foreach ($rows as $row) {
            $rowArr = [];
            if ($canDelete) {
                $rowArr[] = '<td class="delete-all"><input type="checkbox" class="is_open" dataId="'.$row->id.'"><label class="col-3 control-label"></label></td>';
            }
            $rowArr[] = e($row->subject ?? '');
            $rowArr[] = e($row->message ?? '');

            // Format createdAt - it's stored as varchar in ISO format
            $createdAt = '-';
            if ($row->createdAt) {
                try {
                    // Format as: Oct 06, 2025 07:24 AM
                    $createdAt = Carbon::parse($row->createdAt)->format('M d, Y h:i A');
                } catch (\Exception $e) {
                    $createdAt = $row->createdAt; // Show as-is if can't parse
                }
            }
            $rowArr[] = $createdAt;

            if ($canDelete) {
                $rowArr[] = '<span class="action-btn"><a id="'.$row->id.'" name="notifications-delete" class="delete-btn" href="javascript:void(0)"><i class="mdi mdi-delete"></i></a></span>';
            }
            $data[] = $rowArr;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data,
        ]);
    }

    public function broadcastnotification(Request $request)
    {
        // Log the request for debugging
        \Log::info('Broadcast notification request', [
            'role' => $request->role,
            'subject' => $request->subject,
            'message' => $request->message
        ]);

        $role = $request->role;
        $firebaseEnabled = false;
        $pushSent = false;

        // Try to send push notification via Firebase if credentials exist
        // Path: storage/app/firebase/credentials.json
        if(Storage::disk('local')->has('firebase/credentials.json')){
            $firebaseEnabled = true;

            try {
                $client = new Google_Client();
                $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                $client->setAccessType('offline');
                $client->refreshTokenWithAssertion();
                $client_token = $client->getAccessToken();

                if (!$client_token || !isset($client_token['access_token'])) {
                    \Log::error('Failed to get access token from Google Client');
                } else {
                    $access_token = $client_token['access_token'];
                    \Log::info('Successfully obtained Firebase access token');

                    if(!empty($access_token) && !empty($role)){

                        $projectId = env('FIREBASE_PROJECT_ID', 'jippymart-27c08');
                        $url = 'https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send';

                        // Map roles to topics
                        $topicMap = [
                            'vendor' => 'restaurant',
                            'customer' => 'customer',
                            'driver' => 'driver'
                        ];

                        $topic = $topicMap[$role] ?? 'default';

                        \Log::info('Sending notification', [
                            'project_id' => $projectId,
                            'role' => $role,
                            'topic' => $topic,
                            'url' => $url
                        ]);

                        $data = [
                            'message' => [
                                'notification' => [
                                    'title' => $request->subject,
                                    'body' => $request->message,
                                ],
                                'topic' => $topic,
                                'android' => [
                                    'notification' => [
                                        'sound' => 'default'
                                    ],
                                    'priority' => 'high'
                                ],
                                'apns' => [
                                    'payload' => [
                                        'aps' => [
                                            'sound' => 'default',
                                            'badge' => 1
                                        ]
                                    ]
                                ]
                            ],
                        ];

                        $headers = array(
                            'Content-Type: application/json',
                            'Authorization: Bearer '.$access_token
                        );

                        \Log::info('FCM Request Data', ['data' => $data]);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                        $result = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $curlError = curl_error($ch);
                        curl_close($ch);

                        \Log::info('FCM Response', [
                            'http_code' => $httpCode,
                            'result' => $result,
                            'curl_error' => $curlError
                        ]);

                        if ($result !== FALSE && empty($curlError)) {
                            $resultData = json_decode($result, true);
                            if ($httpCode >= 200 && $httpCode < 300) {
                                $pushSent = true;
                                \Log::info('Push notification sent successfully', ['result' => $resultData]);
                            } else {
                                \Log::error('FCM API Error', [
                                    'http_code' => $httpCode,
                                    'response' => $resultData
                                ]);
                            }
                        } else {
                            \Log::error('FCM Send Error: ' . $curlError);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Google Client authentication error: ' . $e->getMessage());
            }
        } else {
            \Log::info('Firebase credentials.json not found - skipping push notification, saving to database only');
        }

        // Always save notification to database regardless of Firebase status
        try {
            $notificationId = (string) Str::uuid();
            DB::table('notifications')->insert([
                'id' => $notificationId,
                'subject' => $request->subject,
                'message' => $request->message,
                'role' => $request->role,
                'createdAt' => now()->toIso8601String(), // Use createdAt (camelCase) to match table
            ]);

            // Return success message
            if ($pushSent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification sent and saved to database'
                ]);
            } else if ($firebaseEnabled) {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification saved to database. Push notification failed or Firebase is not configured properly.'
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'Notification saved to database. (Firebase push notifications are disabled)'
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('❌ Failed to persist notification in SQL: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save notification: ' . $e->getMessage()
            ]);
        }
    }

    public function sendNotification(Request $request)
    {
        \Log::info('Individual notification request', [
            'fcm_token' => $request->fcm,
            'title' => $request->title,
            'message' => $request->message
        ]);

        $fcm_token = $request->fcm;
        $pushSent = false;

        // Try to send push notification via Firebase if credentials exist
        // Path: storage/app/firebase/credentials.json
        if(Storage::disk('local')->has('firebase/credentials.json')){

            try {
                $client = new Google_Client();
                $client->setAuthConfig(storage_path('app/firebase/credentials.json'));
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                $client->setAccessType('offline');
                $client->refreshTokenWithAssertion();
                $client_token = $client->getAccessToken();

                if (!$client_token || !isset($client_token['access_token'])) {
                    \Log::error('Failed to get access token from Google Client');
                } else {
                    $access_token = $client_token['access_token'];
                    \Log::info('Successfully obtained Firebase access token for individual notification');

                    if(!empty($access_token) && !empty($fcm_token)){

                        $projectId = env('FIREBASE_PROJECT_ID', 'jippymart-27c08');
                        $url = 'https://fcm.googleapis.com/v1/projects/'.$projectId.'/messages:send';

                        $data = [
                            'message' => [
                                'notification' => [
                                    'title' => $request->title,
                                    'body' => $request->message,
                                ],
                                'token' => $fcm_token,
                                'android' => [
                                    'notification' => [
                                        'sound' => 'default'
                                    ],
                                    'priority' => 'high'
                                ],
                                'apns' => [
                                    'payload' => [
                                        'aps' => [
                                            'sound' => 'default',
                                            'badge' => 1
                                        ]
                                    ]
                                ]
                            ],
                        ];

                        $headers = array(
                            'Content-Type: application/json',
                            'Authorization: Bearer '.$access_token
                        );

                        \Log::info('FCM Individual Request Data', ['data' => $data]);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

                        $result = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $curlError = curl_error($ch);
                        curl_close($ch);

                        \Log::info('FCM Individual Response', [
                            'http_code' => $httpCode,
                            'result' => $result,
                            'curl_error' => $curlError
                        ]);

                        if ($result !== FALSE && empty($curlError)) {
                            $resultData = json_decode($result, true);
                            if ($httpCode >= 200 && $httpCode < 300) {
                                $pushSent = true;
                                \Log::info('Individual notification sent successfully', ['result' => $resultData]);
                                return response()->json([
                                    'success' => true,
                                    'message' => 'Notification successfully sent to device',
                                    'result' => $resultData
                                ]);
                            } else {
                                \Log::error('FCM API Error', [
                                    'http_code' => $httpCode,
                                    'response' => $resultData
                                ]);
                                return response()->json([
                                    'success' => false,
                                    'message' => 'FCM API Error: ' . ($resultData['error']['message'] ?? 'Unknown error'),
                                    'result' => $resultData
                                ]);
                            }
                        } else {
                            \Log::error('FCM Send Error: ' . $curlError);
                            return response()->json([
                                'success' => false,
                                'message' => 'FCM Send Error: ' . $curlError
                            ]);
                        }
                    } else {
                        \Log::error('Missing access token or FCM token for individual notification');
                        return response()->json([
                            'success' => false,
                            'message' => 'Missing access token or FCM token to send notification.'
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Google Client authentication error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication error: ' . $e->getMessage()
                ]);
            }
        }

        // If Firebase is not available, return appropriate message
        \Log::info('Firebase credentials.json not found - push notification unavailable');
        return response()->json([
            'success' => false,
            'message' => 'Firebase push notifications are not configured. Please configure Firebase credentials to send push notifications.'
        ]);
    }

    public function destroy($id){

        $notification = DB::table('notifications')->where('id', $id)->first();

        DB::table('notifications')->where('id', $id)->delete();

        return response()->json([$notification,
          'message' => "Notification successfully destroyed."]);

    }

}
