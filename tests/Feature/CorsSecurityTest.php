<?php

namespace Tests\Feature;

use App\Support\CorsConfiguration;
use Tests\TestCase;

class CorsSecurityTest extends TestCase
{
    public function test_named_internal_origin_is_allowed_with_credentials(): void
    {
        config()->set('cors.allowed_origins', ['https://mirs.internal.example']);
        config()->set('cors.supports_credentials', true);

        $this->call('OPTIONS', '/api/material-types', [], [], [], [
            'HTTP_ORIGIN' => 'https://mirs.internal.example',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ])
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://mirs.internal.example')
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function test_unknown_origin_is_not_granted_cors_headers(): void
    {
        config()->set('cors.allowed_origins', ['https://mirs.internal.example']);
        config()->set('cors.supports_credentials', true);

        $response = $this->call('OPTIONS', '/api/material-types', [], [], [], [
            'HTTP_ORIGIN' => 'https://unknown.example',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'GET',
        ]);

        $response->assertForbidden();
        $this->assertFalse($response->headers->has('Access-Control-Allow-Origin'));
        $this->assertFalse($response->headers->has('Access-Control-Allow-Credentials'));
    }

    public function test_origin_parser_rejects_wildcards_and_disables_credentials(): void
    {
        $origins = CorsConfiguration::allowedOrigins(
            'https://mirs.internal.example/, *, https://mirs.internal.example, https://user@internal.example/path'
        );

        $this->assertSame(['https://mirs.internal.example'], $origins);
        $this->assertTrue(CorsConfiguration::supportsCredentials($origins));
        $this->assertSame([], CorsConfiguration::allowedOrigins('*'));
        $this->assertFalse(CorsConfiguration::supportsCredentials([]));
    }
}
