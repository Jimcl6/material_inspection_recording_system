<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class RegistrationDisabledTest extends TestCase
{
    public function test_registration_routes_are_not_available(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', [
            'name' => 'Synthetic User',
            'email' => 'synthetic.user@example.test',
            'password' => 'not-a-real-password',
            'password_confirmation' => 'not-a-real-password',
        ])->assertNotFound();
    }

    public function test_public_welcome_components_do_not_offer_registration(): void
    {
        $vueWelcome = file_get_contents(resource_path('js/Pages/Welcome.vue'));
        $bladeWelcome = file_get_contents(resource_path('views/welcome.blade.php'));

        $this->assertIsString($vueWelcome);
        $this->assertIsString($bladeWelcome);
        $this->assertStringNotContainsString("route('register')", $vueWelcome);
        $this->assertStringNotContainsString("route('register')", $bladeWelcome);
    }
}
