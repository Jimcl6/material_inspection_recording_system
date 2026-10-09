<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Cookie;
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

    public function test_emergency_ip_route_uses_host_only_cookies_that_follow_the_request_scheme(): void
    {
        config([
            'app.url' => 'https://mirs.internal.example',
            'app.asset_url' => null,
            'session.domain' => null,
            'session.secure' => null,
        ]);

        $httpResponse = $this->get('http://192.0.2.25/login');
        $httpResponse->assertOk();

        $httpCookies = collect($httpResponse->headers->getCookies())
            ->keyBy(static fn (Cookie $cookie): string => $cookie->getName());

        $this->assertFalse($httpCookies->get(config('session.cookie'))->isSecure());
        $this->assertFalse($httpCookies->get('XSRF-TOKEN')->isSecure());
        $this->assertNull($httpCookies->get(config('session.cookie'))->getDomain());
        $this->assertNull($httpCookies->get('XSRF-TOKEN')->getDomain());

        $httpsResponse = $this->get('https://mirs.internal.example/login');
        $httpsResponse->assertOk();

        $httpsCookies = collect($httpsResponse->headers->getCookies())
            ->keyBy(static fn (Cookie $cookie): string => $cookie->getName());

        $this->assertTrue($httpsCookies->get(config('session.cookie'))->isSecure());
        $this->assertTrue($httpsCookies->get('XSRF-TOKEN')->isSecure());
    }

    public function test_emergency_ip_route_generates_assets_for_the_current_origin(): void
    {
        config([
            'app.url' => 'https://mirs.internal.example',
            'app.asset_url' => null,
        ]);

        Route::get('/_tests/emergency-asset', static fn (): string => asset('build/assets/emergency-route.js')
        );

        $response = $this->get('http://192.0.2.25/_tests/emergency-asset');

        $response->assertOk();
        $response->assertSee(
            'http://192.0.2.25/build/assets/emergency-route.js',
            false
        );
        $response->assertDontSee('https://mirs.internal.example', false);
    }
}
