<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApprovalNotificationsChanged implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $userId,
        public array $summary
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('approval-notifications.'.$this->userId);
    }

    public function broadcastAs(): string
    {
        return 'approval.notifications.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'summary' => $this->summary,
        ];
    }
}
