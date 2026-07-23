<?php

namespace Tests\Feature;

use Tests\TestCase;

class ForceHttpsForAppUrlTest extends TestCase
{
    public function test_public_app_url_redirects_plain_http_to_https(): void
    {
        config(['app.url' => 'https://mirs.internal.example']);

        $response = $this
            ->withServerVariables([
                'HTTP_X_FORWARDED_PROTO' => 'http',
                'HTTPS' => 'off',
            ])
            ->get('http://mirs.internal.example/login');

        $response->assertRedirect('https://mirs.internal.example/login');
        $this->assertSame(301, $response->getStatusCode());
    }

    public function test_non_public_hosts_are_not_redirected(): void
    {
        config(['app.url' => 'https://mirs.internal.example']);

        $response = $this
            ->withServerVariables([
                'HTTP_X_FORWARDED_PROTO' => 'http',
                'HTTPS' => 'off',
            ])
            ->get('http://192.0.2.25/login');

        $response->assertOk();
    }

    public function test_forwarded_https_requests_are_not_redirected(): void
    {
        config([
            'app.url' => 'https://mirs.internal.example',
            'trusted_proxies.proxies' => ['127.0.0.1'],
        ]);

        $response = $this
            ->withServerVariables([
                'HTTP_X_FORWARDED_PROTO' => 'https',
                'HTTPS' => 'off',
            ])
            ->get('http://mirs.internal.example/login');

        $response->assertOk();
    }
}
