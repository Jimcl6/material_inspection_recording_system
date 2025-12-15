<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        DB::table('users')->truncate();
        DB::table('roles')->truncate();
        
        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create roles
        $adminRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Has full access to all features'
        ]);

        Role::create([
            'name' => 'User',
            'slug' => 'user',
            'description' => 'Standard user with limited access'
        ]);

        Role::create([
            'name' => 'Inspector',
            'slug' => 'inspector',
            'description' => 'Can perform inspections'
        ]);

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id, // Use the ID from the created role
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
    }
}