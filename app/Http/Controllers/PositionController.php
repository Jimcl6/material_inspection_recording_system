<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use App\Models\UserPermission;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PositionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * Display a listing of positions.
     */
    public function index(Request $request): Response
    {
        $query = Position::with('department')->withCount('users');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->get('department'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $positions = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Positions/Index', [
            'positions' => $positions,
            'filters' => $request->only(['search', 'department', 'status']),
            'departments' => Department::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Show the form for creating a new position.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Positions/Create', [
            'departments' => Department::active()->orderBy('name')->get(),
            'permissions' => UserPermission::orderBy('module')->orderBy('action')->get()
                ->groupBy('module'),
        ]);
    }

    /**
     * Store a newly created position.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:positions,code',
            'description' => 'nullable|string|max:500',
            'department_id' => 'required|exists:departments,id',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:user_permissions,id',
        ]);

        $position = Position::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'department_id' => $validated['department_id'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Sync permissions if provided
        if (!empty($validated['permissions'])) {
            $position->syncPermissions($validated['permissions']);
        }

        ActivityService::log(
            'create',
            "Created position: {$position->name}",
            $position,
            ['position_data' => $validated]
        );

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position created successfully.');
    }

    /**
     * Show the form for editing the specified position.
     */
    public function edit(Position $position): Response
    {
        $position->load('department', 'permissions');
        $position->loadCount('users');

        return Inertia::render('Admin/Positions/Edit', [
            'position' => $position,
            'departments' => Department::active()->orderBy('name')->get(),
            'permissions' => UserPermission::orderBy('module')->orderBy('action')->get()
                ->groupBy('module'),
            'assignedPermissions' => $position->permissions->pluck('id')->toArray(),
        ]);
    }

    /**
     * Update the specified position.
     */
    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'max:20', Rule::unique('positions')->ignore($position->id)],
            'description' => 'nullable|string|max:500',
            'department_id' => 'required|exists:departments,id',
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => 'exists:user_permissions,id',
        ]);

        $oldData = $position->toArray();
        $oldPermissions = $position->permissions->pluck('id')->toArray();

        $position->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'department_id' => $validated['department_id'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Sync permissions
        $position->syncPermissions($validated['permissions'] ?? []);

        ActivityService::log(
            'update',
            "Updated position: {$position->name}",
            $position,
            [
                'old_data' => $oldData,
                'new_data' => $validated,
                'old_permissions' => $oldPermissions,
                'new_permissions' => $validated['permissions'] ?? [],
            ]
        );

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position updated successfully.');
    }

    /**
     * Remove the specified position.
     */
    public function destroy(Position $position)
    {
        // Check if position has users
        if ($position->users()->exists()) {
            return back()->with('error', 'Cannot delete position with assigned users. Please reassign users first.');
        }

        $positionData = $position->toArray();
        $positionName = $position->name;
        
        // Detach permissions before deleting
        $position->permissions()->detach();
        $position->delete();

        ActivityService::log(
            'delete',
            "Deleted position: {$positionName}",
            null,
            ['deleted_position' => $positionData],
            'positions'
        );

        return redirect()->route('admin.positions.index')
            ->with('success', 'Position deleted successfully.');
    }

    /**
     * Toggle position active status.
     */
    public function toggleStatus(Position $position)
    {
        $position->update(['is_active' => !$position->is_active]);

        $status = $position->is_active ? 'activated' : 'deactivated';
        
        ActivityService::log(
            'update',
            "Position {$status}: {$position->name}",
            $position,
            ['status_change' => $status]
        );

        return back()->with('success', "Position {$status} successfully.");
    }

    /**
     * Sync permissions for a position.
     */
    public function syncPermissions(Request $request, Position $position)
    {
        $validated = $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:user_permissions,id',
        ]);

        $oldPermissions = $position->permissions->pluck('id')->toArray();
        $position->syncPermissions($validated['permissions'] ?? []);

        ActivityService::log(
            'update',
            "Updated permissions for position: {$position->name}",
            $position,
            [
                'old_permissions' => $oldPermissions,
                'new_permissions' => $validated['permissions'] ?? [],
            ]
        );

        return back()->with('success', 'Position permissions updated successfully.');
    }
}
