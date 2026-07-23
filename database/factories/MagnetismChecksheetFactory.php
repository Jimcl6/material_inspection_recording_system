<?php

namespace Database\Factories;

use App\Models\MagnetismChecksheet;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MagnetismChecksheet> */
class MagnetismChecksheetFactory extends Factory
{
    protected $model = MagnetismChecksheet::class;

    public function definition(): array
    {
        return [
            'item_code' => 'TEST-MAG-001',
            'item_name' => 'Synthetic Magnet',
            'machine_no' => 'TEST-MACHINE-02',
            'month' => 1,
            'year' => 2026,
        ];
    }
}
