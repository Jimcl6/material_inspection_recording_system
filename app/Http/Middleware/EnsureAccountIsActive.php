<?php

namespace App\Http\Middleware;

use App\Services\AccountAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function __construct(private AccountAccessService $accountAccess)
    {
    }

    /**
     * Reject an authenticated request as soon as its account is no longer active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isActive()) {
            return $next($request);
        }

        $this->accountAccess->revoke($user, 'authenticated_request_rejected');

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->route('login')
            ->with('status', 'Your session could not be authenticated. Please sign in again.');
    }
}
