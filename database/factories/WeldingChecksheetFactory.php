<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WeldingChecksheet> */
class WeldingChecksheetFactory extends Factory
{
    protected $model = WeldingChecksheet::class;

    public function definition(): array
    {
        return [
            'checksheet_type_id' => WeldingChecksheetType::factory(),
            'item_name' => 'Synthetic Welding Part',
            'item_code' => 'TEST-WELD-001',
            'month_year' => 'January 2026',
            'production_date' => '2026-01-08',
            'machine_no' => 'TEST-MACHINE-03',
            'letter_code' => 'A',
            'prod_qty' => 10,
            'job_number' => 'TEST-JOB-003',
            'operator_id' => User::factory(),
            'status' => 'approved',
            'approved_at' => '2026-01-08 13:00:00',
            'created_by' => User::factory(),
        ];
    }

    public function pendingApproval(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'submitted_at' => '2026-01-08 13:00:00',
            'approved_at' => null,
        ]);
    }
}
