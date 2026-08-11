<?php

namespace Tests\Feature;

use App\Http\Controllers\WeldingChecksheetController;
use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use Database\Seeders\DiaphragmItemCodeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WeldingItemCodeRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_typed_diaphragm_item_code_is_registered_for_future_dropdowns(): void
    {
        $this->seed(DiaphragmItemCodeSeeder::class);

        $type = WeldingChecksheetType::where('key', 'diaphragm')->firstOrFail();
        $prepared = $this->controller()->prepareForTest([
            'checksheet_type_id' => $type->id,
            'item_config_id' => null,
            'item_code' => '  CUSTOM-DFB-001  ',
            'item_name' => '  Custom Diaphragm  ',
        ]);

        $config = WeldingItemConfig::where('checksheet_type_id', $type->id)
            ->where('item_code', 'CUSTOM-DFB-001')
            ->firstOrFail();

        $this->assertTrue($config->is_active);
        $this->assertSame($config->id, $prepared['item_config_id']);
        $this->assertSame('CUSTOM-DFB-001', $prepared['item_code']);
        $this->assertSame('Custom Diaphragm', $prepared['item_name']);
        $this->assertSame([
            'strength_min' => 0.30,
            'measurement_1_type' => 'data_recording',
            'measurement_1_min' => null,
            'measurement_1_max' => null,
            'circumference_diff_type' => 'data_recording',
            'circumference_diff_max' => null,
        ], $config->validation_rules);
    }

    public function test_typed_item_code_reactivates_existing_hidden_config(): void
    {
        $this->seed(DiaphragmItemCodeSeeder::class);

        $type = WeldingChecksheetType::where('key', 'diaphragm')->firstOrFail();
        $config = WeldingItemConfig::create([
            'checksheet_type_id' => $type->id,
            'item_code' => 'HIDDEN-DFB-001',
            'item_name' => null,
            'validation_rules' => null,
            'is_active' => false,
        ]);

        $prepared = $this->controller()->prepareForTest([
            'checksheet_type_id' => $type->id,
            'item_config_id' => null,
            'item_code' => 'HIDDEN-DFB-001',
            'item_name' => 'Visible Diaphragm',
        ]);

        $config->refresh();

        $this->assertTrue($config->is_active);
        $this->assertSame('Visible Diaphragm', $config->item_name);
        $this->assertSame($config->id, $prepared['item_config_id']);
    }

    private function controller(): object
    {
        return new class extends WeldingChecksheetController
        {
            public function prepareForTest(array $data): array
            {
                return $this->prepareChecksheetData($data);
            }
        };
    }
}
