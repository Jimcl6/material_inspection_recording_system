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
        Schema::create('material_parts', function (Blueprint $table) {
            $table->id();
            $table->string('material_type', 50);
            $table->date('date');
            $table->string('item_block_code', 100);
            $table->char('letter_code', 1);
            $table->string('main_lot_number', 50);
            $table->text('sub_lot_numbers')->nullable();
            $table->unsignedInteger('produced_qty')->default(0);
            $table->string('operator', 100)->nullable();
            $table->string('job_number', 100)->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('material_type');
            $table->index('date');
            $table->index('item_block_code');
            $table->index('main_lot_number');
            $table->index(['material_type', 'date', 'item_block_code']);
            
            // Unique constraint to prevent duplicates
            $table->unique(['material_type', 'date', 'item_block_code', 'letter_code', 'main_lot_number'], 'unique_material_record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_parts');
    }
};
