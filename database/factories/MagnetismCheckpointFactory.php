<?php

namespace Database\Factories;

use App\Models\MagnetismCheckpoint;
use App\Models\MagnetismChecksheet;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MagnetismCheckpoint> */
class MagnetismCheckpointFactory extends Factory
{
    protected $model = MagnetismCheckpoint::class;

    public function definition(): array
    {
        return [
            'checksheet_id' => MagnetismChecksheet::factory(),
            'production_date' => '2026-01-06',
            'checkpoint_number' => 1,
            'sample1_first' => 130.0,
            'sample2_first' => 131.0,
            'sample3_first' => 132.0,
            'sample4_first' => 133.0,
            'sample5_first' => 134.0,
            'operator_first' => 'Synthetic Operator',
            'judgment_first' => 'PASS',
            'checked_by' => 'Synthetic Checker',
        ];
    }
}
