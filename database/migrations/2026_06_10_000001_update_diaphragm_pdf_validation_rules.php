<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rules = [
            'DFB480300P' => [
                'strength_min' => 0.15,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.280,
                'measurement_1_max' => 7.590,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            'DFB660024V' => [
                'strength_min' => 0.30,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.890,
                'measurement_1_max' => 7.950,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            'DFB4803000' => [
                'strength_min' => 0.15,
                'measurement_1_type' => 'not_recorded',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
        ];

        $this->syncWeldingItemConfigs($rules);
        $this->syncLegacyDiaphragmItemCodes($rules);
    }

    public function down(): void
    {
        if (! Schema::hasTable('welding_item_configs') || ! Schema::hasTable('welding_checksheet_types')) {
            return;
        }

        $diaphragmTypeId = DB::table('welding_checksheet_types')->where('key', 'diaphragm')->value('id');
        if (! $diaphragmTypeId) {
            return;
        }

        DB::table('welding_item_configs')
            ->where('checksheet_type_id', $diaphragmTypeId)
            ->where('item_code', 'DFB480300P')
            ->update([
                'validation_rules' => json_encode([
                    'strength_min' => 0.15,
                    'measurement_1_type' => 'range',
                    'measurement_1_min' => 7.280,
                    'measurement_1_max' => 7.590,
                    'circumference_diff_type' => 'data_recording',
                    'circumference_diff_max' => null,
                ]),
                'updated_at' => now(),
            ]);

        DB::table('welding_item_configs')
            ->where('checksheet_type_id', $diaphragmTypeId)
            ->whereIn('item_code', ['DFB660024V', 'DFB4803000'])
            ->delete();
    }

    private function syncWeldingItemConfigs(array $rules): void
    {
        if (! Schema::hasTable('welding_item_configs') || ! Schema::hasTable('welding_checksheet_types')) {
            return;
        }

        $diaphragmTypeId = DB::table('welding_checksheet_types')->where('key', 'diaphragm')->value('id');
        if (! $diaphragmTypeId) {
            return;
        }

        foreach ($rules as $itemCode => $validationRules) {
            $keys = [
                'checksheet_type_id' => $diaphragmTypeId,
                'item_code' => $itemCode,
            ];
            $values = [
                'item_name' => null,
                'validation_rules' => json_encode($validationRules),
                'is_active' => true,
                'updated_at' => now(),
            ];

            $exists = DB::table('welding_item_configs')->where($keys)->exists();
            if ($exists) {
                DB::table('welding_item_configs')->where($keys)->update($values);

                continue;
            }

            DB::table('welding_item_configs')->insert($keys + $values + ['created_at' => now()]);
        }
    }

    private function syncLegacyDiaphragmItemCodes(array $rules): void
    {
        if (! Schema::hasTable('diaphragm_item_codes')) {
            return;
        }

        foreach ($rules as $itemCode => $validationRules) {
            $keys = ['item_code' => $itemCode];
            $values = [
                'item_name' => null,
                'strength_min' => $validationRules['strength_min'],
                'measurement_1_type' => $validationRules['measurement_1_type'] === 'not_recorded'
                    ? 'data_recording'
                    : $validationRules['measurement_1_type'],
                'measurement_1_min' => $validationRules['measurement_1_min'],
                'measurement_1_max' => $validationRules['measurement_1_max'],
                'circumference_diff_type' => $validationRules['circumference_diff_type'],
                'circumference_diff_max' => $validationRules['circumference_diff_max'],
                'updated_at' => now(),
            ];

            $exists = DB::table('diaphragm_item_codes')->where($keys)->exists();
            if ($exists) {
                DB::table('diaphragm_item_codes')->where($keys)->update($values);

                continue;
            }

            DB::table('diaphragm_item_codes')->insert($keys + $values + ['created_at' => now()]);
        }
    }
};
