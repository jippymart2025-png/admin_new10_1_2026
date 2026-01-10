<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PromotionsMediaActivityLogsPermissionsSeeder extends Seeder
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
        
        // Define permissions for promotions, media, and activity-logs
        $permissions = [
            'promotions' => ['promotions', 'promotions.create', 'promotions.edit', 'promotions.delete'],
            'media' => ['media', 'media.create', 'media.edit', 'media.delete'],
            'activity-logs' => ['activity-logs']
        ];
        
        $addedCount = 0;
        
        foreach ($permissions as $permission => $routes) {
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
        
        $this->command->info('Successfully added ' . $addedCount . ' permissions to Super Administrator role!');
        $this->command->info('Promotions, Media, and Activity Logs menus should now be visible for super admin users.');
    }
}
