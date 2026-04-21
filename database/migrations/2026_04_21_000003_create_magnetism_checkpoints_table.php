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
        Schema::create('magnetism_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_id')->constrained('magnetism_checksheets')->onDelete('cascade');
            $table->date('production_date');
            $table->tinyInteger('checkpoint_number')->unsigned();

            // First Inspection samples (5 values)
            $table->decimal('sample1_first', 5, 1)->nullable();
            $table->decimal('sample2_first', 5, 1)->nullable();
            $table->decimal('sample3_first', 5, 1)->nullable();
            $table->decimal('sample4_first', 5, 1)->nullable();
            $table->decimal('sample5_first', 5, 1)->nullable();
            $table->string('operator_first', 100)->nullable();
            $table->string('judgment_first', 10)->nullable();

            // Last Inspection samples (5 values)
            $table->decimal('sample1_last', 5, 1)->nullable();
            $table->decimal('sample2_last', 5, 1)->nullable();
            $table->decimal('sample3_last', 5, 1)->nullable();
            $table->decimal('sample4_last', 5, 1)->nullable();
            $table->decimal('sample5_last', 5, 1)->nullable();
            $table->string('operator_last', 100)->nullable();
            $table->string('judgment_last', 10)->nullable();

            $table->string('checked_by', 100)->nullable();
            $table->timestamps();

            $table->index(['checksheet_id', 'production_date']);
            $table->unique(['checksheet_id', 'production_date', 'checkpoint_number'], 'unique_checkpoint');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magnetism_checkpoints');
    }
};
