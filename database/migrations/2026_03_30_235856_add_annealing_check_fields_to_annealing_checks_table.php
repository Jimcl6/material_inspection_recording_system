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
            $table->decimal('temperature_setting', 8, 2)->nullable()->after('machine_setting');
            $table->time('annealing_time')->nullable()->after('temperature_setting');
            $table->string('damper_setting', 100)->nullable()->after('annealing_time');
            
            // Time sub-columns
            $table->time('time_in')->nullable()->after('damper_setting');
            $table->time('time_out')->nullable()->after('time_in');
            
            // Approval workflow fields
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->after('time_out');
            $table->timestamp('submitted_at')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('submitted_at');
            $table->text('approval_notes')->nullable()->after('approved_at');
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
