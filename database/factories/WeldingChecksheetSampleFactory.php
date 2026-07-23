<?php

namespace Database\Factories;

use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetSample;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WeldingChecksheetSample> */
class WeldingChecksheetSampleFactory extends Factory
{
    protected $model = WeldingChecksheetSample::class;

    public function definition(): array
    {
        return [
            'checksheet_id' => WeldingChecksheet::factory(),
            'check_item_key' => 'appearance',
            'check_item_label' => 'Appearance',
            'requirement_text' => 'No visible defect',
            'sample_values' => ['PASS', 'PASS', 'PASS'],
            'sort_order' => 1,
        ];
    }
}
