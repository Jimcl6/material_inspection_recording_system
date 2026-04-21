<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Drop the unique constraint that enforces unique letter_code per date.
     * Letter codes are now tied to material_lot_number, not production_date.
     * Same material lot = same letter code, even across different dates.
     */
    public function up(): void
    {
        Schema::table('magnetism_batches', function (Blueprint $table) {
            $table->dropUnique('unique_batch_letter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('magnetism_batches', function (Blueprint $table) {
            $table->unique(['checksheet_id', 'production_date', 'letter_code'], 'unique_batch_letter');
        });
    }
};
