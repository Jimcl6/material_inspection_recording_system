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
        Schema::table('annealing_checks', function (Blueprint $table) {
            // Change user ID columns to text columns to store names instead of IDs
            $table->string('pic_id', 255)->change();
            $table->string('checked_by_id', 255)->nullable()->change();
            $table->string('verified_by_id', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('annealing_checks', function (Blueprint $table) {
            // Revert back to integer columns (user IDs)
            $table->unsignedBigInteger('pic_id')->change();
            $table->unsignedBigInteger('checked_by_id')->nullable()->change();
            $table->unsignedBigInteger('verified_by_id')->nullable()->change();
        });
    }
};
