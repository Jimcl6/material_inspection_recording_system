<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\UserPermission;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * Display a listing of roles.
     */
    public function index(Request $request): Response
    {
        $query = Role::withCount('users', 'permissions');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            if ($request->get('type') === 'system') {
                $query->system();
            } else {
                $query->custom();
            }
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $roles = $query->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'filters' => $request->only(['search', 'type', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Roles/Create', [
            'permissions' => UserPermission::orderBy('module')->orderBy('action')->get()
                ->groupBy('module'),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'slug' => 'required|string|max:100|unique:roles,slug|regex:/^[a-z_]+$/',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:user_permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'is_system' => false, // Custom roles are never system roles
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Sync permissions if provided
        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        ActivityService::log(
            'create',
            "Created role: {$role->name}",
            $role,
            ['role_data' => $validated]
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): Response
    {
        $role->load('permissions');
        $role->loadCount('users');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
            'permissions' => UserPermission::orderBy('module')->orderBy('action')->get()
                ->groupBy('module'),
            'assignedPermissions' => $role->permissions->pluck('id')->toArray(),
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        // System roles can only have their permissions updated, not their core properties
        $rules = [
            'permissions' => 'array',
            'permissions.*' => 'exists:user_permissions,id',
        ];

        if (!$role->isSystem()) {
            $rules['name'] = ['required', 'string', 'max:100', Rule::unique('roles')->ignore($role->id)];
            $rules['slug'] = ['required', 'string', 'max:100', 'regex:/^[a-z_]+$/', Rule::unique('roles')->ignore($role->id)];
            $rules['description'] = 'nullable|string|max:500';
            $rules['is_active'] = 'boolean';
        }

        $validated = $request->validate($rules);

        $oldData = $role->toArray();
        $oldPermissions = $role->permissions->pluck('id')->toArray();

        // Update role properties only if not a system role
        if (!$role->isSystem()) {
            $role->update([
                'name' => $validated['name'],
                'slug' => $validated['slug'],
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);
        }

        // Sync permissions (allowed for all roles including system roles)
        $role->syncPermissions($validated['permissions'] ?? []);

        ActivityService::log(
            'update',
            "Updated role: {$role->name}",
            $role,
            [
                'old_data' => $oldData,
                'new_data' => $validated,
                'old_permissions' => $oldPermissions,
                'new_permissions' => $validated['permissions'] ?? [],
            ]
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of system roles
        if ($role->isSystem()) {
            return back()->with('error', 'System roles cannot be deleted.');
        }

        // Check if role has users
        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete role with assigned users. Please reassign users first.');
        }

        $roleData = $role->toArray();
        $roleName = $role->name;
        
        // Detach permissions before deleting
        $role->permissions()->detach();
        $role->delete();

        ActivityService::log(
            'delete',
            "Deleted role: {$roleName}",
            null,
            ['deleted_role' => $roleData],
            'roles'
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Toggle role active status.
     */
    public function toggleStatus(Role $role)
    {
        // Prevent deactivating super_admin role
        if ($role->slug === 'super_admin') {
            return back()->with('error', 'Cannot deactivate the Super Admin role.');
        }

        $role->update(['is_active' => !$role->is_active]);

        $status = $role->is_active ? 'activated' : 'deactivated';
        
        ActivityService::log(
            'update',
            "Role {$status}: {$role->name}",
            $role,
            ['status_change' => $status]
        );

        return back()->with('success', "Role {$status} successfully.");
    }

    /**
     * Sync permissions for a role.
     */
    public function syncPermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:user_permissions,id',
        ]);

        $oldPermissions = $role->permissions->pluck('id')->toArray();
        $role->syncPermissions($validated['permissions'] ?? []);

        ActivityService::log(
            'update',
            "Updated permissions for role: {$role->name}",
            $role,
            [
                'old_permissions' => $oldPermissions,
                'new_permissions' => $validated['permissions'] ?? [],
            ]
        );

        return back()->with('success', 'Role permissions updated successfully.');
    }
}
