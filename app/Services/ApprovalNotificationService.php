<?php

namespace App\Services;

use App\Events\ApprovalNotificationsChanged;
use App\Models\AnnealingCheck;
use App\Models\ApprovalNotification;
use App\Models\TempRecord;
use App\Models\TorqueRecord;
use App\Models\User;
use App\Mail\AnnealingCheckNotification;
use App\Models\WeldingChecksheet;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class ApprovalNotificationService
{
    /**
     * Create notifications for users who can approve this module.
     */
    public function notifyApprovers(Model $record, string $type = 'new_submission', ?string $module = null): void
    {
        if (!config('features.approvals', false)) {
            return;
        }

        $module ??= $this->moduleForRecord($record);

        if ($module === null) {
            return;
        }

        if (($record->getAttribute('status') ?? null) !== 'pending') {
            return;
        }

        $approvers = $this->approversForModule($module);

        foreach ($approvers as $approver) {
            ApprovalNotification::firstOrCreate(
                [
                    'module' => $module,
                    'approvable_type' => $record::class,
                    'approvable_id' => $record->getKey(),
                    'user_id' => $approver->id,
                    'status' => 'pending',
                ],
                [
                    'annealing_check_id' => $record instanceof AnnealingCheck ? $record->id : null,
                    'type' => $type,
                    'message' => $this->generateMessage($record, $module, $type),
                ]
            );

            if ($record instanceof AnnealingCheck && $approver->email) {
                try {
                    Mail::to($approver->email)->send(new AnnealingCheckNotification($record, $approver, $type));
                } catch (\Exception $e) {
                    \Log::warning("Failed to send email notification to {$approver->email}: " . $e->getMessage());
                }
            }
        }

        $this->broadcastToUsers($approvers->pluck('id')->all());
    }

    public function summaryForUser(User $user): array
    {
        $approvalWorkflowService = app(ApprovalWorkflowService::class);
        $modules = $approvalWorkflowService->modulesForUser($user, true);

        return [
            'pendingCount' => $approvalWorkflowService->totalPending($modules),
            'notifications' => $this->getPendingNotifications($user)
                ->take(8)
                ->map(fn (ApprovalNotification $notification) => $this->formatNotification($notification))
                ->values(),
            'modules' => $modules
                ->map(fn (array $module) => [
                    'module' => $module['module'],
                    'label' => $module['label'],
                    'routeName' => $module['routeName'],
                    'pendingCount' => $module['pendingCount'],
                ])
                ->values(),
            'hasApprovalAccess' => $modules->isNotEmpty(),
        ];
    }

    public function markRecordsActed(iterable $records, ?string $module = null): void
    {
        $records = $records instanceof Collection || $records instanceof EloquentCollection
            ? $records
            : collect($records);

        $records = $records->filter();

        if ($records->isEmpty()) {
            return;
        }

        $firstRecord = $records->first();
        $module ??= $this->moduleForRecord($firstRecord);

        if ($module === null) {
            return;
        }

        $recordClass = $firstRecord::class;
        $recordIds = $records->map(fn (Model $record) => $record->getKey())->filter()->values()->all();

        if ($recordIds === []) {
            return;
        }

        $query = ApprovalNotification::where('status', 'pending')
            ->where(function ($query) use ($module, $recordClass, $recordIds) {
                $query->where(function ($nestedQuery) use ($module, $recordClass, $recordIds) {
                    $nestedQuery->where('module', $module)
                        ->where('approvable_type', $recordClass)
                        ->whereIn('approvable_id', $recordIds);
                });

                if ($recordClass === AnnealingCheck::class) {
                    $query->orWhereIn('annealing_check_id', $recordIds);
                }
            });

        $userIds = (clone $query)
            ->pluck('user_id')
            ->merge($this->approversForModule($module)->pluck('id'))
            ->unique()
            ->values()
            ->all();

        $query->update(['status' => 'acted']);

        $this->broadcastToUsers($userIds);
    }

    /**
     * Generate notification message
     */
    private function generateMessage(Model $record, string $module, string $type): string
    {
        $label = match ($module) {
            'annealing' => 'annealing check',
            'temperature' => 'temperature record',
            'torque' => 'torque record',
            'welding' => 'welding checksheet',
            default => 'approval record',
        };

        $title = $this->recordTitle($record);

        switch ($type) {
            case 'new_submission':
                return "New {$label} submitted for {$title}";
            case 'update':
                return ucfirst($label) . " updated for {$title}";
            default:
                return ucfirst($label) . " activity for {$title}";
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(ApprovalNotification $notification)
    {
        $notification->update(['status' => 'read']);
    }

    /**
     * Mark notification as acted
     */
    public function markAsActed(ApprovalNotification $notification)
    {
        $notification->update(['status' => 'acted']);
    }

    /**
     * Get pending notifications for a user
     */
    public function getPendingNotifications(User $user)
    {
        if (!config('features.approvals', false)) {
            return collect();
        }

        return ApprovalNotification::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['annealingCheck', 'approvable'])
            ->latest()
            ->get()
            ->filter(fn (ApprovalNotification $notification) => $this->isNotificationActionable($notification))
            ->values();
    }

    public function approversForModule(string $module): Collection
    {
        return User::where(function ($query) use ($module) {
            $query->whereHas('role', fn ($roleQuery) => $roleQuery->where('slug', 'super_admin'))
                ->orWhereHas('role.permissions', function ($permissionQuery) use ($module) {
                    $permissionQuery->where('module', $module)->where('action', 'approve');
                })
                ->orWhereHas('position.permissions', function ($permissionQuery) use ($module) {
                    $permissionQuery->where('module', $module)->where('action', 'approve');
                });
        })
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->orWhereNull('status');
            })
            ->get();
    }

    private function moduleForRecord(Model $record): ?string
    {
        return match (true) {
            $record instanceof AnnealingCheck => 'annealing',
            $record instanceof TempRecord => 'temperature',
            $record instanceof TorqueRecord => 'torque',
            $record instanceof WeldingChecksheet => 'welding',
            default => null,
        };
    }

    private function recordTitle(Model $record): string
    {
        if ($record instanceof AnnealingCheck) {
            return $record->item_code ?: 'Annealing Check #' . $record->id;
        }

        if ($record instanceof TempRecord) {
            return $record->model_series ?: 'Temperature Record #' . $record->id;
        }

        if ($record instanceof TorqueRecord) {
            return $record->model_series ?: 'Torque Record #' . $record->id;
        }

        if ($record instanceof WeldingChecksheet) {
            return $record->item_code ?: 'Welding Checksheet #' . $record->id;
        }

        return 'Record #' . $record->getKey();
    }

    private function broadcastToUsers(array $userIds): void
    {
        collect($userIds)
            ->filter()
            ->unique()
            ->each(function (int $userId) {
                $user = User::find($userId);

                if ($user) {
                    event(new ApprovalNotificationsChanged($user->id, $this->summaryForUser($user)));
                }
            });
    }

    private function formatNotification(ApprovalNotification $notification): array
    {
        $record = $notification->approvable ?: $notification->annealingCheck;
        $module = $notification->module
            ?? ($record instanceof Model ? $this->moduleForRecord($record) : null)
            ?? 'approval';

        return [
            'id' => $notification->id,
            'module' => $module,
            'moduleLabel' => $this->moduleLabel($module),
            'message' => $notification->message ?: 'Record waiting for approval',
            'routeName' => $this->approvalRouteForModule($module),
            'createdAt' => optional($notification->created_at)->toIso8601String(),
        ];
    }

    private function isNotificationActionable(ApprovalNotification $notification): bool
    {
        $record = $notification->approvable ?: $notification->annealingCheck;

        return $record instanceof Model && ($record->getAttribute('status') ?? null) === 'pending';
    }

    private function moduleLabel(string $module): string
    {
        return match ($module) {
            'annealing' => 'Annealing Checks',
            'temperature' => 'Temperature Records',
            'torque' => 'Torque Records',
            'welding' => 'Welding Checksheet',
            default => 'Approvals',
        };
    }

    private function approvalRouteForModule(string $module): string
    {
        return match ($module) {
            'annealing' => 'annealing-checks.approval',
            'temperature' => 'temp-records.approval',
            'torque' => 'torque-records.approval',
            'welding' => 'welding-checksheets.approval',
            default => 'approvals.index',
        };
    }
}
