<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use App\Models\Position;
use App\Models\UserQrCode;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display users list
     */
    public function index(Request $request): Response
    {
        $query = User::with(['role', 'department', 'position', 'qrCode']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role_id', $request->get('role'));
        }

        if ($request->filled('department')) {
            $query->where('department_id', $request->get('department'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('employment_status')) {
            $query->whereHas('qrCode', function ($q) use ($request) {
                $q->where('employment_status', $request->get('employment_status'));
            });
        }

        $users = $query->latest()->paginate(10)->withQueryString(); // @phpstan-ignore-line - withQueryString() is valid in Laravel 9

        // Ensure relationships are loaded and serialized properly
        $users->getCollection()->each(function ($user) {
            $user->loadMissing(['role', 'department', 'position', 'qrCode']);
        });

        return Inertia::render('UserManagement/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'department', 'status', 'employment_status']),
            'roles' => Role::orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'positions' => Position::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Show user creation form
     */
    public function create(): Response
    {
        return Inertia::render('UserManagement/Create', [
            'roles' => Role::orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'positions' => Position::with('department')->active()->get(),
        ]);
    }

    /**
     * Store new user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'employee_id' => 'required|string|max:50|unique:users,employee_id',
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'contact_number' => 'nullable|string|max:20',
            'employment_status' => 'required|in:regular,contractual,probationary',
            'hire_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:hire_date',
            'generate_qr' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'employee_id' => $validated['employee_id'],
                'role_id' => $validated['role_id'],
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'],
                'contact_number' => $validated['contact_number'],
                'status' => 'active',
            ]);

            // Create QR code if requested
            if ($request->boolean('generate_qr')) {
                $this->qrCodeService->createOrUpdateQrCode(
                    $user,
                    $validated['employee_id'],
                    $validated['employment_status'],
                    $validated['hire_date'] ? new \DateTime($validated['hire_date']) : null,
                    $validated['contract_end_date'] ? new \DateTime($validated['contract_end_date']) : null
                );
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('User creation failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Show user details
     */
    public function show(User $user): Response
    {
        $user->load(['role', 'department', 'position', 'qrCode']);
        
        // Load login history separately to avoid created_at issues
        $loginHistory = \App\Models\UserLoginHistory::where('user_id', $user->id)
            ->orderBy('login_at', 'desc')
            ->limit(10)
            ->get();
        
        $user->setRelation('loginHistory', $loginHistory);

        $qrStatus = null;
        $qrData = null;
        
        if ($user->qrCode) {
            $qrStatus = $this->qrCodeService->checkQrCodeStatus($user->qrCode);
            $qrData = $user->qrCode->qr_data;
        }

        return Inertia::render('UserManagement/Show', [
            'user' => $user,
            'qrStatus' => $qrStatus,
            'qrData' => $qrData,
        ]);
    }

    /**
     * Show user edit form
     */
    public function edit(User $user): Response
    {
        $user->load(['role', 'department', 'position', 'qrCode']);

        return Inertia::render('UserManagement/Edit', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'departments' => Department::active()->orderBy('name')->get(),
            'positions' => Position::with('department')->active()->get(),
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'employee_id' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'role_id' => 'required|exists:roles,id',
            'department_id' => 'nullable|exists:departments,id',
            'position_id' => 'nullable|exists:positions,id',
            'contact_number' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
            'employment_status' => 'required|in:regular,contractual,probationary',
            'hire_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:hire_date',
            'regenerate_qr' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        DB::beginTransaction();
        try {
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'employee_id' => $validated['employee_id'],
                'role_id' => $validated['role_id'],
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'],
                'contact_number' => $validated['contact_number'],
                'status' => $validated['status'],
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            // Update or regenerate QR code
            if ($request->boolean('regenerate_qr') || $user->qrCode) {
                $this->qrCodeService->createOrUpdateQrCode(
                    $user,
                    $validated['employee_id'],
                    $validated['employment_status'],
                    $validated['hire_date'] ? new \DateTime($validated['hire_date']) : null,
                    $validated['contract_end_date'] ? new \DateTime($validated['contract_end_date']) : null
                );
            }

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Delete user
     */
    public function destroy(User $user)
    {
        try {
            // Soft delete by deactivating
            $user->update(['status' => 'inactive']);
            
            // Deactivate QR code
            if ($user->qrCode) {
                $this->qrCodeService->deactivateQrCode($user);
            }

            return redirect()->route('users.index')
                ->with('success', 'User deactivated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to deactivate user: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate QR code for a user
     */
    public function regenerateQr(User $user)
    {
        try {
            $user->load('qrCode');

            $employmentStatus = $user->qrCode->employment_status ?? 'regular';
            $hireDate = $user->qrCode->hire_date ?? null;
            $contractEndDate = $user->qrCode->contract_end_date ?? null;

            $this->qrCodeService->createOrUpdateQrCode(
                $user,
                $user->employee_id,
                $employmentStatus,
                $hireDate ? new \DateTime($hireDate) : null,
                $contractEndDate ? new \DateTime($contractEndDate) : null
            );

            return back()->with('success', 'QR code regenerated successfully.');
        } catch (\Exception $e) {
            \Log::error('QR regeneration failed:', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to regenerate QR code: ' . $e->getMessage());
        }
    }

    /**
     * QR Code Scanner page
     */
    public function scanner(): Response
    {
        return Inertia::render('UserManagement/Scanner');
    }

    /**
     * Process QR Code scan
     */
    public function processScan(Request $request)
    {
        $request->validate([
            'qr_data' => 'required|string',
            'material_code' => 'nullable|string',
        ]);

        $qrData = $request->get('qr_data');
        $materialCode = $request->get('material_code');
        $user = $this->qrCodeService->findUserByQrData($qrData);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or inactive QR code.',
            ], 400);
        }

        // Record the scan
        if ($user->qrCode) {
            $this->qrCodeService->recordScan($user->qrCode);
        }

        // Record login history
        $user->recordLogin(
            $request->ip(),
            $request->userAgent(),
            'qr_code'
        );

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'employee_id' => $user->employee_id,
                'role' => $user->role?->name,
                'department' => $user->department?->name,
                'position' => $user->position?->name,
                'employment_status' => $user->qrCode?->employment_status,
                'material_code' => $materialCode,
            ],
        ]);
    }

    /**
     * Bulk actions on users
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = $request->get('user_ids');
        $action = $request->get('action');

        try {
            switch ($action) {
                case 'activate':
                    User::whereIn('id', $userIds)->update(['status' => 'active']);
                    $message = 'Users activated successfully.';
                    break;
                case 'deactivate':
                    User::whereIn('id', $userIds)->update(['status' => 'inactive']);
                    UserQrCode::whereIn('user_id', $userIds)->update(['is_active' => false]);
                    $message = 'Users deactivated successfully.';
                    break;
                case 'delete':
                    // Soft delete
                    User::whereIn('id', $userIds)->update(['status' => 'inactive']);
                    UserQrCode::whereIn('user_id', $userIds)->update(['is_active' => false]);
                    $message = 'Users deleted successfully.';
                    break;
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to perform bulk action: ' . $e->getMessage());
        }
    }
}
