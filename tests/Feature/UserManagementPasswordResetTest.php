<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reset_a_users_password_from_user_management(): void
    {
        $adminRole = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Administrator',
            ]
        );

        $userRole = Role::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'description' => 'Standard user account',
            ]
        );

        $admin = User::factory()->create([
            'role_id' => $adminRole->id,
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'role_id' => $userRole->id,
            'status' => 'active',
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($admin)->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'role_id' => $userRole->id,
            'department_id' => null,
            'position_id' => null,
            'contact_number' => $user->contact_number,
            'status' => 'active',
            'employment_status' => 'regular',
            'hire_date' => null,
            'contract_end_date' => null,
            'password' => 'temporary-password',
            'password_confirmation' => 'temporary-password',
        ]);

        $response
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User updated successfully.');

        $this->assertTrue(Hash::check('temporary-password', $user->refresh()->password));
    }
}
