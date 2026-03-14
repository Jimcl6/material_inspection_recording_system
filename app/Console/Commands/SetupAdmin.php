<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupAdmin extends Command
{
    protected $signature = 'setup:admin';
    protected $description = 'Create admin role and assign to first user';

    public function handle()
    {
        $this->info('Available users:');
        $users = DB::table('users')->select('id', 'email', 'name')->get();
        foreach ($users as $user) {
            $this->line("- ID: {$user->id}, Email: {$user->email}, Name: {$user->name}");
        }

        // Create admin role
        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        if (!$adminRole) {
            $roleId = DB::table('roles')->insertGetId([
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Has full access to all features',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("\nCreated admin role with ID: {$roleId}");
            
            // Update the first user to have admin role
            if (isset($users[0])) {
                DB::table('users')
                    ->where('id', $users[0]->id)
                    ->update(['role_id' => $roleId]);
                
                $this->info("Updated user {$users[0]->email} (ID: {$users[0]->id}) to have admin role");
            }
        } else {
            $this->info("\nAdmin role already exists with ID: {$adminRole->id}");
        }

        return 0;
    }
}
