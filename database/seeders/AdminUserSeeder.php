<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Create admin role if it doesn't exist
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], [
            'name' => 'Administrator',
            'slug' => 'admin',
        ]);

        // Create admin user
        User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole->id,
            'email_verified_at' => now(),
        ]);
    }
}
