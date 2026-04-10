<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Item codes table for validation rules
        Schema::create('diaphragm_item_codes', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 50)->unique();
            $table->string('item_name', 100)->nullable();
            $table->decimal('strength_min', 5, 2)->default(0.30);
            $table->enum('measurement_1_type', ['range', 'data_recording'])->default('data_recording');
            $table->decimal('measurement_1_min', 6, 3)->nullable();
            $table->decimal('measurement_1_max', 6, 3)->nullable();
            $table->enum('circumference_diff_type', ['max_limit', 'data_recording'])->default('data_recording');
            $table->decimal('circumference_diff_max', 5, 2)->nullable();
            $table->timestamps();
        });

        // Main checksheet table
        Schema::create('diaphragm_welding_checksheets', function (Blueprint $table) {
            $table->id();
            $table->string('item_name', 100)->nullable();
            $table->string('item_code', 50)->nullable();
            $table->string('month_year', 20)->nullable();
            $table->date('production_date');
            $table->string('lasermark_lot_number', 50)->nullable();
            $table->string('machine_no', 10)->nullable();
            $table->string('letter_code', 5)->nullable();
            $table->string('df_rubber_lot', 50)->nullable();
            $table->string('center_plate_a_lot', 50)->nullable();
            $table->string('center_plate_b_lot', 50)->nullable();
            $table->integer('prod_qty')->nullable();
            $table->string('jo_number', 50)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checked_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('item_code');
            $table->index('production_date');
            $table->index('status');
        });

        // Samples table for welding data
        Schema::create('diaphragm_welding_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_id')->constrained('diaphragm_welding_checksheets')->cascadeOnDelete();
            $table->string('check_item', 50); // collapse_depth, collapse_time, strength, appearance, welding_condition, measurement_1-5
            $table->string('sample_1', 20)->nullable();
            $table->string('sample_2', 20)->nullable();
            $table->string('sample_3', 20)->nullable();
            $table->string('sample_4', 20)->nullable();
            $table->string('sample_5', 20)->nullable();
            $table->timestamps();

            $table->index(['checksheet_id', 'check_item']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('diaphragm_welding_samples');
        Schema::dropIfExists('diaphragm_welding_checksheets');
        Schema::dropIfExists('diaphragm_item_codes');
    }
};
