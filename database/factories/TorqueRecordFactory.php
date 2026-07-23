<?php

namespace Database\Factories;

use App\Models\TorqueRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TorqueRecord> */
class TorqueRecordFactory extends Factory
{
    protected $model = TorqueRecord::class;

    public function definition(): array
    {
        return [
            'date' => '2026-01-05',
            'model_series' => 'TEST-SERIES',
            'driver_model' => 'TEST-DRIVER',
            'driver_type' => 'TEST-TYPE',
            'line_assigned' => 'TEST-LINE',
            'control_no' => 'TEST-TORQUE-001',
            'screw_type' => 'TEST-SCREW',
            'process_assigned' => 'TEST-PROCESS',
            'person_in_charge' => 'Synthetic Inspector',
            'time_am' => '08:00:00',
            'time_pm' => '13:00:00',
            'checked_by' => 'Synthetic Checker',
            'status' => 'approved',
            'approved_at' => '2026-01-05 13:00:00',
        ];
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'submitted_at' => '2026-01-05 13:00:00',
            'approved_at' => null,
        ]);
    }
}
