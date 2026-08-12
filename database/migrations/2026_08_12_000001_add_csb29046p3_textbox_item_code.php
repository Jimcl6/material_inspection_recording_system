<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('welding_checksheet_types') || ! Schema::hasTable('welding_item_configs')) {
            return;
        }

        $typeId = DB::table('welding_checksheet_types')->where('key', 'casing_tank')->value('id');
        if (! $typeId) {
            return;
        }

        $exists = DB::table('welding_item_configs')
            ->where('checksheet_type_id', $typeId)
            ->where('item_code', 'CSB29046P3-P')
            ->exists();

        if ($exists) {
            return;
        }

        $now = now();

        DB::table('welding_item_configs')->insert([
            'checksheet_type_id' => $typeId,
            'item_code' => 'CSB29046P3-P',
            'item_name' => null,
            'validation_rules' => json_encode([
                'collapse_depth_min' => 0.37,
                'collapse_time_min' => 1.3,
                'collapse_time_max' => 1.7,
            ], JSON_THROW_ON_ERROR),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        // Configuration rows may be referenced or customized after deployment.
    }
};
