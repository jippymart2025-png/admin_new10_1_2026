<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class MartBannerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Find Super Administrator role
        $superAdminRole = Role::where('role_name', 'Super Administrator')->first();
        
        if (!$superAdminRole) {
            $this->command->error('Super Administrator role not found!');
            return;
        }
        
        $this->command->info('Found Super Administrator role (ID: ' . $superAdminRole->id . ')');
        
        // Define mart banners permissions
        $martBannerPermissions = [
            'mart_banners' => ['mart_banners', 'mart_banners.create', 'mart_banners.edit', 'mart_banners.delete']
        ];
        
        $addedCount = 0;
        
        foreach ($martBannerPermissions as $permission => $routes) {
            foreach ($routes as $route) {
                // Check if permission already exists
                $existingPermission = Permission::where('role_id', $superAdminRole->id)
                    ->where('permission', $permission)
                    ->where('routes', $route)
                    ->first();
                
                if (!$existingPermission) {
                    // Create new permission
                    Permission::create([
                        'role_id' => $superAdminRole->id,
                        'permission' => $permission,
                        'routes' => $route
                    ]);
                    
                    $this->command->info('Added permission: ' . $permission . ' -> ' . $route);
                    $addedCount++;
                } else {
                    $this->command->line('Permission already exists: ' . $permission . ' -> ' . $route);
                }
            }
        }
        
        $this->command->info('Successfully added ' . $addedCount . ' mart banners permissions to Super Administrator role!');
    }
}
