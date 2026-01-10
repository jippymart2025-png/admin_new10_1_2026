<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class MartSettingsPermissionsSeeder extends Seeder
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

        // Define mart settings permissions
        $martSettingsPermissions = [
            'mart-settings' => [
                'mart-settings',
                'settings.app.martSettings'
            ]
        ];

        $addedCount = 0;

        foreach ($martSettingsPermissions as $permission => $routes) {
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

        $this->command->info('Successfully added ' . $addedCount . ' mart settings permissions to Super Administrator role!');
    }
}
