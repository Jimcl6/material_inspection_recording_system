<?php

namespace Database\Factories;

use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<UserPermission> */
class UserPermissionFactory extends Factory
{
    protected $model = UserPermission::class;

    public function definition(): array
    {
        $suffix = $this->faker->unique()->numerify('####');

        return [
            'name' => 'View Testing Records '.$suffix,
            'slug' => 'testing.view-'.$suffix,
            'description' => 'Synthetic permission for automated tests',
            'module' => 'testing',
            'action' => 'view',
        ];
    }
}
