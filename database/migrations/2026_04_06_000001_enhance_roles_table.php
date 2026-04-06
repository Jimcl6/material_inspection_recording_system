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
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('description');
            $table->boolean('is_active')->default(true)->after('is_system');
        });

        // Mark existing system roles
        DB::table('roles')->whereIn('slug', ['admin', 'user', 'inspector'])->update(['is_system' => true]);

        // Create super_admin role if it doesn't exist
        $superAdminExists = DB::table('roles')->where('slug', 'super_admin')->exists();
        
        if (!$superAdminExists) {
            DB::table('roles')->insert([
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'description' => 'Full system access with administrative privileges',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove super_admin role
        DB::table('roles')->where('slug', 'super_admin')->delete();

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['is_system', 'is_active']);
        });
    }
};
