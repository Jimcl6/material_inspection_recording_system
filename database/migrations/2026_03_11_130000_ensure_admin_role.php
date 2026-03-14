<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if admin role exists, if not create it
        $adminRole = DB::table('roles')->where('slug', 'admin')->first();
        if (!$adminRole) {
            $adminRoleId = DB::table('roles')->insertGetId([
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Has full access to all features',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            echo "Created admin role with ID: {$adminRoleId}\n";
            
            // Update the first user to be admin
            $firstUser = DB::table('users')->orderBy('id')->first();
            if ($firstUser) {
                DB::table('users')
                    ->where('id', $firstUser->id)
                    ->update(['role_id' => $adminRoleId]);
                
                echo "Updated user {$firstUser->email} to have admin role\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove admin role
        DB::table('roles')->where('slug', 'admin')->delete();
    }
};
