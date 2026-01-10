<?php

namespace App\Http\Controllers;

use App\Mail\DynamicEmail;
use App\Models\admin_users;
use App\Models\AppUser;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PaypalPayoutsSDK\Core\PayPalHttpClient;
use PaypalPayoutsSDK\Core\SandboxEnvironment;
use PaypalPayoutsSDK\Core\ProductionEnvironment;
use PaypalPayoutsSDK\Payouts\PayoutsPostRequest;
use Razorpay\Api\Api;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view("settings.users.index");
    }

    public function edit($id)
    {
        return view('settings.users.edit')->with('id', $id);
    }

    public function adminUsers()
    {
        $users = admin_users::join('role', 'role.id', '=', 'admin_users.role_id')
            ->select('admin_users.*', 'role.role_name as roleName')
            ->where('admin_users.id', '!=', 1)
            ->get();
        return view('admin_users.index', compact(['users']));
    }

    public function createAdminUsers()
    {
        $roles = Role::all();
        return view('admin_users.create', compact(['roles']));
    }
    public function storeAdminUsers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            $errorMessage = $validator->errors()->first(); // Get the first error message
            return redirect()->back()->with(['message' => $errorMessage])->withInput();
        }

        admin_users::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role_id' => $request->input('role'),
        ]);

        // Log activity
        app(\App\Services\ActivityLogger::class)->log(
            auth()->user(),
            'customers',
            'created',
            'Created new admin user: ' . $request->input('name'),
            $request
        );

        return redirect('admin-users');
    }
    public function editAdminUsers($id)
    {
        $user = admin_users::join('role', 'role.id', '=', 'admin_users.role_id')
            ->select('admin_users.*', 'role.role_name as roleName')
            ->where('admin_users.id', $id)
            ->first();
        $roles = Role::all();
        return view('admin_users.edit', compact(['user', 'roles']));
    }
    public function updateAdminUsers(Request $request, $id)
    {
        $name = $request->input('name');
        $password = $request->input('password');
        $old_password = $request->input('old_password');
        $email = $request->input('email');
        $role = ($id == 1) ? 1 : $request->input('role');
        if ($password == '') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'email' => 'required|email'
            ]);
        } else {
            $user = admin_users::find($id);
            if (password_verify($old_password, $user->password)) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:255',
                    'password' => 'required|min:8',
                    'confirm_password' => 'required|same:password',
                    'email' => 'required|email'
                ]);
            } else {
                return Redirect()->back()->with(['message' => "Please enter correct old password"]);
            }
        }

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return Redirect()->back()->with(['message' => $error]);
        }

        $user = admin_users::find($id);

        if ($user) {
            $oldName = $user->name;
            $user->name = $name;
            $user->email = $email;
            if ($password != '') {
                $user->password = Hash::make($password);
            }
            $user->role_id = $role;
            $user->save();

            // Log activity
            app(\App\Services\ActivityLogger::class)->log(
                auth()->user(),
                'customers',
                'updated',
                'Updated admin user: ' . $oldName,
                $request
            );
        }

        return redirect('admin-users');
    }
    public function deleteAdminUsers($id)
    {
        $id = json_decode($id);

        if (is_array($id)) {
            $deletedUsers = [];
            for ($i = 0; $i < count($id); $i++) {
                $users = admin_users::find($id[$i]);
                if ($users) {
                    $deletedUsers[] = $users->name;
                    $users->delete();
                }
            }

            // Log bulk delete activity
            if (!empty($deletedUsers)) {
                app(\App\Services\ActivityLogger::class)->log(
                    auth()->user(),
                    'customers',
                    'bulk_deleted',
                    'Bulk deleted admin users: ' . implode(', ', $deletedUsers),
                    request()
                );
            }
        } else {
            $user = admin_users::find($id);
            if ($user) {
                $userName = $user->name;
                $user->delete();

                // Log single delete activity
                app(\App\Services\ActivityLogger::class)->log(
                    auth()->user(),
                    'customers',
                    'deleted',
                    'Deleted admin user: ' . $userName,
                    request()
                );
            }
        }

        return redirect()->back();
    }


    public function profile()
    {
        $user = Auth::user();
        return view('settings.users.profile', compact(['user']));
    }

    public function update(Request $request, $id)
    {
        $name = $request->input('name');
        $password = $request->input('password');
        $old_password = $request->input('old_password');
        $email = $request->input('email');
        if ($password == '') {
            $validator = Validator::make($request->all(), [
                'name' => 'required|max:255',
                'email' => 'required|email'
            ]);
        } else {
            $user = Auth::user();
            if (password_verify($old_password, $user->password)) {
                $validator = Validator::make($request->all(), [
                    'name' => 'required|max:255',
                    'password' => 'required|min:8',
                    'confirm_password' => 'required|same:password',
                    'email' => 'required|email'
                ]);
            } else {
                return Redirect()->back()->with(['message' => "Please enter correct old password"]);
            }
        }

        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return Redirect()->back()->with(['message' => $error]);
        }

        $user = User::find($id);
        if ($user) {
            $user->name = $name;
            $user->email = $email;
            if ($password != '') {
                $user->password = Hash::make($password);
            }
            $user->save();
        }

        return redirect()->back();
    }

    public function create()
    {
        return view('settings.users.create');
    }

    public function view($id)
    {
        return view('settings.users.view')->with('id', $id);
    }

    public function payToUser(Request $request)
    {
        $response = array();
        $encrypt_data =  $request->data;

        if(!empty($encrypt_data)){

            $data = json_decode(base64_decode($encrypt_data),true);

            if($data['method'] == "paypal"){

                $response = $this->payWithPaypal($data);

            }else if($data['method'] == "stripe"){

                $response = $this->payWithStripe($data);

            }else if($data['method'] == "razorpay"){

                $response = $this->payWithRazorpay($data);

            }else if($data['method'] == "flutterwave"){

                $response = $this->payWithFlutterwave($data);
            }

        }else{
            $response['success'] = false;
            $response['message'] = 'Payout method setup is not done';
        }

        return response()->json($response);
    }

    public function payWithPaypal($data){

        $payout_response = array();

        if(!empty($data['user']['withdrawMethod']['paypal']['email'])){

            $paypal_email = $data['user']['withdrawMethod']['paypal']['email'];

            $isLive = $data['settings']['paypal']['isLive'];
            $clientId = $data['settings']['paypal']['paypalAppId'];
            $clientSecret = $data['settings']['paypal']['paypalSecret'];
            if($isLive){
                $environment = new ProductionEnvironment($clientId, $clientSecret);
            }else{
                $environment = new SandboxEnvironment($clientId, $clientSecret);
            }

            $client = new PayPalHttpClient($environment);
            $request = new PayoutsPostRequest();
            $body = [
                "sender_batch_header" => [
                    "sender_batch_id" => "Payouts_".$data["payoutId"],
                    "email_subject" => "You have a payout!",
                    "email_message" => "You have received a payout! Thanks for using our service!",
                ],
                "items" => [
                    [
                        "recipient_type" => "EMAIL",
                        "receiver" => $paypal_email,
                        "note" => "Your $".$data["amount"]." payout",
                        "sender_item_id" => $data["payoutId"],
                        "amount" => [
                            "currency" => "USD",
                            "value" => $data["amount"],
                        ],
                    ],
                ]
            ];

            $request->body = $body;

            try {

                $response = $client->execute($request);

                if(isset($response->statusCode) && $response->statusCode == "201"){
                    $payout_response['success'] = true;
                    $payout_response['message'] = 'We successfully processed your payout request';
                    $payout_response['result'] = $response->result;
                    $payout_response['status'] = "In Process";
                }else{
                    $payout_response['success'] = false;
                    $payout_response['message'] = 'Something went wrong to process your payout request';
                }

            }catch(\Throwable $e){
                $payout_response['success'] = false;
                $payout_response['message'] = $e->getMessage();
            }

        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'User paypal email address is required';
        }

        return $payout_response;
    }

    public function payWithStripe($data){

        $payout_response = array();

        if(!empty($data['user']['withdrawMethod']['stripe']['accountId'])){

            $accountId = $data['user']['withdrawMethod']['stripe']['accountId'];
            $amount = bcmul($data["amount"], 100);

            $stripeSecret = $data['settings']['stripe']['stripeSecret'];
            $stripe = new \Stripe\StripeClient($stripeSecret);

            try {

                $response = $stripe->transfers->create([
                    'amount' => $amount,
                    'currency' => 'usd',
                    'destination' => $accountId,
                    'transfer_group' => $data["payoutId"],
                ]);

                $response = json_decode($response,true);

                if(isset($response['id']) && isset($response['balance_transaction'])){
                    $payout_response['success'] = true;
                    $payout_response['message'] = 'We successfully processed your payout request';
                    $payout_response['result'] = $response;
                    $payout_response['status'] = "Success";
                }else{
                    $payout_response['success'] = false;
                    $payout_response['message'] = "No such destination: '".$accountId."'";
                }

            }catch(\Throwable $e){
                $payout_response['success'] = false;
                $payout_response['message'] = $e->getMessage();
            }

        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Stripe accountId is required';
        }

        return $payout_response;
    }

    public function payWithRazorpay($data){

        $payout_response = array();

        if(!empty($data['user']['withdrawMethod']['razorpay']['accountId'])){

            $accountId = $data['user']['withdrawMethod']['razorpay']['accountId'];
            $amount = bcmul($data["amount"], 100);

            $api_key = $data['settings']['razorpay']['razorpayKey'];
            $api_secret = $data['settings']['razorpay']['razorpaySecret'];
            $api = new Api($api_key, $api_secret);

            try {

                $response = $api->transfer->create(array('account' => $accountId, 'amount' => $amount, 'currency' => 'INR'));
                $response = json_decode($response,true);

                if(isset($response['status']) && isset($response['id'])){
                    $payout_response['success'] = true;
                    $payout_response['message'] = 'We successfully processed your payout request';
                    $payout_response['result'] = $response;
                    $payout_response['status'] = "In Process";
                }else{
                    $payout_response['success'] = false;
                    $payout_response['message'] = $response['error']['description'];
                }

            }catch(\Throwable $e){
                $payout_response['success'] = false;
                $payout_response['message'] = $e->getMessage();
            }

        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Razorpay accountId is required';
        }

        return $payout_response;
    }

    public function payWithFlutterwave($data){

        $payout_response = array();

        if(!empty($data['user']['withdrawMethod']['flutterwave'])){

            $bankCode = $data['user']['withdrawMethod']['flutterwave']['bankCode'];
            $accountNumber = $data['user']['withdrawMethod']['flutterwave']['accountNumber'];
            $amount = bcmul($data["amount"],10);
            $secretKey = $data['settings']['flutterwave']['secretKey'];

            $fields = [
                "account_bank" => $bankCode,
                "account_number" => $accountNumber,
                "amount" => $amount,
                "narration" => "Payment Request: ".$data["payoutId"]."",
                "currency" => "NGN",
                "reference" => $data["payoutId"],
            ];

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,"https://api.flutterwave.com/v3/transfers");
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "Authorization: Bearer ".$secretKey,
                "Cache-Control: no-cache",
                "Content-Type: application/json",
            ));
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $response = json_decode($result,true);

            if($response['status'] == "success"){
                $payout_response['success'] = true;
                $payout_response['message'] = 'We successfully processed your payout request';
                $payout_response['result'] = $response;
                $payout_response['status'] = "In Process";
            }else{
                $payout_response['success'] = false;
                $payout_response['message'] = $response['message'];
            }

        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Flutterwave account detail is required';
        }

        return $payout_response;
    }

    public function checkPayoutStatus(Request $request){

        $response = array();
        $encrypt_data =  $request->data;

        if(!empty($encrypt_data)){

            $data = json_decode(base64_decode($encrypt_data),true);

            if($data['method'] == "paypal"){

                $response = $this->checkStatusPaypal($data);

            }else if($data['method'] == "razorpay"){

                $response = $this->checkStatusRazorpay($data);

            }else if($data['method'] == "flutterwave"){

                $response = $this->checkStatusFlutterwave($data);
            }

        }else{
            $response['success'] = false;
            $response['message'] = 'Something went wrong to check status';
        }

        return response()->json($response);
    }

    public function checkStatusPaypal($data){

        $payout_response = array();

        if(isset($data['payoutDetail']['payoutResponse']) && !empty($data['payoutDetail']['payoutResponse'])){

            $payout_batch_id = $data['payoutDetail']['payoutResponse']['batch_header']['payout_batch_id'];

            if(!empty($payout_batch_id)){

                $isLive = $data['settings']['paypal']['isLive'];
                $clientId = $data['settings']['paypal']['paypalAppId'];
                $clientSecret = $data['settings']['paypal']['paypalSecret'];
                if($isLive){
                    $api_url = "https://api-m.paypal.com";
                }else{
                    $api_url = "https://api-m.sandbox.paypal.com";
                }

                //Get access token
                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,$api_url."/v1/oauth2/token");
                curl_setopt($ch,CURLOPT_POST, true);
                curl_setopt($ch,CURLOPT_POSTFIELDS,"grant_type=client_credentials");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Basic ".base64_encode($clientId.":".$clientSecret),
                    "Content-Type: application/x-www-form-urlencoded"
                ));
                curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                $response = json_decode($result,true);

                //Get status
                if($response['access_token']){

                    $ch = curl_init();
                    curl_setopt($ch,CURLOPT_URL,$api_url."/v1/payments/payouts/".$payout_batch_id);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        "Authorization: Bearer ".$response['access_token'],
                        "Cache-Control: no-cache",
                    ));
                    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
                    $result2 = curl_exec($ch);
                    $response2 = json_decode($result2,true);

                    if(isset($response2['items']) && isset($response2['items'][0]['transaction_status'])){
                        if($response2['items'][0]['transaction_status'] == "SUCCESS"){
                            $payout_response['success'] = true;
                            $payout_response['message'] = "We successfully processed your transaction";
                            $payout_response['result'] = $response2;
                            $payout_response['status'] = "Success";
                        }else{
                            $payout_response['success'] = false;
                            $payout_response['message'] = $response2['items'][0]['errors']['name']." : ".$response2['items'][0]['errors']['message'];
                            $payout_response['result'] = $response2;
                            $payout_response['status'] = "Failed";
                        }
                    }else{
                        $payout_response['success'] = false;
                        $payout_response['message'] = 'Invalid payout transaction';
                    }
                }else{
                    $payout_response['success'] = false;
                    $payout_response['message'] = 'Invalid client credentials';
                }

            }else{
                $payout_response['success'] = false;
                $payout_response['message'] = 'Invalid payout_batch_id';
            }

        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Invalid payout response';
        }

        return $payout_response;
    }

    public function checkStatusRazorpay($data){

        $payout_response = array();

        if(isset($data['payoutDetail']['payoutResponse']) && !empty($data['payoutDetail']['payoutResponse'])){

            $transfer_id = $data['payoutDetail']['payoutResponse']['id'];

            if(!empty($transfer_id)){

                $api_key = $data['settings']['razorpay']['razorpayKey'];
                $api_secret = $data['settings']['razorpay']['razorpaySecret'];
                $api = new Api($api_key, $api_secret);

                try {

                    $response = $api->transfer->fetch($transfer_id);
                    $response = json_decode($response,true);

                    if(isset($response['settlement_status']) && $response['settlement_status'] == "settled"){
                        $payout_response['success'] = true;
                        $payout_response['message'] = 'We successfully processed your transaction';
                        $payout_response['result'] = $response;
                        $payout_response['status'] = "Success";
                    }else{
                        $payout_response['success'] = false;
                        $payout_response['message'] = $response['error']['description'];
                        $payout_response['status'] = "Failed";
                    }

                }catch(\Throwable $e){
                    $payout_response['success'] = false;
                    $payout_response['message'] = $e->getMessage();
                }

            }else{
                $payout_response['success'] = false;
                $payout_response['message'] = 'Invalid transfer id';
            }

        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Invalid payout response';
        }

        return $payout_response;
    }

    public function checkStatusFlutterwave($data){

        $payout_response = array();

        if(isset($data['payoutDetail']['payoutResponse']) && !empty($data['payoutDetail']['payoutResponse'])){

            $transfer_id = $data['payoutDetail']['payoutResponse']['data']['id'];

            if(!empty($transfer_id)){

                $secretKey = $data['settings']['flutterwave']['secretKey'];

                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,"https://api.flutterwave.com/v3/transfers/".$transfer_id);
                curl_setopt($ch,CURLOPT_CUSTOMREQUEST, "GET");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Authorization: Bearer ".$secretKey,
                    "Cache-Control: no-cache",
                    "Content-Type: application/json",
                ));
                curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
                $result = curl_exec($ch);
                $response = json_decode($result,true);

                if($response['status'] == "success"){
                    $payout_response['success'] = true;
                    $payout_response['message'] = 'We successfully processed your transaction';
                    $payout_response['result'] = $response;
                    $payout_response['status'] = "Success";
                }else{
                    $payout_response['success'] = false;
                    $payout_response['message'] = $response['message'];
                }

            }else{
                $payout_response['success'] = false;
                $payout_response['message'] = 'Invalid transfer id';
            }

        }else{
            $payout_response['success'] = false;
            $payout_response['message'] = 'Invalid payout response';
        }

        return $payout_response;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $spreadsheet = IOFactory::load($request->file('file'));
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);

        if (empty($rows) || count($rows) < 2) {
            return back()->withErrors(['file' => 'The uploaded file is empty or missing data.']);
        }

        $headers = array_map('trim', array_shift($rows));
        $imported = 0;
        foreach ($rows as $row) {
            $data = array_combine($headers, $row);
            if (empty($data['firstName']) || empty($data['lastName']) || empty($data['email']) || empty($data['password'])) {
                continue; // Skip incomplete rows
            }
            $userData = [
                'firstName' => $data['firstName'],
                'lastName' => $data['lastName'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'zoneId' => $data['zone'] ?? null,
//                'active' => $data['active'] ?? null,
                'active' => isset($data['active'])
                    ? (int) filter_var($data['active'], FILTER_VALIDATE_BOOLEAN)
                    : 0,
                'role' => $data['role'] ?? 'customer',
                'profilePictureURL' => $data['profilePictureURL'] ?? null,
                'migratedBy' => 'migrate:users',
            ];

            // Generate a random unique Firebase ID if missing
            $userData['firebase_id'] = $data['firebase_id'] ?? Str::uuid()->toString();

            // createdAt column is text in provided schema; store as string (Y-m-d H:i:s)
            $userData['createdAt'] = !empty($data['createdAt'])
                ? (string) Carbon::parse($data['createdAt'])->format('Y-m-d H:i:s')
                : (string) now()->format('Y-m-d H:i:s');

            // Upsert by email if present, otherwise insert
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
            $imported++;
        }
        if ($imported === 0) {
            return back()->withErrors(['file' => 'No valid rows were found to import.']);
        }
        return back()->with('success', "Users imported successfully! ($imported rows)");
    }

    public function downloadTemplate()
    {
        $filePath = storage_path('app/templates/users_import_template.xlsx');
        $templateDir = dirname($filePath);

        // Create template directory if it doesn't exist
        if (!is_dir($templateDir)) {
            mkdir($templateDir, 0755, true);
        }

        // Generate template if it doesn't exist
        if (!file_exists($filePath)) {
            $this->generateUsersTemplate($filePath);
        }

        return response()->download($filePath, 'users_import_template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="users_import_template.xlsx"'
        ]);
    }

    /**
     * Generate Excel template for users import
     */
    private function generateUsersTemplate($filePath)
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'A1' => 'firstName',
                'B1' => 'lastName',
                'C1' => 'email',
                'D1' => 'password',
                'E1' => 'zone',
                'F1' => 'active (1=active, 0=inactive)',
                'G1' => 'role',
                'H1' => 'profilePictureURL',
                'I1' => 'createdAt'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
                $sheet->getStyle($cell)->getFont()->setBold(true);
            }

            // Add sample data
            $sampleData = [
                'John',
                'Doe',
                'john.doe@example.com',
                'password123',
                'zoneId123',
                1,
                'customer',
                'https://example.com/profile.jpg',
                date('Y-m-d H:i:s')
            ];
