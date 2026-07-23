<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Clear the roles table first
        \DB::table('roles')->delete();

        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Has full access to all features'
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Standard user with limited access'
            ],
            [
                'name' => 'Inspector',
                'slug' => 'inspector',
                'description' => 'Can perform inspections'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}