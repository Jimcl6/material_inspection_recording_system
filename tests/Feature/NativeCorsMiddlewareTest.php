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

        Route::get('/_tests/native-cors', fn () => response()->json(['ok' => true]));
    }

    public function test_framework_native_middleware_handles_cors_request(): void
    {
        $response = $this->withHeader('Origin', self::ORIGIN)
            ->get('/_tests/native-cors');

        $response->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', self::ORIGIN)
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function test_framework_native_middleware_handles_preflight_request(): void
    {
        $response = $this->withHeaders([
            'Origin' => self::ORIGIN,
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, X-Requested-With',
        ])->options('/_tests/native-cors');

        $response->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', self::ORIGIN)
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }
}
