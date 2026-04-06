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
            // Machine Setting sub-columns
            if (!Schema::hasColumn('annealing_checks', 'temperature_setting')) {
                $table->decimal('temperature_setting', 8, 2)->nullable()->after('machine_setting');
            }
            if (!Schema::hasColumn('annealing_checks', 'annealing_time')) {
                $table->time('annealing_time')->nullable()->after('temperature_setting');
            }
            if (!Schema::hasColumn('annealing_checks', 'damper_setting')) {
                $table->string('damper_setting', 100)->nullable()->after('annealing_time');
            }
            
            // Time sub-columns
            if (!Schema::hasColumn('annealing_checks', 'time_in')) {
                $table->time('time_in')->nullable()->after('damper_setting');
            }
            if (!Schema::hasColumn('annealing_checks', 'time_out')) {
                $table->time('time_out')->nullable()->after('time_in');
            }
            
            // Approval workflow fields
            if (!Schema::hasColumn('annealing_checks', 'status')) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->after('time_out');
            }
            if (!Schema::hasColumn('annealing_checks', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('annealing_checks', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn('annealing_checks', 'approval_notes')) {
                $table->text('approval_notes')->nullable()->after('approved_at');
            }
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
            $table->dropColumn([
                'temperature_setting',
                'annealing_time',
                'damper_setting',
                'time_in',
                'time_out',
                'status',
                'submitted_at',
                'approved_at',
                'approval_notes'
            ]);
        });
    }
};
