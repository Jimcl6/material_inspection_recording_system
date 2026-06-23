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
        Schema::dropIfExists('temperature_readings');

        if (Schema::hasColumn('annealing_checks', 'machine_setting')) {
            Schema::table('annealing_checks', function (Blueprint $table) {
                $table->dropColumn('machine_setting');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * Previously stored values cannot be restored by rolling back.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('annealing_checks', 'machine_setting')) {
            Schema::table('annealing_checks', function (Blueprint $table) {
                $table->string('machine_setting', 100)
                    ->nullable()
                    ->after('machine_number');
            });
        }

        if (!Schema::hasTable('temperature_readings')) {
            Schema::create('temperature_readings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('annealing_check_id')
                    ->constrained('annealing_checks')
                    ->cascadeOnDelete();
                $table->time('reading_time');
                $table->decimal('temperature', 8, 2);
                $table->unsignedBigInteger('recorded_by');
                $table->timestamps();
                $table->index('reading_time');
            });
        }
    }
};
