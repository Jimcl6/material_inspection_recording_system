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
        if (!DB::table('roles')->where('slug', 'admin')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Has full access to all features',
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
        // Remove admin role
        DB::table('roles')->where('slug', 'admin')->delete();
    }
};
