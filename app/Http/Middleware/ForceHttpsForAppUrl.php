<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttpsForAppUrl
{
    /**
     * Redirect the configured public HTTPS host away from plain HTTP.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $appUrl = (string) config('app.url');
        $appHost = parse_url($appUrl, PHP_URL_HOST);
        $appScheme = parse_url($appUrl, PHP_URL_SCHEME);

        if (
            $appScheme === 'https'
            && is_string($appHost)
            && strcasecmp($request->getHost(), $appHost) === 0
            && ! $request->secure()
        ) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
