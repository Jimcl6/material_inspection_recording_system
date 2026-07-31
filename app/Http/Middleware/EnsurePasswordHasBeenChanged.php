<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordHasBeenChanged
{
    /**
     * Keep temporary-password users on the password-change flow until they secure the account.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs('profile.edit', 'password.update', 'logout')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'You must change your temporary password before continuing.',
            ], 403);
        }

        return redirect()->route('profile.edit')
            ->with('status', 'Please change your temporary password before continuing.');
    }
}
