<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('torque_records')) {
            return;
        }

        Schema::create('torque_records', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->string('model_series', 100)->nullable();
            $table->string('driver_model', 100)->nullable();
            $table->string('driver_type', 100)->nullable();
            $table->string('line_assigned', 100)->nullable();
            $table->string('control_no', 50)->nullable();
            $table->string('screw_type', 50)->nullable();
            $table->string('process_assigned', 100)->nullable();
            $table->string('person_in_charge', 100)->nullable();
            $table->time('time_am')->nullable();
            $table->string('torque_am', 20)->nullable();
            $table->time('time_pm')->nullable();
            $table->string('torque_pm', 20)->nullable();
            $table->string('col_remarks', 100)->nullable();
            $table->string('checked_by', 100)->nullable();

            $table->index('date');
            $table->index('driver_model');
            $table->index('line_assigned');
            $table->index(['date', 'screw_type', 'process_assigned', 'line_assigned'], 'torque_records_duplicate_lookup_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('torque_records');
    }
};
