<?php

namespace Tests\Feature;

use App\Http\Requests\StoreWeldingChecksheetRequest;
use App\Models\WeldingChecksheetType;
use App\Models\WeldingItemConfig;
use Database\Seeders\DiaphragmItemCodeSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class WeldingChecksheetValidationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_non_formula_diaphragm_item_codes_validate_appearance_and_strength_only(): void
    {
        $this->seed(DiaphragmItemCodeSeeder::class);

        $type = WeldingChecksheetType::where('key', 'diaphragm')->firstOrFail();
        $config = WeldingItemConfig::where('checksheet_type_id', $type->id)
            ->where('item_code', 'DFB660220P')
            ->firstOrFail();

        $validator = $this->validatorFor($this->payload($type, $config, [
            'strength' => ['0.20', '', '', '', ''],
            'appearance' => ['F', '', '', '', ''],
            'measurement_1' => ['7.000', '', '', '', ''],
            'measurement_2' => ['10.000', '', '', '', ''],
        ]));

        $this->assertFalse($validator->passes());
        $messages = $validator->errors()->get('samples');
        $this->assertStringContainsString('Appearance sample 1 must be P or /.', implode(' ', $messages));
        $this->assertStringContainsString('Strength must be >= 0.3 kN. Sample 1 got 0.20.', implode(' ', $messages));
        $this->assertStringNotContainsString('Circumference difference', implode(' ', $messages));

        $validWithLargeMeasurementDifference = $this->validatorFor($this->payload($type, $config, [
            'strength' => ['0.30', '', '', '', ''],
            'appearance' => ['P', '', '', '', ''],
            'measurement_1' => ['7.000', '', '', '', ''],
            'measurement_2' => ['10.000', '', '', '', ''],
        ]));

        $this->assertTrue($validWithLargeMeasurementDifference->passes(), implode(' ', $validWithLargeMeasurementDifference->errors()->all()));
    }

    public function test_formula_diaphragm_item_codes_still_validate_measurement_difference(): void
    {
        $this->seed(DiaphragmItemCodeSeeder::class);

        $type = WeldingChecksheetType::where('key', 'diaphragm')->firstOrFail();
        $config = WeldingItemConfig::where('checksheet_type_id', $type->id)
            ->where('item_code', 'DFB660023P')
            ->firstOrFail();

        $validator = $this->validatorFor($this->payload($type, $config, [
            'strength' => ['0.30', '', '', '', ''],
            'appearance' => ['P', '', '', '', ''],
            'measurement_1' => ['7.900', '', '', '', ''],
            'measurement_2' => ['8.600', '', '', '', ''],
        ]));

        $this->assertFalse($validator->passes());
        $this->assertStringContainsString(
            'Circumference difference for measurement_2 sample 1 must be <= 0.3.',
            implode(' ', $validator->errors()->get('samples'))
        );
    }

    private function validatorFor(array $payload): \Illuminate\Validation\Validator
    {
        $request = StoreWeldingChecksheetRequest::create('/welding-checksheets', 'POST', $payload);
        $request->setContainer($this->app);
        $request->setRedirector($this->app->make('redirect'));

        $validator = Validator::make($request->all(), $request->rules());
        $request->withValidator($validator);

        return $validator;
    }

    private function payload(WeldingChecksheetType $type, WeldingItemConfig $config, array $overrides): array
    {
        return [
            'checksheet_type_id' => $type->id,
            'item_config_id' => $config->id,
            'item_code' => $config->item_code,
            'production_date' => '2026-06-11',
            'samples' => collect($type->check_items)->map(function (array $item) use ($overrides) {
                $key = $item['key'];

                return [
                    'check_item_key' => $key,
                    'check_item_label' => $item['label'] ?? $key,
                    'requirement_text' => $item['requirement'] ?? $item['requirement_text'] ?? null,
                    'sort_order' => 0,
                    'sample_values' => $overrides[$key] ?? ['', '', '', '', ''],
                ];
            })->values()->all(),
        ];
    }
}
