<?php

namespace App\Services;

use App\Models\AnnealingCheck;
use App\Models\ApprovalNotification;
use App\Models\TempRecord;
use App\Models\TorqueRecord;
use App\Models\User;
use App\Mail\AnnealingCheckNotification;
use App\Models\WeldingChecksheet;
use Illuminate\Database\Eloquent\Model;
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

        $approvers = User::where(function ($query) use ($module) {
            $query->whereHas('role', fn ($roleQuery) => $roleQuery->where('slug', 'super_admin'))
                ->orWhereHas('role.permissions', function ($permissionQuery) use ($module) {
                    $permissionQuery->where('module', $module)->where('action', 'approve');
                })
                ->orWhereHas('position.permissions', function ($permissionQuery) use ($module) {
                    $permissionQuery->where('module', $module)->where('action', 'approve');
                });
        })->get();

        foreach ($approvers as $approver) {
            ApprovalNotification::create([
                'annealing_check_id' => $record instanceof AnnealingCheck ? $record->id : null,
                'module' => $module,
                'approvable_type' => $record::class,
                'approvable_id' => $record->getKey(),
                'user_id' => $approver->id,
                'type' => $type,
                'status' => 'pending',
                'message' => $this->generateMessage($record, $module, $type),
            ]);

            if ($record instanceof AnnealingCheck && $approver->email) {
                try {
                    Mail::to($approver->email)->send(new AnnealingCheckNotification($record, $approver, $type));
                } catch (\Exception $e) {
                    \Log::warning("Failed to send email notification to {$approver->email}: " . $e->getMessage());
                }
            }
        }
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
}
