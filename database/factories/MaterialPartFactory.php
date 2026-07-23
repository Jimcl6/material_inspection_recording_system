<?php

namespace Database\Factories;

use App\Models\MaterialPart;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MaterialPart> */
class MaterialPartFactory extends Factory
{
    protected $model = MaterialPart::class;

    public function definition(): array
    {
        return [
            'material_type' => 'ALARM',
            'date' => '2026-01-07',
            'item_block_code' => 'TEST-MATERIAL-001',
            'letter_code' => 'A',
            'main_lot_number' => 'TEST-MAIN-LOT-001',
            'sub_lot_numbers' => ['sub_lots' => ['TEST-SUB-LOT-001']],
            'produced_qty' => 10,
            'operator' => 'Synthetic Operator',
            'job_number' => 'TEST-JOB-002',
        ];
    }
}
