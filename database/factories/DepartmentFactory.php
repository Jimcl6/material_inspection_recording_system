<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Department> */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        $suffix = $this->faker->unique()->numerify('####');

        return [
            'name' => 'Testing Department '.$suffix,
            'code' => 'T'.$suffix,
            'description' => 'Synthetic department for automated tests',
            'is_active' => true,
        ];
    }
}
