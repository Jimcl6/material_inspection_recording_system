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
        Schema::create('magnetism_checksheets', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 50);
            $table->string('item_name', 100);
            $table->string('machine_no', 50);
            $table->tinyInteger('month')->unsigned();
            $table->smallInteger('year')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['item_code']);
            $table->index(['month', 'year']);
            $table->unique(['item_code', 'machine_no', 'month', 'year'], 'unique_checksheet');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('magnetism_checksheets');
    }
};
