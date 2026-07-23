<?php

namespace Database\Factories;

use App\Models\AnnealingCheck;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AnnealingCheck> */
class AnnealingCheckFactory extends Factory
{
    protected $model = AnnealingCheck::class;

    public function definition(): array
    {
        return [
            'item_code' => 'TEST-ANNEAL-001',
            'receiving_date' => '2026-01-02',
            'supplier_lot_number' => 'TEST-LOT-001',
            'quantity' => 10,
            'annealing_date' => '2026-01-03',
            'machine_number' => 'TEST-MACHINE-01',
            'temperature_setting' => 120.00,
            'annealing_time' => '01:00:00',
            'damper_setting' => 'TEST-SETTING',
            'time_in' => '08:00:00',
            'time_out' => '09:00:00',
            'status' => 'approved',
            'approved_at' => '2026-01-03 09:00:00',
            'pic_id' => User::factory(),
            'created_by' => User::factory(),
            'remarks' => 'Synthetic annealing record',
        ];
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'submitted_at' => '2026-01-03 09:00:00',
            'approved_at' => null,
        ]);
    }
}
