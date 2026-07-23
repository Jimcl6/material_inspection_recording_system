<?php

namespace Database\Factories;

use App\Models\TorqueReading;
use App\Models\TorqueRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TorqueReading> */
class TorqueReadingFactory extends Factory
{
    protected $model = TorqueReading::class;

    public function definition(): array
    {
        return [
            'torque_record_id' => TorqueRecord::factory(),
            'period' => 'AM',
            'reading_no' => 1,
            'torque_value' => 1.25,
        ];
    }
}
