<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_inactive_users_receive_the_same_generic_login_failure(): void
    {
        $user = User::factory()->create(['status' => 'inactive']);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email' => trans('auth.failed')]);
    }

    public function test_suspended_users_receive_the_same_generic_login_failure(): void
    {
        $user = User::factory()->create(['status' => 'suspended']);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email' => trans('auth.failed')]);
    }

    public function test_existing_web_session_is_terminated_after_deactivation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->update(['status' => 'inactive']);

        $this->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
        $this->assertDatabaseHas('security_audit_logs', [
            'target_user_id' => $user->id,
            'action' => 'account_access_revoked',
        ]);
    }

    public function test_sanctum_token_is_rejected_and_revoked_after_deactivation(): void
    {
        $user = User::factory()->create();
        $plainTextToken = $user->createToken('synthetic-test-token')->plainTextToken;
        $user->update(['status' => 'inactive']);

        $this->withToken($plainTextToken)
            ->getJson('/api/user')
            ->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
