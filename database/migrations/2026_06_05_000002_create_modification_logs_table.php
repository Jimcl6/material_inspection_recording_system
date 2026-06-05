<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('modification_logs')) {
            return;
        }

        Schema::create('modification_logs', function (Blueprint $table) {
            $table->id();
            $table->dateTime('prod_date')->nullable();
            $table->string('col_4m', 50)->nullable();
            $table->string('col_line', 50)->nullable();
            $table->string('model_code', 100);
            $table->string('item_for_modification');
            $table->text('nature_of_change')->nullable();
            $table->string('col_from')->nullable();
            $table->string('col_to')->nullable();
            $table->string('material_lot_no')->nullable();
            $table->string('start_serial')->nullable();
            $table->string('end_serial')->nullable();
            $table->string('recorded_by');
            $table->string('source_of_info')->nullable();
            $table->string('approved_by')->nullable();
            $table->string('job_no_transfer_order')->nullable();
            $table->text('col_remarks')->nullable();
            $table->timestamps();

            $table->index('prod_date');
            $table->index('col_line');
            $table->index('col_4m');
            $table->index('model_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modification_logs');
    }
};
