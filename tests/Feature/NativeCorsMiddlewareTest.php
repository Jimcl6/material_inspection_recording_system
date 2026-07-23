<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class NativeCorsMiddlewareTest extends TestCase
{
    private const ORIGIN = 'https://internal-client.example';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('cors.allowed_origins', [self::ORIGIN]);
        config()->set('cors.supports_credentials', true);

        Route::get('/api/_tests/native-cors', fn () => response()->json(['ok' => true]));
    }

    public function test_framework_native_middleware_handles_cors_request(): void
    {
        $response = $this->withHeader('Origin', self::ORIGIN)
            ->get('/api/_tests/native-cors');

        $response->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', self::ORIGIN)
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function test_framework_native_middleware_handles_preflight_request(): void
    {
        $response = $this->withHeaders([
            'Origin' => self::ORIGIN,
            'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'Content-Type, X-Requested-With',
        ])->options('/api/_tests/native-cors');

        $response->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', self::ORIGIN)
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }
}
