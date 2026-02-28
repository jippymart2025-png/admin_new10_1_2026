<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\CustomerWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    /**
     * Generate a unique Firebase ID in format: user_XXX
     */
    private function generateFirebaseId()
    {
        // Get the highest existing user number
        $lastUser = AppUser::where('firebase_id', 'like', 'user_%')
            ->orderByRaw('CAST(SUBSTRING(firebase_id, 6) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;

        if ($lastUser && $lastUser->firebase_id) {
            // Extract number from firebase_id (e.g., "user_999" -> 999)
            preg_match('/user_(\d+)/', $lastUser->firebase_id, $matches);
            if (!empty($matches[1])) {
                $nextNumber = (int)$matches[1] + 1;
            }
        }

        // Generate new ID
        $firebaseId = 'user_' . $nextNumber;

        // Ensure uniqueness (in case of concurrent requests)
        while (AppUser::where('firebase_id', $firebaseId)->exists()) {
            $nextNumber++;
            $firebaseId = 'user_' . $nextNumber;
        }

        return $firebaseId;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'countryCode' => 'nullable|string|max:10',
            'phoneNumber' => 'nullable|string|max:30',
            'active' => 'nullable',
            'role' => 'nullable|string|max:50',
            'zoneId' => 'nullable|string|max:255',
            'photo' => 'nullable|string', // base64 data URL (optional)
            'fileName' => 'nullable|string',
        ]);

        $profileUrl = null;
        if (!empty($validated['photo'])) {
            $data = $validated['photo'];
            $data = preg_replace('#^data:image/\w+;base64,#i', '', $data);
            $binary = base64_decode($data, true);
            if ($binary !== false) {
                $name = $validated['fileName'] ?? ('user_' . time() . '.jpg');
                $path = 'users/' . $name;
                Storage::disk('public')->put($path, $binary);
                $profileUrl = asset('storage/' . $path);
            }
        }

        // Generate unique firebase_id
        $firebase_id = $this->generateFirebaseId();

        // Determine active status
        $isActive = false;
        if (isset($validated['active'])) {
            if (is_bool($validated['active'])) {
                $isActive = $validated['active'];
            } else {
                $isActive = ($validated['active'] === 'true' || $validated['active'] === true || $validated['active'] === 1);
            }
        }

        // Create new user
        // Set createdAt in Asia/Kolkata timezone
        // Store in ISO format (e.g., "2025-12-19T09:50:00.000000+05:30")
        // Format: YYYY-MM-DDTHH:mm:ss.microseconds+timezone
        $createdAt = Carbon::now('Asia/Kolkata')->format('Y-m-d\TH:i:s.uP');
        // Store as JSON string format (with quotes) for consistency with system
        $createdAt = '"' . $createdAt . '"';

        $user = AppUser::create([
            'firebase_id' => $firebase_id,
            '_id' => $firebase_id, // Also set _id for compatibility
            'firstName' => $validated['firstName'],
            'lastName' => $validated['lastName'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'countryCode' => $validated['countryCode'] ?? null,
            'phoneNumber' => $validated['phoneNumber'] ?? null,
            'profilePictureURL' => $profileUrl,
            'provider' => 'email',
            'role' => $validated['role'] ?? 'customer',
            'active' => $isActive ? 1 : 0,
            'isActive' => $isActive ? 1 : 0,
            'zoneId' => $validated['zoneId'] ?? null,
            'appIdentifier' => 'web',
            'createdAt' => $createdAt,
            'wallet_amount' => 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => [
                'id' => (string) $user->firebase_id,
                'firebase_id' => (string) $user->firebase_id,
                'email' => $user->email,
                'isActive' => $user->isActive
            ]
        ], 201);
    }

    /**
     * Get users list with pagination and filters
     *
     * Performance optimizations:
     * - Uses range queries instead of whereDate for better index usage
     * - Uses JSON_EXTRACT for faster JSON column searches
     * - Only selects needed columns
     * - Removed inefficient LIKE search on createdAt
     *
     * Recommended database indexes:
     * - CREATE INDEX idx_users_role_createdAt ON users(role, createdAt);
     * - CREATE INDEX idx_users_active ON users(active);
     * - CREATE INDEX idx_users_createdAt ON users(createdAt);
     * - CREATE INDEX idx_users_email ON users(email);
     */
    public function index(Request $request)
    {
        $limit = (int) $request->query('limit', 10);
        $page = (int) $request->query('page', 1);
        $active = $request->query('active');
        $zoneId = $request->query('zoneId');
        $search = trim((string) $request->query('search', ''));

        $query = AppUser::with([
            'customerWallet:user_id,coin_balance',
            'latestDailyCheckin:user_id,streak_day_number',
            'referralUsed',
            'referredBy'
        ]);

        // Only customers by default unless role is specified
        $role = $request->query('role', 'customer');
        if (!empty($role)) {
            $query->where('role', $role);
        }

        // Date range filter (supports presets: last_24_hours, last_week, last_month, custom, all_orders)
        $dateRange = $request->query('date_range');
        $from = $request->query('from');
        $to = $request->query('to');

        // Note: createdAt is stored as JSON string format (e.g., "2025-12-19T09:50:00.000000+05:30")
        if ($dateRange === 'last_24_hours') {
            $startDateTime = Carbon::now('Asia/Kolkata')->subDay()->format('Y-m-d H:i:s');
            $endDateTime = Carbon::now('Asia/Kolkata')->format('Y-m-d H:i:s');
            $query->whereRaw("
    STR_TO_DATE(
        REPLACE(SUBSTRING(createdAt, 1, 19), 'T', ' '),
        '%Y-%m-%d %H:%i:%s'
    ) BETWEEN ? AND ?
", [$startDateTime, $endDateTime]);
        } elseif ($dateRange === 'last_week') {
            $startDate = Carbon::now('Asia/Kolkata')->subWeek()->startOfDay()->format('Y-m-d');
            $endDate = Carbon::now('Asia/Kolkata')->endOfDay()->format('Y-m-d');
            // Extract date part (first 10 chars: YYYY-MM-DD) for comparison
            $query->whereRaw("SUBSTRING(REPLACE(createdAt, '\"', ''), 1, 10) BETWEEN ? AND ?",
                [$startDate, $endDate]);
        } elseif ($dateRange === 'last_month') {
            $startDate = Carbon::now('Asia/Kolkata')->subMonth()->startOfDay()->format('Y-m-d');
            $endDate = Carbon::now('Asia/Kolkata')->endOfDay()->format('Y-m-d');
            $query->whereRaw("SUBSTRING(REPLACE(createdAt, '\"', ''), 1, 10) BETWEEN ? AND ?",
                [$startDate, $endDate]);
        } elseif ($dateRange === 'all_orders') {
            // Show all users - skip date filtering entirely
            // Do nothing - no date filter applied
        } elseif (!empty($from) || !empty($to)) {
            // Custom range or direct from/to parameters
            if (!empty($from)) {
                $fromDate = Carbon::parse($from)->startOfDay()->format('Y-m-d');
                $query->whereRaw("SUBSTRING(REPLACE(createdAt, '\"', ''), 1, 10) >= ?", [$fromDate]);
            }
            if (!empty($to)) {
                $toDate = Carbon::parse($to)->endOfDay()->format('Y-m-d');
                $query->whereRaw("SUBSTRING(REPLACE(createdAt, '\"', ''), 1, 10) <= ?", [$toDate]);
            }
        } else {
            // No date range selected - default to today only (Asia/Kolkata timezone)
            // Convert IST date range to UTC for database comparison
            $todayStartIST = Carbon::today('Asia/Kolkata'); // Feb 09, 2026 00:00:00 IST
            $todayEndIST = Carbon::today('Asia/Kolkata')->endOfDay(); // Feb 09, 2026 23:59:59 IST

            // Convert to UTC (subtract 5:30)
            $todayStartUTC = $todayStartIST->copy()->setTimezone('UTC')->format('Y-m-d H:i:s');
            $todayEndUTC = $todayEndIST->copy()->setTimezone('UTC')->format('Y-m-d H:i:s');

            // Compare with datetime in database
            $query->whereRaw("
                STR_TO_DATE(
                    REPLACE(SUBSTRING(createdAt, 1, 19), 'T', ' '),
                    '%Y-%m-%d %H:%i:%s'
                ) BETWEEN ? AND ?
            ", [$todayStartUTC, $todayEndUTC]);
        }

        if ($active !== null && $active !== '') {
            $query->where('active', (int) $active);
        }

        // Zone filter - search in shippingAddress JSON column
        if (!empty($zoneId)) {
            $query->where(function($q) use ($zoneId) {
                // Use JSON_EXTRACT for better performance (MySQL 5.7+)
                // This searches in the JSON array for zoneId
                $q->whereRaw('JSON_SEARCH(shippingAddress, "one", ?, NULL, "$[*].zoneId") IS NOT NULL', [$zoneId])
                    ->orWhereRaw('JSON_EXTRACT(shippingAddress, "$[0].zoneId") = ?', [$zoneId])
                    ->orWhere('shippingAddress', 'like', "%\"zoneId\":\"$zoneId\"%"); // Fallback for older MySQL
            });
        }

        // Search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('firstName', 'like', "%$search%")
                    ->orWhere('lastName', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phoneNumber', 'like', "%$search%");
            });
        }

        // Optimize count query - use selectRaw for faster counting
        $total = (clone $query)->count();

        // Only fetch needed columns
        // Order by indexed column (id) for better performance
        $rows = $query->select(
            'id',
            'firebase_id',
            'firstName',
            'lastName',
            'email',
            'phoneNumber',
            'shippingAddress',
            'active',
            'isActive',
            'createdAt',
            'profilePictureURL'
        )
            ->orderByDesc('id')
            ->skip(($page - 1) * $limit)
            ->take($limit)
            ->get();

        $items = $rows->map(function ($u) {
            $fullName = trim(($u->firstName ?? '') . ' ' . ($u->lastName ?? ''));
            $zoneId = $u->shippingAddress
                ? \App\Http\Controllers\UserController::extractZoneFromShippingAddress($u->shippingAddress)
                : '';

            // Format createdAt with Asia/Kolkata timezone
            $createdAtFormatted = '';
            if ($u->createdAt) {
                try {
                    // Handle JSON string format (e.g., "2025-10-16T07:13:41.487000Z")
                    $dateStr = is_string($u->createdAt) ? trim($u->createdAt, '"') : $u->createdAt;

                    // Parse the date
                    $date = Carbon::parse($dateStr);

                    // Convert to Asia/Kolkata timezone
                    $date->setTimezone('Asia/Kolkata');

                    // Format as: Dec 19, 2025 04:10 AM
                    $createdAtFormatted = $date->format('M d, Y h:i A');
                } catch (\Exception $e) {
                    // Fallback to original value if parsing fails
                    $createdAtFormatted = (string) $u->createdAt;
                }
            }

            $walletCoins = optional($u->customerWallet)->coin_balance ?? 0;
            $streak = optional($u->latestDailyCheckin)->streak_day_number ?? 0;
            $referredBy = null;
            $referralCodeUsed = null;

            if ($u->referredBy) {
                $referredBy = [
                    'name'  => trim($u->referredBy->firstName . ' ' . $u->referredBy->lastName),
                    'email' => $u->referredBy->email,
                ];
            }

//            if ($u->referralUsed) {
//                $referralCodeUsed = $u->referralUsed->referral_code;
//            }


            return [
                'id' => (string) ($u->firebase_id ?: $u->id),
                'firstName' => $u->firstName,
                'lastName' => $u->lastName,
                'fullName' => $fullName,
                'email' => (string) ($u->email ?? ''),
                'phoneNumber' => (string) ($u->phoneNumber ?? ''),
                'zoneId' => (string) $zoneId,
                'createdAt' => $createdAtFormatted,
//                'active' => in_array((string) $u->active, ['1','true'], true) || (bool) ($u->isActive ?? 0),
                'active' => ($u->active == 1 || $u->isActive == 1) ? 1 : 0,
                'profilePictureURL' => $u->profilePictureURL,
                'walletCoins' => $walletCoins,
                'streak' => $streak,
                'myReferralCode' => $u->referral_code,
                'referredBy' => $referredBy,
//                'referralCodeUsed' => $referralCodeUsed,
                ];
        })->all();

        return response()->json([
            'status' => true,
            'data' => $items,
            'meta' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'has_more' => ($page * $limit) < $total,
            ],
        ]);
    }

    public function destroy(string $id)
    {
        $user = AppUser::where('firebase_id', $id)->orWhere('id', $id)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['status' => true]);
    }

    public function setActive(Request $request, string $id)
    {
        $isActive = filter_var($request->input('active', false), FILTER_VALIDATE_BOOLEAN);
        $user = AppUser::where('firebase_id', $id)->orWhere('id', $id)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found'], 404);
        }
        $user->active = $isActive ? 1 : 0;
        $user->isActive = $isActive ? 1 : 0;
        $user->save();
        return response()->json(['status' => true]);
    }
    private function exportCSV($users)
    {
        return new StreamedResponse(function () use ($users) {
            $handle = fopen('php://output', 'w');

            // CSV Header
            fputcsv($handle, [
                'Name',
                'Email',
                'Phone',
                'Zone',
                'Active',
                'Created At'
            ]);

            foreach ($users as $user) {
                $isActive = ($user->active == 1 || $user->isActive == 1);

                // Format createdAt with Asia/Kolkata timezone
                $createdAtFormatted = '';
                if ($user->createdAt) {
                    try {
                        $dateStr = is_string($user->createdAt) ? trim($user->createdAt, '"') : $user->createdAt;
                        $date = Carbon::parse($dateStr);
                        $date->setTimezone('Asia/Kolkata');
                        $createdAtFormatted = $date->format('M d, Y h:i A');
                    } catch (\Exception $e) {
                        $createdAtFormatted = '';
                    }
                }

                fputcsv($handle, [
                    trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? '')),
                    $user->email ?? '',
                    $user->phoneNumber ?? '',
                    $user->zone_name ?? 'Not Assigned',
                    $isActive ? 'Active' : 'Inactive',
                    $createdAtFormatted
                ]);

            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users.csv"',
        ]);
    }
    private function exportPDF($users)
    {
        try {
            $pdf = Pdf::loadView('exports.users-pdf', ['users' => $users])
                ->setPaper('A4', 'portrait')
                ->setOption('enable-local-file-access', true);

            return $pdf->download('users.pdf');
        } catch (\Exception $e) {
            \Log::error('PDF Export Error: ' . $e->getMessage());
            abort(500, 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    private function exportExcel($users)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\UsersExport($users),
            'users.xlsx'
        );
    }

    public function export(Request $request)
    {
        $query = AppUser::query();

        // Role - default to customer
        $role = $request->input('role', $request->query('role', 'customer'));
        if (!empty($role)) {
            $query->where('role', $role);
        }

        // Active
        $active = $request->input('active', $request->query('active'));
        if ($active !== null && $active !== '') {
            $query->where('active', (int) $active);
        }

        // Zone filter - search in shippingAddress JSON column (same as index method)
        $zoneId = $request->input('zoneId', $request->query('zoneId'));
        if (!empty($zoneId) && $zoneId !== '') {
            $query->where(function($q) use ($zoneId) {
                // Use JSON_EXTRACT for better performance (MySQL 5.7+)
                $q->whereRaw('JSON_SEARCH(shippingAddress, "one", ?, NULL, "$[*].zoneId") IS NOT NULL', [$zoneId])
                    ->orWhereRaw('JSON_EXTRACT(shippingAddress, "$[0].zoneId") = ?', [$zoneId])
                    ->orWhere('shippingAddress', 'like', "%\"zoneId\":\"$zoneId\"%"); // Fallback for older MySQL
            });
        }

        // Search
        $search = trim((string) ($request->input('search', $request->query('search', ''))));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('firstName', 'like', "%$search%")
                    ->orWhere('lastName', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('phoneNumber', 'like', "%$search%");
            });
        }

        // Date range filter (same logic as index method)
        $dateRange = $request->input('date_range', $request->query('date_range'));
        $from = $request->input('from', $request->query('from'));
        $to = $request->input('to', $request->query('to'));

        // Handle date range presets
        if ($dateRange === 'last_24_hours') {
            $query->where('createdAt', '>=', now()->subDay()->toDateTimeString());
            $query->where('createdAt', '<=', now()->toDateTimeString());
        } elseif ($dateRange === 'last_week') {
            $query->where('createdAt', '>=', now()->subWeek()->startOfDay()->toDateTimeString());
            $query->where('createdAt', '<=', now()->endOfDay()->toDateTimeString());
        } elseif ($dateRange === 'last_month') {
            $query->where('createdAt', '>=', now()->subMonth()->startOfDay()->toDateTimeString());
            $query->where('createdAt', '<=', now()->endOfDay()->toDateTimeString());
        } elseif ($dateRange === 'all_orders' || $dateRange === 'all_users') {
            // Show all users - skip date filtering entirely
            // Do nothing - no date filter applied
        } elseif (!empty($from) || !empty($to)) {
            // Custom range or direct from/to parameters
            if (!empty($from)) {
                $query->where('createdAt', '>=', $from);
            }
            if (!empty($to)) {
                $query->where('createdAt', '<=', $to);
            }
        } else {
            // No date range selected - default to today only
            // Use range query instead of whereDate for better index usage
            $today = Carbon::today();
            $query->where('createdAt', '>=', $today->startOfDay()->toDateTimeString())
                ->where('createdAt', '<=', $today->endOfDay()->toDateTimeString());
        }

        // ORDER - Only fetch needed columns for export
        $users = $query->select(
            'id',
            'firebase_id',
            'firstName',
            'lastName',
            'email',
            'phoneNumber',
            'shippingAddress',
            'active',
            'isActive',
            'createdAt'
        )->orderByDesc('id')->get();

        // Extract zoneId from shippingAddress and fetch zone names
        $zoneIds = [];
        foreach ($users as $user) {
            $zoneId = \App\Http\Controllers\UserController::extractZoneFromShippingAddress($user->shippingAddress);
            if (!empty($zoneId)) {
                $zoneIds[] = $zoneId;
            }
        }

        // Fetch zone names for all zoneIds
        $zones = [];
        if (!empty($zoneIds)) {
            $zoneRecords = DB::table('zone')
                ->whereIn('id', array_unique($zoneIds))
                ->pluck('name', 'id')
                ->toArray();
            $zones = $zoneRecords;
        }

        // Map zone names to users
        $users = $users->map(function ($user) use ($zones) {
            $zoneId = \App\Http\Controllers\UserController::extractZoneFromShippingAddress($user->shippingAddress);
            $user->zone_name = !empty($zoneId) && isset($zones[$zoneId])
                ? $zones[$zoneId]
                : 'Not Assigned';
            return $user;
        });

        if ($request->type === 'pdf' && $users->count() > 500) {
            return response()->json([
                'success' => false,
                'message' => 'PDF export is limited to 200 records. Use Excel or CSV for full data.'
            ], 422);
        }

        return match ($request->type) {
            'csv'   => $this->exportCSV($users),
            'pdf'   => $this->exportPDF($users),
            'excel' => $this->exportExcel($users),
            default => abort(404),
        };
    }
    public function userData($id)
    {
        $user = AppUser::with(['referredBy'])
            ->where('firebase_id', $id)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $shippingAddress = $user->shippingAddress;
        if (is_string($shippingAddress)) {
            $shippingAddress = json_decode($shippingAddress, true);
        }

        $referredBy = null;
        if ($user->referredBy) {
            $referredBy = [
                'name'  => trim(($user->referredBy->firstName ?? '') . ' ' . ($user->referredBy->lastName ?? '')),
                'email' => $user->referredBy->email ?? '',
            ];
        }

        // Wallet coins / balance: safe when customer_wallet table does not exist (e.g. production without coins)
        $walletCoins = 0;
        $walletCoinsBalance = '0.00';
        $streak = 0;
        try {
            if (Schema::hasTable('customer_wallet')) {
                $wallet = DB::table('customer_wallet')->where('user_id', $user->id)->first();
                if ($wallet) {
                    $walletCoins = (int) ($wallet->coin_balance ?? 0);
                    $walletCoinsBalance = number_format((int) ($wallet->money_balance_paise ?? 0) / 100, 2, '.', '');
                }
            }
            if (Schema::hasTable('daily_checkins')) {
                $streakRow = DB::table('daily_checkins')->where('user_id', $user->id)->orderByDesc('id')->first();
                $streak = (int) ($streakRow->streak_day_number ?? 0);
            }
        } catch (\Throwable $e) {
            // keep defaults 0 / 0.00
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (string) ($user->firebase_id ?: $user->id),
                'firstName' => $user->firstName,
                'lastName' => $user->lastName,
                'email' => $user->email,
                'phoneNumber' => $user->phoneNumber,
                'wallet_amount' => $user->wallet_amount ?? 0,
                'walletCoins' => $walletCoins,
                'walletCoinsBalance' => $walletCoinsBalance,
                'streak' => $streak,
                'profilePictureURL' => $user->profilePictureURL,
                'referredBy' => $referredBy,
                'shippingAddress' => $shippingAddress,
            ]
        ]);
    }

    public function addWalletCoins(Request $request, $id)
    {
        if (!Schema::hasTable('customer_wallet') || !Schema::hasTable('coin_ledger')) {
            return response()->json([
                'success' => false,
                'message' => 'Wallet coins feature is not available (tables not set up).'
            ], 503);
        }

        DB::beginTransaction();

        try {
            $request->validate([
                'coins' => 'required|integer',
            ]);

            $user = AppUser::where('id', $id)
                ->orWhere('firebase_id', $id)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $coins = (int) $request->coins;

            $wallet = DB::table('customer_wallet')
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($wallet) {
                DB::table('customer_wallet')
                    ->where('user_id', $user->id)
                    ->update([
                        'coin_balance' => $wallet->coin_balance + $coins,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('customer_wallet')->insert([
                    'user_id' => $user->id,
                    'coin_balance' => $coins,
                    'money_balance_paise' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 🟢 INSERT COIN LEDGER HISTORY (THIS WAS MISSING)
//            DB::table('coin_ledger')->insert([
//                'user_id'         => $user->id,
//                'type'            => 'REWARDED',
//                'coins'           => $coins,
////                'reference_id'    => 'admin_' . $user->id . '_' . time(),
////                'metadata'        => json_encode([
////                    'by'     => 'admin',
////                    'reason' => 'manual_wallet_adjustment'
////                ]),
////                'idempotency_key' => 'admin_credit_' . Str::random(10),
//                'created_at'      => now(),
//            ]);
            $coins = (int) $coins; // ensure integer

            $type = $coins < 0 ? 'ADJUSTMENT' : 'REWARDED';

            DB::table('coin_ledger')->insert([
                'user_id'    => $user->id,
                'type'       => $type,
                'coins'      => $coins,
//                'created_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Wallet coins added successfully',
                'walletCoins' => DB::table('customer_wallet')
                    ->where('user_id', $user->id)
                    ->value('coin_balance')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Wallet Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong'
            ], 500);
        }
    }
}
