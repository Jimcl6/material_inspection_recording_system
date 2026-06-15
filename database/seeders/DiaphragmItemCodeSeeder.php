<?php

namespace Database\Seeders;

use App\Models\DiaphragmItemCode;
use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DiaphragmItemCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $itemCodes = [
            [
                'item_code' => 'DFB8402000',
                'item_name' => null,
                'strength_min' => 0.40,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB8402001',
                'item_name' => null,
                'strength_min' => 0.40,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB660220P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB660023P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.890,
                'measurement_1_max' => 7.950,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            [
                'item_code' => 'DFB660024P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.890,
                'measurement_1_max' => 7.950,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            [
                'item_code' => 'DFB660050P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 8.250,
                'measurement_1_max' => 8.390,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            [
                'item_code' => 'DFB6602000',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB6602001',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB5902000',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB5902001',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB4805004',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB6602201',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB660240P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.890,
                'measurement_1_max' => 7.950,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            [
                'item_code' => 'DFB660221P',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
            ],
            [
                'item_code' => 'DFB480300P',
                'item_name' => null,
                'strength_min' => 0.15,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.280,
                'measurement_1_max' => 7.590,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            [
                'item_code' => 'DFB660024V',
                'item_name' => null,
                'strength_min' => 0.30,
                'measurement_1_type' => 'range',
                'measurement_1_min' => 7.890,
                'measurement_1_max' => 7.950,
                'circumference_diff_type' => 'max_limit',
                'circumference_diff_max' => 0.30,
            ],
            [
                'item_code' => 'DFB4803000',
                'item_name' => null,
                'strength_min' => 0.15,
                'measurement_1_type' => 'data_recording',
                'measurement_1_min' => null,
                'measurement_1_max' => null,
                'circumference_diff_type' => 'data_recording',
                'circumference_diff_max' => null,
                'welding_measurement_1_type' => 'not_recorded',
            ],
        ];

        foreach ($itemCodes as $itemCode) {
            $legacyItemCode = $itemCode;
            unset($legacyItemCode['welding_measurement_1_type']);

            DiaphragmItemCode::updateOrCreate(
                ['item_code' => $itemCode['item_code']],
                $legacyItemCode
            );
        }

        $diaphragmType = Schema::hasTable('welding_checksheet_types')
            ? WeldingChecksheetType::where('key', 'diaphragm')->first()
            : null;

        if ($diaphragmType && Schema::hasTable('welding_item_configs')) {
            foreach ($itemCodes as $itemCode) {
                WeldingItemConfig::updateOrCreate(
                    [
                        'checksheet_type_id' => $diaphragmType->id,
                        'item_code' => $itemCode['item_code'],
                    ],
                    [
                        'item_name' => $itemCode['item_name'],
                        'validation_rules' => [
                            'strength_min' => $itemCode['strength_min'],
                            'measurement_1_type' => $itemCode['welding_measurement_1_type'] ?? $itemCode['measurement_1_type'],
                            'measurement_1_min' => $itemCode['measurement_1_min'],
                            'measurement_1_max' => $itemCode['measurement_1_max'],
                            'circumference_diff_type' => $itemCode['circumference_diff_type'],
                            'circumference_diff_max' => $itemCode['circumference_diff_max'],
                        ],
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command->info('Diaphragm item codes seeded successfully!');
    }
}
