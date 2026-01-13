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
        Schema::table('productionbatches', function (Blueprint $table) {
            // Add magnetism_checksheet_id column if it doesn't exist
            if (!Schema::hasColumn('productionbatches', 'magnetism_checksheet_id')) {
                $table->unsignedBigInteger('magnetism_checksheet_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productionbatches', function (Blueprint $table) {
            $table->dropColumn('magnetism_checksheet_id');
        });
    }
};