//
//            $sheet->fromArray([$sampleData], null, 'A2');
//
//
//            // Force F2 (active) to be numeric/text, not boolean
//            $sheet->setCellValueExplicit('F2', '1', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
// Add sample data manually to control types
            $row = 2;
            $sheet->setCellValueExplicit("A{$row}", 'John', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("B{$row}", 'Doe', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$row}", 'john.doe@example.com', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$row}", 'password123', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E{$row}", 'zoneId123', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("F{$row}", '1', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC); // ✅ force as number
            $sheet->setCellValueExplicit("G{$row}", 'customer', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("H{$row}", 'https://example.com/profile.jpg', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("I{$row}", date('Y-m-d H:i:s'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // Auto-size columns
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Apply formatting for date column
            $sheet->getStyle('I2')->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');

            // Save the file
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save($filePath);

        } catch (\Exception $e) {
            \Log::error('Failed to generate users template: ' . $e->getMessage());
            abort(500, 'Failed to generate template');
        }
    }

    /**
     * Get user data by ID for view page (SQL API)
     */
    public function getUserData($id)
    {
        try {
            $user = User::where('firebase_id', $id)
                ->orWhere('id', $id)
                ->orWhere('_id', $id)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Get total orders count
            $totalOrders = \DB::table('restaurant_orders')
                ->where('authorID', $id)
                ->count();

            // Parse shipping address if it's JSON
            $shippingAddress = null;
            if ($user->shippingAddress) {
                $shippingAddress = json_decode($user->shippingAddress, true);
            }

            // Extract zoneId from shippingAddress
            $zoneId = self::extractZoneFromShippingAddress($user->shippingAddress);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->firebase_id ?? $user->_id ?? $user->id,
                    'firstName' => $user->firstName,
                    'lastName' => $user->lastName,
                    'email' => $user->email,
                    'phoneNumber' => $user->phoneNumber,
                    'countryCode' => $user->countryCode,
                    'wallet_amount' => $user->wallet_amount ?? 0,
                    'profilePictureURL' => $user->profilePictureURL,
                    'shippingAddress' => $shippingAddress,
                    'zoneId' => $zoneId,
                    'isActive' => $user->isActive,
                    'createdAt' => $user->createdAt,
                    'totalOrders' => $totalOrders,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching user data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching user data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add wallet amount to user (SQL API)
     */
    public function addWalletAmount(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'amount' => 'required|numeric',
                'note' => 'nullable|string'
            ]);

            // ✅ FIND DRIVER (ONLY BY id)
            $driver = user::where('firebase_id', $id)->first();

            if (!$driver) {
                return response()->json([
                    'success' => false,
                    'message' => 'Driver not found'
                ], 404);
            }

            $amount = (float) $request->amount;

            // ✅ UPDATE DRIVER WALLET
            $currentWallet = (float) ($driver->wallet_amount ?? 0);
            $newWallet = $currentWallet + $amount;

            $driver->wallet_amount = $newWallet;
            $driver->save();

            // ✅ INSERT INTO WALLET TABLE
            DB::table('wallet')->insert([
                'id' => Str::uuid()->toString(),
                'user_id' => $driver->firebase_id,          // 🔥 CORRECT
                'amount' => $amount,
                'isTopUp' => $amount >= 0 ? 1 : 0,
                'payment_method' => 'Wallet',
                'payment_status' => 'success',
                'note' => $request->note ?? '',
                'transactionUser' => 'driver',
                'order_id' => null,
                'subscription_id' => null,
                'date' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wallet amount added successfully',
                'newWalletAmount' => $newWallet
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Wallet Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Extract zoneId from shippingAddress JSON
     * @param string|null $shippingAddress JSON string
     * @return string zoneId or empty string
     */
    public static function extractZoneFromShippingAddress($shippingAddress)
    {
        $zoneId = '';
        if ($shippingAddress) {
            try {
                $addresses = json_decode($shippingAddress, true);
                if (is_array($addresses)) {
                    // Find default address first
                    $defaultAddress = collect($addresses)->firstWhere('isDefault', 1);
                    if ($defaultAddress && isset($defaultAddress['zoneId'])) {
                        $zoneId = $defaultAddress['zoneId'];
                    } else {
                        // If no default, get zoneId from first address
                        $firstAddress = reset($addresses);
                        if ($firstAddress && isset($firstAddress['zoneId'])) {
                            $zoneId = $firstAddress['zoneId'];
                        }
                    }
                }
            } catch (\Exception $e) {
                // If JSON parsing fails, leave zoneId empty
            }
        }
        return $zoneId;
    }

    /**
     * Get a single user by ID (for edit page)
     */
    public function showUser(Request $request, $id)
    {
        $isActive = $request->input('isActive');

        $user = User::where('firebase_id', $id)
            ->orWhere('id', $id)
            ->orWhere('_id', $id)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }
        $query = AppUser::query();

        if ($isActive !== null && $isActive !== '') {
            $query->where('active', $isActive == '1' ? '1' : '0');
        }

        // Get total orders count
        $totalOrders = \DB::table('restaurant_orders')
            ->where('authorID', $id)
            ->count();

        $fullName = trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? ''));

        // Extract zoneId from shippingAddress
        $zoneId = self::extractZoneFromShippingAddress($user->shippingAddress);

        $totalDrivers   = $query->count();
        $activeDrivers  = $query->clone()->where('active', 1)->count();
        $inactiveDrivers = $query->clone()->where('active', 0)->count();


        return response()->json([
            'status' => true,
            'data' => [
                'id' => (string) ($user->firebase_id ?: $user->_id ?: $user->id),
                'firebase_id' => (string) ($user->firebase_id ?: $user->_id ?: $user->id),
                '_id' => (string) ($user->_id ?: $user->firebase_id ?: $user->id),
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'fullName' => $fullName,
                'email' => $user->email,
                'phoneNumber' => $user->phoneNumber,
                'countryCode' => $user->countryCode,
                'wallet_amount' => $user->wallet_amount ?? 0,
                'profilePictureURL' => $user->profilePictureURL,
                'shippingAddress' => $user->shippingAddress ? json_decode($user->shippingAddress, true) : null,
                'zoneId' => $zoneId,
//                'active' => in_array((string) $user->active, ['1','true'], true) || (bool) ($user->isActive ?? 0),
//                'isActive' => in_array((string) $user->active, ['1','true'], true) || (bool) ($user->isActive ?? 0),
                'active' => ($user->active == 1 || $user->isActive == 1) ? 1 : 0,
                'createdAt' => $user->createdAt,
                'totalOrders' => $totalOrders,
                'provider' => $user->provider ?? 'email',
                'role' => $user->role ?? 'customer',
            ],
            'stats' => [
                'total' => $totalDrivers,
                'active' => $activeDrivers,
                'inactive' => $inactiveDrivers
            ]
        ]);
    }

    /**
     * Update user (for edit page)
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::where('firebase_id', $id)
            ->orWhere('id', $id)
            ->orWhere('_id', $id)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        }

        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'countryCode' => 'nullable|string|max:10',
            'phoneNumber' => 'nullable|string|max:30',
            'active' => 'nullable',
            'photo' => 'nullable|string', // base64 data URL (optional)
            'fileName' => 'nullable|string',
        ]);

        // Handle profile picture upload
        if (!empty($validated['photo'])) {
            // Delete old image if exists
            if ($user->profilePictureURL && strpos($user->profilePictureURL, 'storage/users/') !== false) {
                $oldPath = str_replace(asset('storage/'), '', $user->profilePictureURL);
                Storage::disk('public')->delete($oldPath);
            }

            $data = $validated['photo'];
            $data = preg_replace('#^data:image/\w+;base64,#i', '', $data);
            $binary = base64_decode($data, true);
            if ($binary !== false) {
                $name = $validated['fileName'] ?? ('user_' . time() . '.jpg');
                $path = 'users/' . $name;
                Storage::disk('public')->put($path, $binary);
                $user->profilePictureURL = asset('storage/' . $path);
            }
        }

        // Determine active status
        $isActive = false;
        if (isset($validated['active'])) {
            if (is_bool($validated['active'])) {
                $isActive = $validated['active'];
            } else {
                $isActive = ($validated['active'] === 'true' || $validated['active'] === true || $validated['active'] === 1);
            }
        }

        // Update user fields
        $user->firstName = $validated['firstName'];
        $user->lastName = $validated['lastName'];
        $user->countryCode = $validated['countryCode'] ?? $user->countryCode;
        $user->phoneNumber = $validated['phoneNumber'] ?? $user->phoneNumber;
        $user->active = $isActive ? 1 : 0;
        $user->isActive = $isActive ? 1 : 0;
        $user->save();

        // Log activity
        app(\App\Services\ActivityLogger::class)->log(
            auth()->user(),
            'users',
            'updated',
            'Updated user: ' . $user->firstName . ' ' . $user->lastName,
            $request
        );

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully',
            'data' => [
                'id' => (string) ($user->firebase_id ?: $user->id),
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
            ]
        ]);
    }
}
