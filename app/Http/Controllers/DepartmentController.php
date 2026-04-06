<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:super_admin']);
    }

    /**
     * Display a listing of departments.
     */
    public function index(Request $request): Response
    {
        $query = Department::withCount('users', 'positions');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        $departments = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('Admin/Departments/Index', [
            'departments' => $departments,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new department.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Departments/Create');
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:departments,name',
            'code' => 'required|string|max:20|unique:departments,code',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $department = Department::create($validated);

        ActivityService::log(
            'create',
            "Created department: {$department->name}",
            $department,
            ['department_data' => $validated]
        );

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    /**
     * Show the form for editing the specified department.
     */
    public function edit(Department $department): Response
    {
        $department->loadCount('users', 'positions');

        return Inertia::render('Admin/Departments/Edit', [
            'department' => $department,
        ]);
    }

    /**
     * Update the specified department.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('departments')->ignore($department->id)],
            'code' => ['required', 'string', 'max:20', Rule::unique('departments')->ignore($department->id)],
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
        ]);

        $oldData = $department->toArray();
        $department->update($validated);

        ActivityService::log(
            'update',
            "Updated department: {$department->name}",
            $department,
            ['old_data' => $oldData, 'new_data' => $validated]
        );

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    /**
     * Remove the specified department.
     */
    public function destroy(Department $department)
    {
        // Check if department has users
        if ($department->users()->exists()) {
            return back()->with('error', 'Cannot delete department with assigned users. Please reassign users first.');
        }

        $departmentData = $department->toArray();
        $departmentName = $department->name;
        $department->delete();

        ActivityService::log(
            'delete',
            "Deleted department: {$departmentName}",
            null,
            ['deleted_department' => $departmentData],
            'departments'
        );

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully.');
    }

    /**
     * Toggle department active status.
     */
    public function toggleStatus(Department $department)
    {
        $department->update(['is_active' => !$department->is_active]);

        $status = $department->is_active ? 'activated' : 'deactivated';
        
        ActivityService::log(
            'update',
            "Department {$status}: {$department->name}",
            $department,
            ['status_change' => $status]
        );

        return back()->with('success', "Department {$status} successfully.");
    }
}
