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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // e.g., 'create', 'update', 'delete', 'login'
            $table->string('subject_type'); // e.g., 'AnnealingCheck', 'TempRecord'
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description'); // Human-readable description
            $table->json('properties')->nullable(); // Additional data
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activities');
    }
};
