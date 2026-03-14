<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Make new fields nullable to handle existing users
            $table->string('employee_id')->nullable()->change();
            $table->string('status')->default('active')->change();
        });

        // Update existing users to have default values
        \DB::statement("UPDATE users SET employee_id = CONCAT('EMP', id) WHERE employee_id IS NULL");
        \DB::statement("UPDATE users SET status = 'active' WHERE status IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
        });
    }
};
