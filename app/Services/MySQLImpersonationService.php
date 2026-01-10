<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MySQLImpersonationService
{
    /**
     * Generate impersonation token for a restaurant owner (MYSQL)
     */

    public function generateToken($restaurantId, $adminId)
    {
        $owner = DB::table('users')
            ->where('vendorID', $restaurantId)
            ->where('role', 'vendor')
            ->first();

        if (!$owner) {
            return ['success' => false, 'error' => 'Owner not found'];
        }

        $token = 'imp_' . bin2hex(random_bytes(30));
        $expiresAt = time() + (5 * 60);

        DB::table('impersonation_tokens')->insert([
            'token' => $token,
            'user_id' => $owner->id,
            'restaurant_id' => $restaurantId,
            'expires_at' => $expiresAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'success' => true,
            'key' => $token,
            'owner_name' => $owner->firstName . ' ' . $owner->lastName
        ];
    }


    /**
     * Validate impersonation key
     */
    public function validateKey($key)
    {
        return Cache::get($key);
    }
}
