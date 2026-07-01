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
        if (Schema::hasTable('temp_records')) {
            return;
        }

        Schema::create('temp_records', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('model_series', 100)->nullable();
            $table->string('solder_model', 100)->nullable();
            $table->string('line_assigned', 100)->nullable();
            $table->string('control_no', 50)->nullable();
            $table->string('equipment_type', 100)->nullable();
            $table->string('process_assigned', 100)->nullable();
            $table->string('person_in_charge', 100)->nullable();
            $table->time('time_am')->nullable();
            $table->string('temp_am', 20)->nullable();
            $table->time('time_pm')->nullable();
            $table->string('temp_pm', 20)->nullable();
            $table->string('col_remarks', 100)->nullable();
            $table->string('checked_by', 100)->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('equipment_type');
            $table->index('line_assigned');
            $table->index(['date', 'equipment_type', 'control_no'], 'temp_records_duplicate_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_records');
    }
};
