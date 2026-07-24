<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityService;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UserBadgeController extends Controller
{
    private QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
        $this->middleware(['auth', 'role:admin,super_admin']);
    }

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $users = User::query()
            ->active()
            ->with(['department:id,name', 'qrCode'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('name', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'employee_id' => $user->employee_id,
                'department' => $user->department?->name,
                'employment_status' => $user->qrCode?->employment_status,
                'qr_data' => $user->qrCode?->qr_data,
                'can_reissue' => (bool) $user->qrCode?->is_active,
            ]);

        return Inertia::render('UserManagement/BadgePrint', [
            'users' => $users,
            'filters' => ['search' => $search],
        ]);
    }

    public function reissue(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1', 'max:100'],
            'user_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $requestedIds = collect($validated['user_ids'])->map(fn ($id): int => (int) $id)->unique()->values();
        $users = User::query()
            ->active()
            ->with('qrCode')
            ->whereIn('id', $requestedIds)
            ->whereHas('qrCode', fn ($query) => $query->where('is_active', true))
            ->orderBy('id')
            ->get();

        if ($users->count() !== $requestedIds->count()) {
            throw ValidationException::withMessages([
                'user_ids' => 'Every selected user must be active and have an active stored badge.',
            ]);
        }

        DB::transaction(function () use ($users): void {
            foreach ($users as $user) {
                $this->qrCodeService->regenerateStoredQrCode($user);

                ActivityService::log(
                    'update',
                    "Reissued QR badge for user ID {$user->id}",
                    $user,
                    ['employee_id' => $user->employee_id, 'action' => 'qr_badge_reissued'],
                    'users'
                );
            }
        });

        return redirect()->route('users.badges')
            ->with('success', 'Selected badges were reissued and are ready to print.');
    }
}
