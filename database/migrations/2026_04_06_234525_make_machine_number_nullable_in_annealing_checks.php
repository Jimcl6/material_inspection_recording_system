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
            $table->string('machine_number', 50)->nullable()->change();
            $table->string('supplier_lot_number', 100)->nullable()->change();
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
            $table->string('machine_number', 50)->nullable(false)->change();
            $table->string('supplier_lot_number', 100)->nullable(false)->change();
        });
    }
};
