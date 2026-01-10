<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class MenuPeriodsPermissionsSeeder extends Seeder
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
        
        // Define menu_periods permissions
        $menuPeriodsPermissions = [
            'menu-periods' => ['menu-periods', 'menu-periods.create', 'menu-periods.edit', 'menu-periods.delete']
        ];
        
        $addedCount = 0;
        $existingCount = 0;
        
        foreach ($menuPeriodsPermissions as $module => $permissions) {
            foreach ($permissions as $permission) {
                // Check if permission already exists
                $existingPermission = Permission::where('role_id', $superAdminRole->id)
                    ->where('permission', $permission)
                    ->first();
                
                if (!$existingPermission) {
                    // Create new permission
                    Permission::create([
                        'role_id' => $superAdminRole->id,
                        'permission' => $permission,
                        'routes' => json_encode([]), // Add empty routes array
                    ]);
                    $addedCount++;
                    $this->command->info("✅ Added permission: {$permission}");
                } else {
                    $existingCount++;
                    $this->command->info("ℹ️  Permission already exists: {$permission}");
                }
            }
        }
        
        $this->command->info("\n📊 Summary:");
        $this->command->info("   - Added: {$addedCount} permissions");
        $this->command->info("   - Already existed: {$existingCount} permissions");
        $this->command->info("   - Total: " . ($addedCount + $existingCount) . " permissions");
        
        if ($addedCount > 0) {
            $this->command->info("\n🎉 Menu Periods permissions successfully added to Super Administrator role!");
        } else {
            $this->command->info("\nℹ️  All Menu Periods permissions already exist for Super Administrator role.");
        }
    }
}
