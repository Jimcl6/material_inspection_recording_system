<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token_and_user_on_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'operator@example.com',
            'password' => bcrypt('secret123'),
            'status' => 'active',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'operator@example.com',
            'password' => 'secret123',
            'device_name' => 'Tablet-SM-X210',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('token'));
        $this->assertSame($user->id, $response->json('user.id'));
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'operator@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'operator@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_me_endpoint_returns_authenticated_user(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                ],
            ]);
    }

    public function test_me_endpoint_rejects_unauthenticated_request(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_logout_revokes_current_access_token(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);
        $token = $user->createToken('test-token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->postJson('/api/logout');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully.',
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}
