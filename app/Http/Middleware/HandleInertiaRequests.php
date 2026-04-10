<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => function () use ($request) {
                    $user = $request->user();
                    return $user ? $user->load(['role', 'position']) : null;
                },
                'permissions' => function () use ($request) {
                    return $this->getUserPermissions($request->user());
                },
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
            'csrf_token' => csrf_token(),
        ]);
    }

    /**
     * Get flattened permissions array for the user.
     * Returns an object like { 'annealing.view': true, 'annealing.update': false, ... }
     */
    protected function getUserPermissions($user): array
    {
        if (!$user) {
            return [];
        }

        // Super admin has all permissions
        if ($user->isSuperAdmin()) {
            return ['*' => true];
        }

        $permissions = [];

        // Get permissions from role
        if ($user->role) {
            $user->role->load('permissions');
            foreach ($user->role->permissions as $permission) {
                $key = "{$permission->module}.{$permission->action}";
                $permissions[$key] = true;
            }
        }

        // Get permissions from position (additive)
        if ($user->position) {
            $user->position->load('permissions');
            foreach ($user->position->permissions as $permission) {
                $key = "{$permission->module}.{$permission->action}";
                $permissions[$key] = true;
            }
        }

        return $permissions;
    }
}
