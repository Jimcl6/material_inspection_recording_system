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
        Schema::create('magnetism_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checksheet_id')->constrained('magnetism_checksheets')->onDelete('cascade');
            $table->date('production_date');
            $table->char('letter_code', 1);
            $table->string('material_lot_number', 50);
            $table->string('qr_code', 50);
            $table->integer('produce_qty')->unsigned();
            $table->string('job_number', 50);
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['checksheet_id', 'production_date']);
            $table->index(['material_lot_number']);
            $table->index(['qr_code']);
            $table->unique(['checksheet_id', 'production_date', 'letter_code'], 'unique_batch_letter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magnetism_batches');
    }
};
