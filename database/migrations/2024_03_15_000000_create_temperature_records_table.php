<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('temperature_records', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number');
            $table->decimal('temperature', 8, 2);
            $table->string('checked_by');
            $table->dateTime('checked_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('temperature_records');
    }
};
