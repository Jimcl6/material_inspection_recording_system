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
    public function up(): void
    {
        Schema::create('inspection_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('BatchID');
            $table->integer('CheckpointNumber');
            $table->string('InspectorName_First', 255)->nullable();
            $table->string('Judgement_First', 255)->nullable();
            $table->string('InspectorName_Last', 255)->nullable();
            $table->string('Judgement_Last', 255)->nullable();
            // Remove timestamps since model doesn't use them
            
            // Remove foreign key constraint for now
            // $table->foreign('BatchID')->references('BatchID')->on('productionbatches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_checkpoints');
    }
};
