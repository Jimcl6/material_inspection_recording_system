<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('annealing_checks')
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_at' => $now,
                'updated_at' => $now,
            ]);

        DB::table('welding_checksheets')
            ->where('status', 'pending')
            ->update([
                'status' => 'approved',
                'approved_at' => $now,
                'updated_at' => $now,
            ]);

        DB::table('approval_notifications')
            ->where('status', 'pending')
            ->update([
                'status' => 'acted',
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // Intentionally irreversible: finalized records cannot be safely
        // distinguished from records approved through the normal workflow.
    }
};
