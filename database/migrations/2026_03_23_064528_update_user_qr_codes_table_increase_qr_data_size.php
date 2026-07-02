<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::table('user_qr_codes', function (Blueprint $table) {
            $table->dropUnique(['qr_data']);
        });

        Schema::table('user_qr_codes', function (Blueprint $table) {
            $table->text('qr_data')->change();
        });

        DB::statement('ALTER TABLE user_qr_codes ADD UNIQUE KEY user_qr_codes_qr_data_unique (qr_data(191))');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_qr_codes', function (Blueprint $table) {
            $table->dropUnique(['qr_data']);
        });

        Schema::table('user_qr_codes', function (Blueprint $table) {
            $table->string('qr_data', 500)->change();
            $table->unique('qr_data');
        });
    }
};
