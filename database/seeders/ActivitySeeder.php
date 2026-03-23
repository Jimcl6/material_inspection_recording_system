<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first(); // Get the first user (usually admin)

        if ($user) {
            // Sample login activities
            Activity::create([
                'user_id' => $user->id,
                'type' => 'login',
                'subject_type' => '',
                'subject_id' => null,
                'description' => 'Logged in to the system',
                'properties' => ['ip' => '192.168.1.100'],
                'created_at' => now()->subMinutes(30),
            ]);

            Activity::create([
                'user_id' => $user->id,
                'type' => 'login',
                'subject_type' => '',
                'subject_id' => null,
                'description' => 'Logged in to the system',
                'properties' => ['ip' => '192.168.1.100'],
                'created_at' => now()->subHours(2),
            ]);

            // Sample create activities
            Activity::create([
                'user_id' => $user->id,
                'type' => 'create',
                'subject_type' => 'App\Models\AnnealingCheck',
                'subject_id' => 1,
                'description' => 'Created AnnealingCheck: # 1',
                'properties' => ['item_name' => 'Sample Item'],
                'created_at' => now()->subHours(3),
            ]);

            Activity::create([
                'user_id' => $user->id,
                'type' => 'create',
                'subject_type' => 'App\Models\TempRecord',
                'subject_id' => 1,
                'description' => 'Created TempRecord: # 1',
                'properties' => ['am_temp' => '25.5', 'pm_temp' => '26.0'],
                'created_at' => now()->subHours(5),
            ]);

            // Sample update activities
            Activity::create([
                'user_id' => $user->id,
                'type' => 'update',
                'subject_type' => 'App\Models\AnnealingCheck',
                'subject_id' => 1,
                'description' => 'Updated AnnealingCheck: # 1 - Updated: status',
                'properties' => ['changes' => ['status' => 'verified']],
                'created_at' => now()->subHours(4),
            ]);

            Activity::create([
                'user_id' => $user->id,
                'type' => 'update',
                'subject_type' => 'App\Models\User',
                'subject_id' => 2,
                'description' => 'Updated User: John Doe - Updated: email',
                'properties' => ['changes' => ['email' => 'john@example.com']],
                'created_at' => now()->subDay(),
            ]);

            // Sample delete activities
            Activity::create([
                'user_id' => $user->id,
                'type' => 'delete',
                'subject_type' => 'App\Models\TempRecord',
                'subject_id' => 0,
                'description' => 'Deleted TempRecord: # 5',
                'properties' => ['subject_data' => ['id' => 5, 'am_temp' => '24.0']],
                'created_at' => now()->subDays(2),
            ]);
        }
    }
}
