<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserPermission;

class UserPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            'annealing' => ['view', 'create', 'update', 'delete', 'approve', 'reject', 'import', 'export'],
            'temperature' => ['view', 'create', 'update', 'delete', 'import', 'export'],
            'torque' => ['view', 'create', 'update', 'delete', 'import', 'export'],
            'magnetism' => ['view', 'create', 'update', 'delete', 'import', 'export'],
            'production' => ['view', 'create', 'update', 'delete', 'import', 'export'],
            'users' => ['view', 'create', 'update', 'delete', 'activate', 'deactivate'],
            'departments' => ['view', 'create', 'update', 'delete', 'activate', 'deactivate'],
            'positions' => ['view', 'create', 'update', 'delete', 'activate', 'deactivate'],
            'roles' => ['view', 'create', 'update', 'delete', 'activate', 'deactivate'],
            'activities' => ['view', 'delete'],
            'reports' => ['view', 'create', 'export'],
            'auth' => ['login', 'logout'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                UserPermission::createOrUpdate(
                    ucfirst($action) . ' ' . ucfirst($module),
                    $module,
                    $action,
                    'Permission to ' . $action . ' ' . $module . ' module'
                );
            }
        }

        $this->command->info('User permissions seeded successfully.');
    }
}
