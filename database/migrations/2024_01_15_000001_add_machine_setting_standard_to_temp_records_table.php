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
        Schema::table('temp_records', function (Blueprint $table) {
            $table->string('machine_setting_standard', 50)->nullable()->after('equipment_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_records', function (Blueprint $table) {
            $table->dropColumn('machine_setting_standard');
        });
    }
};
