<?php

use App\Models\UserPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addApprovalColumns('temp_records');
        $this->addApprovalColumns('torque_records');
        $this->backfillApprovedState('temp_records');
        $this->backfillApprovedState('torque_records');

        foreach (['temperature', 'torque'] as $module) {
            foreach (['approve', 'reject'] as $action) {
                UserPermission::createOrUpdate(
                    ucfirst($action) . ' ' . ucfirst($module),
                    $module,
                    $action,
                    'Permission to ' . $action . ' ' . $module . ' module'
                );
            }
        }

        Schema::table('approval_notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('approval_notifications', 'module')) {
                $table->string('module', 50)->nullable()->after('annealing_check_id');
            }
            if (!Schema::hasColumn('approval_notifications', 'approvable_type')) {
                $table->string('approvable_type')->nullable()->after('module');
            }
            if (!Schema::hasColumn('approval_notifications', 'approvable_id')) {
                $table->unsignedBigInteger('approvable_id')->nullable()->after('approvable_type');
            }
        });

        if (Schema::hasColumn('approval_notifications', 'approvable_type') && Schema::hasColumn('approval_notifications', 'approvable_id')) {
            Schema::table('approval_notifications', function (Blueprint $table) {
                $table->index(['module', 'approvable_type', 'approvable_id'], 'approval_notifications_approvable_idx');
            });
        }

        DB::table('approval_notifications')
            ->whereNotNull('annealing_check_id')
            ->whereNull('module')
            ->update([
                'module' => 'annealing',
                'approvable_type' => App\Models\AnnealingCheck::class,
                'approvable_id' => DB::raw('annealing_check_id'),
            ]);

        $this->makeAnnealingNotificationColumnNullable();
    }

    public function down(): void
    {
        Schema::table('approval_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('approval_notifications', 'approvable_type') && Schema::hasColumn('approval_notifications', 'approvable_id')) {
                $table->dropIndex('approval_notifications_approvable_idx');
            }
        });

        Schema::table('approval_notifications', function (Blueprint $table) {
            foreach (['approvable_id', 'approvable_type', 'module'] as $column) {
                if (Schema::hasColumn('approval_notifications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        $this->dropApprovalColumns('temp_records');
        $this->dropApprovalColumns('torque_records');

        UserPermission::whereIn('module', ['temperature', 'torque'])
            ->whereIn('action', ['approve', 'reject'])
            ->delete();
    }

    private function addApprovalColumns(string $table): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (!Schema::hasColumn($table, 'status')) {
                $blueprint->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->after('checked_by');
            }
            if (!Schema::hasColumn($table, 'submitted_at')) {
                $blueprint->timestamp('submitted_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn($table, 'approved_at')) {
                $blueprint->timestamp('approved_at')->nullable()->after('submitted_at');
            }
            if (!Schema::hasColumn($table, 'approval_notes')) {
                $blueprint->text('approval_notes')->nullable()->after('approved_at');
            }
            $blueprint->index('status', $table . '_approval_status_idx');
        });
    }

    private function backfillApprovedState(string $table): void
    {
        $approvedAtExpression = Schema::hasColumn($table, 'created_at')
            ? DB::raw('COALESCE(created_at, NOW())')
            : DB::raw('NOW()');

        DB::table($table)
            ->whereNull('status')
            ->orWhere('status', '')
            ->update([
                'status' => 'approved',
                'approved_at' => $approvedAtExpression,
            ]);

        DB::table($table)
            ->where('status', 'approved')
            ->whereNull('approved_at')
            ->update([
                'approved_at' => $approvedAtExpression,
            ]);
    }

    private function dropApprovalColumns(string $table): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            if (Schema::hasColumn($table, 'status')) {
                $blueprint->dropIndex($table . '_approval_status_idx');
            }
        });

        Schema::table($table, function (Blueprint $blueprint) use ($table) {
            foreach (['approval_notes', 'approved_at', 'submitted_at', 'status'] as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $blueprint->dropColumn($column);
                }
            }
        });
    }

    private function makeAnnealingNotificationColumnNullable(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE approval_notifications MODIFY annealing_check_id BIGINT UNSIGNED NULL');
        }
    }
};
