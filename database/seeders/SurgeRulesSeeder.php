<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class SurgeRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find Super Administrator role
        $superAdminRole = Role::where('role_name', 'Super Administrator')->first();

        if (!$superAdminRole) {
            $this->command->error('Super Administrator role not found!');
            return;
        }

        $this->command->info('Found Super Administrator role (ID: ' . $superAdminRole->id . ')');

        // Define surge rules permissions
        $surgeRulesPermissions = [
            'surge-rules' => [
                'surge-rules',
                'settings.app.surgeRules'
            ]
        ];

        $addedCount = 0;

        foreach ($surgeRulesPermissions as $permission => $routes) {
            foreach ($routes as $route) {
                $existingPermission = Permission::where('role_id', $superAdminRole->id)
                    ->where('permission', $permission)
                    ->where('routes', $route)
                    ->first();

                if (!$existingPermission) {
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

        $this->command->info('Successfully added ' . $addedCount . ' surge rules permissions to Super Administrator role!');
    }
}
