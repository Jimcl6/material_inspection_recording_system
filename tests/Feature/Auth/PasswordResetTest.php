<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_help_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response
            ->assertStatus(200)
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/ForgotPassword')
                ->where('status', 'Password reset emails are not available. Please contact an administrator to reset your password.')
            );
    }

    public function test_password_help_request_does_not_send_reset_notification(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', ['email' => 'operator@example.com']);

        $response
            ->assertRedirect()
            ->assertSessionHas('status', 'Password reset emails are not available. Please contact an administrator to reset your password.');
        Notification::assertNothingSent();
    }

    public function test_email_token_reset_routes_are_disabled(): void
    {
        $this->assertFalse(Route::has('password.reset'));
        $this->assertFalse(Route::has('password.store'));
        $this->get('/reset-password/fake-token')->assertNotFound();
        $this->post('/reset-password')->assertNotFound();
    }
}
