<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Position> */
class PositionFactory extends Factory
{
    protected $model = Position::class;

    public function definition(): array
    {
        $suffix = $this->faker->unique()->numerify('####');

        return [
            'name' => 'Testing Position '.$suffix,
            'code' => 'P'.$suffix,
            'description' => 'Synthetic position for automated tests',
            'department_id' => Department::factory(),
            'is_active' => true,
        ];
    }
}
