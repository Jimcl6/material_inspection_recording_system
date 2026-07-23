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

        DB::transaction(function (): void {
            $typeIds = [];

            foreach ($this->typeDefinitions() as $key => $definition) {
                $typeIds[$key] = $this->insertTypeIfMissing($key, $definition);
            }

            $this->insertCasingTankItemConfigs($typeIds['casing_tank']);
            $this->insertDiaphragmItemConfigs($typeIds['diaphragm']);
        });
    }

    public function down(): void
    {
        // Configuration rows may be referenced or customized after deployment.
    }

    private function insertTypeIfMissing(string $key, array $definition): int
    {
        $existingId = DB::table('welding_checksheet_types')->where('key', $key)->value('id');
        if ($existingId) {
            return (int) $existingId;
        }

        $now = now();

        return (int) DB::table('welding_checksheet_types')->insertGetId([
            'key' => $key,
            'name' => $definition['name'],
            'description' => $definition['description'],
            'material_fields' => json_encode($definition['material_fields'], JSON_THROW_ON_ERROR),
            'check_items' => json_encode($definition['check_items'], JSON_THROW_ON_ERROR),
            'import_config' => json_encode($definition['import_config'], JSON_THROW_ON_ERROR),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function insertCasingTankItemConfigs(int $typeId): void
    {
        $definitions = [
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

        foreach ($definitions as $itemCode => $rules) {
            $this->insertItemConfigIfMissing($typeId, $itemCode, null, $rules);
        }
    }

    private function insertDiaphragmItemConfigs(int $typeId): void
    {
        if (! Schema::hasTable('diaphragm_item_codes')) {
            return;
        }

        DB::table('diaphragm_item_codes')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($typeId): void {
                foreach ($rows as $row) {
                    $rules = [
                        'strength_min' => $row->strength_min,
                        'measurement_1_type' => $row->measurement_1_type,
                        'measurement_1_min' => $row->measurement_1_min,
                        'measurement_1_max' => $row->measurement_1_max,
                        'circumference_diff_type' => $row->circumference_diff_type,
                        'circumference_diff_max' => $row->circumference_diff_max,
                    ];

                    if ($row->item_code === 'DFB4803000') {
                        $rules['measurement_1_type'] = 'not_recorded';
                    }

                    $this->insertItemConfigIfMissing(
                        $typeId,
                        $row->item_code,
                        $row->item_name,
                        $rules
                    );
                }
            });
    }

    private function insertItemConfigIfMissing(int $typeId, string $itemCode, ?string $itemName, array $rules): void
    {
        $exists = DB::table('welding_item_configs')
            ->where('checksheet_type_id', $typeId)
            ->where('item_code', $itemCode)
            ->exists();

        if ($exists) {
            return;
        }

        $now = now();
        DB::table('welding_item_configs')->insert([
            'checksheet_type_id' => $typeId,
            'item_code' => $itemCode,
            'item_name' => $itemName,
            'validation_rules' => json_encode($rules, JSON_THROW_ON_ERROR),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function typeDefinitions(): array
    {
        return [
            'diaphragm' => [
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
            ],
            'casing_tank' => [
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
};
