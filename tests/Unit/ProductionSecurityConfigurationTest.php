<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProductionSecurityConfigurationTest extends TestCase
{
    public function test_cors_and_cookie_defaults_are_restrictive(): void
    {
        $this->assertSame(['api/*', 'sanctum/csrf-cookie'], config('cors.paths'));
        $this->assertNotContains('*', config('cors.allowed_methods'));
        $this->assertNotContains('*', config('cors.allowed_headers'));
        $this->assertTrue((bool) config('session.http_only'));
        $this->assertContains(config('session.same_site'), ['lax', 'strict']);
    }

    public function test_wildcard_proxy_trust_is_not_enabled(): void
    {
        $this->assertNotContains('*', config('trusted_proxies.proxies'));
    }

    public function test_production_template_and_nginx_keep_private_files_outside_the_web_root(): void
    {
        $environmentTemplate = file_get_contents(base_path('.env.docker.example'));
        $nginx = file_get_contents(base_path('docker/nginx/default.conf'));

        $this->assertIsString($environmentTemplate);
        $this->assertIsString($nginx);
        $this->assertStringContainsString('APP_DEBUG=false', $environmentTemplate);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $environmentTemplate);
        $this->assertStringContainsString('SESSION_HTTP_ONLY=true', $environmentTemplate);
        $this->assertStringContainsString('root /var/www/html/public;', $nginx);
        $this->assertStringContainsString('location = /index.php', $nginx);
        $this->assertStringContainsString('storage/(?:framework|logs)', $nginx);
    }

    public function test_non_debug_error_response_does_not_expose_exception_details(): void
    {
        config()->set('app.debug', false);
        Route::get('/_tests/production-error', static function (): void {
            throw new \RuntimeException('PHASE3_INTERNAL_EXCEPTION_SENTINEL');
        });

        $response = $this->get('/_tests/production-error');

        $response->assertStatus(500);
        $response->assertDontSee('PHASE3_INTERNAL_EXCEPTION_SENTINEL');
        $response->assertDontSee(base_path());
        $response->assertDontSee('Stack trace');
    }
}
