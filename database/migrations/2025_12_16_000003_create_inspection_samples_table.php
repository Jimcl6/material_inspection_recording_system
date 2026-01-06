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
        Schema::create('inspection_samples', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('CheckpointID');
            $table->integer('SampleOrder');
            $table->string('Phase', 10); // 'FIRST' or 'LAST'
            $table->string('Value', 255)->nullable();
            
            // Remove foreign key constraint for now
            // $table->foreign('CheckpointID')->references('CheckpointID')->on('inspection_checkpoints')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_samples');
    }
};
