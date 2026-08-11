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

        DB::transaction(function () use ($typeId): void {
            foreach ($this->itemDefinitions() as $itemCode => $rules) {
                $exists = DB::table('welding_item_configs')
                    ->where('checksheet_type_id', $typeId)
                    ->where('item_code', $itemCode)
                    ->exists();

                if ($exists) {
                    continue;
                }

                $now = now();

                DB::table('welding_item_configs')->insert([
                    'checksheet_type_id' => $typeId,
                    'item_code' => $itemCode,
                    'item_name' => null,
                    'validation_rules' => json_encode($rules, JSON_THROW_ON_ERROR),
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Configuration rows may be referenced or customized after deployment.
    }

    private function itemDefinitions(): array
    {
        $standardRules = [
            'collapse_depth_min' => 0.37,
            'collapse_time_min' => 1.3,
            'collapse_time_max' => 1.7,
        ];

        $dataRecordingOnly = [
            'collapse_depth_min' => null,
            'collapse_time_min' => null,
            'collapse_time_max' => null,
        ];

        return [
            'CSB290053P' => $standardRules,
            'CSB290054P' => $standardRules,
            'CSB2900560-P' => $standardRules,
            'CSB290057P' => $standardRules,
            'CSB2900580-P' => $standardRules,
            'CSB2900590-P' => $standardRules,
            'CSB2900610-P' => $standardRules,
            'CSB2900620-P' => $dataRecordingOnly,
            'CSB29043P1-P' => $dataRecordingOnly,
            'CSB29044P1-P' => $standardRules,
            'CSB2904511-P' => $standardRules,
            'CSB29045P1-P' => $standardRules,
            'CSB29046P1-P' => $standardRules,
            'CSB29046P2-P' => $standardRules,
            'CSB29046P3-P' => $standardRules,
            'CSB29052P1' => $standardRules,
            'CSB29052P2' => $standardRules,
            'CSB29052P3' => $standardRules,
            'CSB29053P3' => $standardRules,
            'CSB641001P' => $dataRecordingOnly,
        ];
    }
};
