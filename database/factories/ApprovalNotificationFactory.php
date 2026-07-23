<?php

namespace Database\Factories;

use App\Models\ApprovalNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ApprovalNotification> */
class ApprovalNotificationFactory extends Factory
{
    protected $model = ApprovalNotification::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => 'new_submission',
            'status' => 'pending',
            'message' => 'Synthetic record is awaiting approval',
        ];
    }
}
