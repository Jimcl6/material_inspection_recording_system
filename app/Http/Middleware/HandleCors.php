<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Middleware\HandleCors as FrameworkHandleCors;

class HandleCors extends FrameworkHandleCors
{
    /**
     * Prevent the CORS service's single-origin optimization from emitting
     * credentialed headers for an unlisted Origin.
     */
    public function handle($request, Closure $next)
    {
        if ($this->hasMatchingPath($request) && $this->cors->isCorsRequest($request)) {
            $this->cors->setOptions($this->container['config']->get('cors', []));

            if (! $this->cors->isOriginAllowed($request)) {
                if ($this->cors->isPreflightRequest($request)) {
                    return response('', 403);
                }

                return $next($request);
            }
        }

        return parent::handle($request, $next);
    }
}
