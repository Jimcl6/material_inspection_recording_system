<?php

namespace Database\Factories;

use App\Models\MagnetismBatch;
use App\Models\MagnetismChecksheet;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MagnetismBatch> */
class MagnetismBatchFactory extends Factory
{
    protected $model = MagnetismBatch::class;

    public function definition(): array
    {
        return [
            'checksheet_id' => MagnetismChecksheet::factory(),
            'production_date' => '2026-01-06',
            'letter_code' => 'A',
            'material_lot_number' => 'TEST-MAG-LOT-001',
            'qr_code' => 'TEST-NON-PRODUCTION-QR',
            'produce_qty' => 10,
            'job_number' => 'TEST-JOB-001',
            'remarks' => 'Synthetic magnetism batch',
        ];
    }
}
