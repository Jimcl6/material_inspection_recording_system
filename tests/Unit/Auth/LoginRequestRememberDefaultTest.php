<?php

namespace Tests\Unit\Auth;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginRequestRememberDefaultTest extends TestCase
{
    public function test_authentication_uses_persistent_remember_cookie_by_default(): void
    {
        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'operator@example.com',
            'password' => 'password',
        ]);
        $request->setContainer($this->app);

        RateLimiter::shouldReceive('tooManyAttempts')
            ->once()
            ->with($request->throttleKey(), 5)
            ->andReturn(false);
        Auth::shouldReceive('attempt')
            ->once()
            ->with([
                'email' => 'operator@example.com',
                'password' => 'password',
                'status' => 'active',
            ], true)
            ->andReturn(true);
        RateLimiter::shouldReceive('clear')
            ->once()
            ->with($request->throttleKey());

        $request->authenticate();
    }
}
