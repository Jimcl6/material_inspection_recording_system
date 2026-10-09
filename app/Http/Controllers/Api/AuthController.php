<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Authenticate mobile/tablet user and issue Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $identifier = trim($validated['email']);
        $password = $validated['password'];

        $user = User::query()
            ->with(['role', 'department', 'position'])
            ->where('email', $identifier)
            ->orWhere('employee_id', $identifier)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if (! $user->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently inactive. Please contact an administrator.',
            ], 403);
        }

        // Update login timestamp
        $user->forceFill(['last_login_at' => now()])->save();

        // Create Sanctum personal access token
        $deviceName = $validated['device_name'] ?? 'Android Tablet';
        $token = $user->createToken($deviceName)->plainTextToken;

        // Log login activity
        Auth::setUser($user);
        ActivityService::log(
            'login',
            "Logged in via Android Tablet: {$deviceName}",
            $user,
            ['device_name' => $deviceName],
            'auth'
        );

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $this->formatUser($user),
            'permissions' => $this->getUserPermissions($user),
        ]);
    }

    /**
     * Revoke current Sanctum token.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * Get authenticated user profile and permissions.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user()->load(['role', 'department', 'position']);

        return response()->json([
            'success' => true,
            'user' => $this->formatUser($user),
            'permissions' => $this->getUserPermissions($user),
        ]);
    }

    /**
     * Format user data for mobile consumption.
     */
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'employee_id' => $user->employee_id,
            'role' => $user->role ? [
                'id' => $user->role->id,
                'name' => $user->role->name,
                'slug' => $user->role->slug,
            ] : null,
            'department' => $user->department ? [
                'id' => $user->department->id,
                'name' => $user->department->name,
                'code' => $user->department->code,
            ] : null,
            'position' => $user->position ? [
                'id' => $user->position->id,
                'name' => $user->position->name,
                'code' => $user->position->code,
            ] : null,
            'is_admin' => $user->isAdmin(),
            'is_super_admin' => $user->isSuperAdmin(),
            'must_change_password' => (bool) $user->must_change_password,
        ];
    }

    /**
     * Map permissions for standard operational modules.
     */
    private function getUserPermissions(User $user): array
    {
        $modules = ['annealing', 'temperature', 'torque', 'welding', 'magnetism', 'material', 'modification'];
        $actions = ['view', 'create', 'update', 'delete', 'import', 'export', 'approve'];

        $permissions = [];
        foreach ($modules as $module) {
            $permissions[$module] = [];
            foreach ($actions as $action) {
                $permissions[$module][$action] = $user->hasPermission($module, $action);
            }
        }

        return $permissions;
    }
}
