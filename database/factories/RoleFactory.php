<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Role> */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $suffix = $this->faker->unique()->numerify('####');

        return [
            'name' => 'Testing Role '.$suffix,
            'slug' => 'testing-role-'.$suffix,
            'description' => 'Synthetic role for automated tests',
            'is_system' => false,
            'is_active' => true,
        ];
    }
}
