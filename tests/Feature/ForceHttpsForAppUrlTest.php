<?php

namespace Tests\Feature;

use Tests\TestCase;

class ForceHttpsForAppUrlTest extends TestCase
{
    public function test_public_app_url_redirects_plain_http_to_https(): void
    {
        config(['app.url' => 'https://mirs.hiblow.online']);

        $response = $this
            ->withServerVariables([
                'HTTP_X_FORWARDED_PROTO' => 'http',
                'HTTPS' => 'off',
            ])
            ->get('http://mirs.hiblow.online/login');

        $response->assertRedirect('https://mirs.hiblow.online/login');
        $this->assertSame(301, $response->getStatusCode());
    }

    public function test_non_public_hosts_are_not_redirected(): void
    {
        config(['app.url' => 'https://mirs.hiblow.online']);

        $response = $this
            ->withServerVariables([
                'HTTP_X_FORWARDED_PROTO' => 'http',
                'HTTPS' => 'off',
            ])
            ->get('http://192.168.2.137/login');

        $response->assertOk();
    }

    public function test_forwarded_https_requests_are_not_redirected(): void
    {
        config(['app.url' => 'https://mirs.hiblow.online']);

        $response = $this
            ->withServerVariables([
                'HTTP_X_FORWARDED_PROTO' => 'https',
                'HTTPS' => 'off',
            ])
            ->get('http://mirs.hiblow.online/login');

        $response->assertOk();
    }
}
