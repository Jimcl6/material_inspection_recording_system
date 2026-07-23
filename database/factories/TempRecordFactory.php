<?php

namespace Database\Factories;

use App\Models\TempRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TempRecord> */
class TempRecordFactory extends Factory
{
    protected $model = TempRecord::class;

    public function definition(): array
    {
        return [
            'date' => '2026-01-04',
            'model_series' => 'TEST-SERIES',
            'solder_model' => 'TEST-SOLDER',
            'line_assigned' => 'TEST-LINE',
            'control_no' => 'TEST-TEMP-001',
            'equipment_type' => 'TEST-EQUIPMENT',
            'machine_setting_standard' => '100-120',
            'process_assigned' => 'TEST-PROCESS',
            'person_in_charge' => 'Synthetic Inspector',
            'time_am' => '08:00:00',
            'temp_am' => '110',
            'time_pm' => '13:00:00',
            'temp_pm' => '111',
            'checked_by' => 'Synthetic Checker',
            'status' => 'approved',
            'approved_at' => '2026-01-04 13:00:00',
        ];
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'submitted_at' => '2026-01-04 13:00:00',
            'approved_at' => null,
        ]);
    }
}
