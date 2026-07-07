<?php

namespace Database\Seeders;

use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class WeldingChecksheetTypeSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('welding_checksheet_types') || !Schema::hasTable('welding_item_configs')) {
            return;
        }

        $diaphragmType = WeldingChecksheetType::updateOrCreate(
            ['key' => 'diaphragm'],
            [
                'name' => 'Diaphragm Welding Checksheet',
                'description' => 'Legacy diaphragm welding checksheet format.',
                'material_fields' => [
                    ['key' => 'lasermark_lot_number', 'label' => 'Lasermark Lot Number', 'type' => 'text'],
                    ['key' => 'df_rubber_lot', 'label' => 'DF Rubber Lot', 'type' => 'text'],
                    ['key' => 'center_plate_a_lot', 'label' => 'Center Plate A Lot', 'type' => 'text'],
                    ['key' => 'center_plate_b_lot', 'label' => 'Center Plate B Lot', 'type' => 'text'],
                ],
                'check_items' => $this->diaphragmCheckItems(),
                'import_config' => [
                    'data_start_row' => 10,
                    'record_span' => 10,
                    'sample_columns' => ['V', 'W', 'X', 'Y', 'Z'],
                    'format' => 'legacy_diaphragm',
                ],
                'is_active' => true,
            ]
        );

        $casingTankType = WeldingChecksheetType::updateOrCreate(
            ['key' => 'casing_tank'],
            [
                'name' => 'Casing-Tank Welding Checksheet',
                'description' => 'Casing-Tank welding checksheet format from reference Excel files.',
                'material_fields' => [
                    ['key' => 'tank', 'label' => 'Tank', 'type' => 'text'],
                    ['key' => 'cd_partition', 'label' => 'CD Partition', 'type' => 'text'],
                    ['key' => 'vcr', 'label' => 'VCR', 'type' => 'text'],
                ],
                'check_items' => $this->casingTankCheckItems(),
                'import_config' => [
                    'data_start_row' => 10,
                    'record_span' => 5,
                    'material_columns' => ['tank' => 'K', 'cd_partition' => 'L', 'vcr' => 'M'],
                    'sample_columns' => ['S', 'T', 'U', 'V', 'W'],
                    'format' => 'casing_tank',
                ],
                'is_active' => true,
            ]
        );

        foreach ($this->casingTankItemConfigs() as $itemCode => $rules) {
            WeldingItemConfig::updateOrCreate(
                [
                    'checksheet_type_id' => $casingTankType->id,
                    'item_code' => $itemCode,
                ],
                [
                    'validation_rules' => $rules,
                    'is_active' => true,
                ]
            );
        }

        if (Schema::hasTable('diaphragm_item_codes')) {
            $this->call(DiaphragmItemCodeSeeder::class);
        }
    }

    private function casingTankItemConfigs(): array
    {
        return [
            'CSB29046P3' => [
                'collapse_depth_min' => 0.37,
                'collapse_time_min' => 1.3,
                'collapse_time_max' => 1.7,
            ],
            'CSB29052P1' => [
                'collapse_depth_min' => 0.37,
                'collapse_time_min' => 1.3,
                'collapse_time_max' => 1.7,
            ],
            'TKB4000100' => [
                'collapse_depth_min' => null,
                'collapse_time_min' => null,
                'collapse_time_max' => null,
            ],
        ];
    }

    private function diaphragmCheckItems(): array
    {
        return [
            ['key' => 'collapse_depth', 'label' => 'COLLAPSE - DEPTH (mm)', 'requirement_text' => null, 'sort_order' => 1],
            ['key' => 'collapse_time', 'label' => 'COLLAPSE - TIME (sec.)', 'requirement_text' => null, 'sort_order' => 2],
            ['key' => 'strength', 'label' => 'STRENGTH (kN)', 'requirement_text' => 'Minimum configured per item code', 'sort_order' => 3],
            ['key' => 'appearance', 'label' => 'APPEARANCE', 'requirement_text' => 'P or /', 'sort_order' => 4],
            ['key' => 'welding_condition', 'label' => 'WELDING CONDITION', 'requirement_text' => null, 'sort_order' => 5],
            ['key' => 'measurement_1', 'label' => 'MEASUREMENT 1 - CENTER', 'requirement_text' => 'Configured per item code', 'sort_order' => 6],
            ['key' => 'measurement_2', 'label' => 'MEASUREMENT 2 - UP', 'requirement_text' => 'Configured per item code', 'sort_order' => 7],
            ['key' => 'measurement_3', 'label' => 'MEASUREMENT 3 - LOW', 'requirement_text' => 'Configured per item code', 'sort_order' => 8],
            ['key' => 'measurement_4', 'label' => 'MEASUREMENT 4 - LEFT', 'requirement_text' => 'Configured per item code', 'sort_order' => 9],
            ['key' => 'measurement_5', 'label' => 'MEASUREMENT 5 - RIGHT', 'requirement_text' => 'Configured per item code', 'sort_order' => 10],
        ];
    }

    private function casingTankCheckItems(): array
    {
        return [
            ['key' => 'collapse_depth', 'label' => 'COLLAPSE - DEPTH (mm)', 'requirement_text' => '>= 0.37 mm when configured', 'sort_order' => 1],
            ['key' => 'collapse_time', 'label' => 'COLLAPSE - TIME (sec.)', 'requirement_text' => '1.3 - 1.7 sec when configured', 'sort_order' => 2],
            ['key' => 'airleak', 'label' => 'AIRLEAK', 'requirement_text' => null, 'sort_order' => 3],
            ['key' => 'appearance', 'label' => 'APPEARANCE', 'requirement_text' => 'P or /', 'sort_order' => 4],
            ['key' => 'welding_condition', 'label' => 'WELDING CONDITION', 'requirement_text' => null, 'sort_order' => 5],
        ];
    }
}
