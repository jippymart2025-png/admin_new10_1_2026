<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImpersonationPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Add impersonation permission to restaurants permissions
        $adminRoleId = 1; // Assuming role_id = 1 is admin
        
        // Check if impersonation permission already exists for admin role
        $exists = DB::table('permissions')
            ->where('role_id', $adminRoleId)
            ->where('permission', 'restaurants')
            ->where('routes', 'restaurants.impersonate')
            ->exists();

        if (!$exists) {
            DB::table('permissions')->insert([
                'role_id' => $adminRoleId,
                'permission' => 'restaurants',
                'routes' => 'restaurants.impersonate',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "Added impersonation permission to admin role\n";
        } else {
            echo "Impersonation permission already exists for admin role\n";
        }
    }
}
