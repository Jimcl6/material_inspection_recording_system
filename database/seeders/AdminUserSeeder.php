<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'description' => 'Has unrestricted access to all features',
                'is_system' => true,
                'is_active' => true,
            ]
        );

        User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }
}
