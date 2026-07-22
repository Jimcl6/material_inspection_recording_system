<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\SecurityAuditLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SetupAdmin extends Command
{
    protected $signature = 'setup:admin
                            {--user= : Email address of the existing user to configure}
                            {--force : Skip confirmation (required for non-interactive production use)}';

    protected $description = 'Assign the administrator role to one explicitly selected existing user';

    public function handle(): int
    {
        $email = trim((string) $this->option('user'));

        if ($email === '') {
            $this->error('The --user=<email> option is required.');

            return self::INVALID;
        }

        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email']]);
        if ($validator->fails()) {
            $this->error('The --user value must be a valid email address.');

            return self::INVALID;
        }

        $target = User::query()->where('email', $email)->first();
        if (! $target) {
            $this->error('No existing user matches the supplied email address.');

            return self::FAILURE;
        }

        if ($target->role && in_array($target->role->slug, ['admin', 'super_admin'], true)) {
            $this->warn("User ID {$target->id} is already configured with administrative access. No changes were made.");

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Intended administrative setup action');
        $this->table(
            ['Action', 'Target user ID', 'Target email', 'Role'],
            [['Assign role', $target->id, $target->email, 'admin']]
        );

        if ($this->input->isInteractive() && ! $this->option('force')) {
            if (! $this->confirm('Proceed with this administrative role assignment?', false)) {
                $this->warn('Operation cancelled. No changes were made.');

                return self::FAILURE;
            }
        } elseif (app()->environment('production') && ! $this->option('force')) {
            $this->error('Non-interactive production use requires --force. No changes were made.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($target): void {
            $adminRole = Role::query()->firstOrCreate(
                ['slug' => 'admin'],
                [
                    'name' => 'Administrator',
                    'description' => 'Has full access to all features',
                    'is_system' => true,
                    'is_active' => true,
                ]
            );

            $target->update(['role_id' => $adminRole->id]);

            SecurityAuditLog::query()->create([
                'actor' => $this->consoleActor(),
                'target_user_id' => $target->id,
                'action' => 'setup_admin_role_assigned',
                'occurred_at' => now(),
                'context' => [
                    'command' => 'setup:admin',
                    'target_email' => $target->email,
                    'assigned_role' => 'admin',
                ],
            ]);
        });

        $this->info("Administrator access assigned to user ID {$target->id}.");

        return self::SUCCESS;
    }

    private function consoleActor(): string
    {
        $username = trim((string) get_current_user());

        return 'console:'.($username !== '' ? $username : 'unknown');
    }
}
