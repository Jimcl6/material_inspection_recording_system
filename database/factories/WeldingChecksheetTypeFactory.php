<?php

namespace Database\Factories;

use App\Models\WeldingChecksheetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WeldingChecksheetType> */
class WeldingChecksheetTypeFactory extends Factory
{
    protected $model = WeldingChecksheetType::class;

    public function definition(): array
    {
        return [
            'key' => 'testing-welding',
            'name' => 'Synthetic Welding Checksheet',
            'description' => 'Synthetic checksheet type for automated tests',
            'material_fields' => [],
            'check_items' => [
                ['key' => 'appearance', 'label' => 'Appearance'],
            ],
            'import_config' => [],
            'is_active' => true,
        ];
    }
}
