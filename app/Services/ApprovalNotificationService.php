<?php

namespace App\Services;

use App\Models\AnnealingCheck;
use App\Models\ApprovalNotification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\AnnealingCheckNotification;

class ApprovalNotificationService
{
    /**
     * Create notifications for all administrators and inspectors
     */
    public function notifyApprovers(AnnealingCheck $annealingCheck, string $type = 'new_submission')
    {
        // Get all users with admin or inspector roles
        $approvers = User::whereHas('role', function ($query) {
            $query->whereIn('slug', ['admin', 'inspector']);
        })->get();

        foreach ($approvers as $approver) {
            ApprovalNotification::create([
                'annealing_check_id' => $annealingCheck->id,
                'user_id' => $approver->id,
                'type' => $type,
                'status' => 'pending',
                'message' => $this->generateMessage($annealingCheck, $type),
            ]);

            // Send email notification
            if ($approver->email) {
                try {
                    Mail::to($approver->email)->send(new AnnealingCheckNotification($annealingCheck, $approver, $type));
                } catch (\Exception $e) {
                    \Log::warning("Failed to send email notification to {$approver->email}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Generate notification message
     */
    private function generateMessage(AnnealingCheck $annealingCheck, string $type): string
    {
        switch ($type) {
            case 'new_submission':
                return "New annealing check submitted for item {$annealingCheck->item_code}";
            case 'update':
                return "Annealing check updated for item {$annealingCheck->item_code}";
            default:
                return "Annealing check activity for item {$annealingCheck->item_code}";
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
        return ApprovalNotification::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with('annealingCheck')
            ->latest()
            ->get();
    }
}
