<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Check if the authenticated user has the required permission.
     * Permission is checked against both role AND position permissions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $module  The module name (e.g., 'annealing', 'users')
     * @param  string  $action  The action name (e.g., 'view', 'create', 'update', 'delete')
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $module, string $action)
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // Super admin has all permissions
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if user has the required permission (checks both role and position)
        if (!$user->hasPermission($module, $action)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to perform this action.',
                    'required_permission' => "{$module}.{$action}",
                ], 403);
            }

            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
