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
        $this->assertTrue($user->must_change_password);
    }

    public function test_user_with_temporary_password_must_change_password_before_continuing(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $loginResponse
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Please change your temporary password before continuing.');

        $this->get(route('dashboard'))
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Please change your temporary password before continuing.');
    }

    public function test_password_update_clears_temporary_password_requirement(): void
    {
        $user = User::factory()->create([
            'must_change_password' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'Password updated successfully.');

        $this->assertFalse($user->refresh()->must_change_password);
        $this->get(route('dashboard'))->assertOk();
    }
}
