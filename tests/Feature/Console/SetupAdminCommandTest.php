<?php

namespace Tests\Feature\Console;

use App\Models\Role;
use App\Models\SecurityAuditLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SetupAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_option_is_required(): void
    {
        $this->artisan('setup:admin')
            ->expectsOutput('The --user=<email> option is required.')
            ->assertExitCode(Command::INVALID);

        $this->assertDatabaseCount('security_audit_logs', 0);
    }

    public function test_target_user_must_exist(): void
    {
        $this->artisan('setup:admin', ['--user' => 'missing.user@example.test'])
            ->expectsOutput('No existing user matches the supplied email address.')
            ->assertExitCode(Command::FAILURE);
    }

    public function test_interactive_confirmation_can_cancel_without_changes(): void
    {
        $user = User::factory()->create(['email' => 'candidate@example.test']);
        $originalRoleId = $user->role_id;

        $this->artisan('setup:admin', ['--user' => $user->email])
            ->expectsConfirmation('Proceed with this administrative role assignment?', 'no')
            ->expectsOutput('Operation cancelled. No changes were made.')
            ->assertExitCode(Command::FAILURE);

        $this->assertSame($originalRoleId, $user->fresh()->role_id);
        $this->assertDatabaseCount('security_audit_logs', 0);
    }

    public function test_force_assigns_admin_role_and_writes_a_safe_audit_record(): void
    {
        $user = User::factory()->create(['email' => 'candidate@example.test']);

        $this->artisan('setup:admin', ['--user' => $user->email, '--force' => true])
            ->assertSuccessful();

        $this->assertSame('admin', $user->fresh()->role->slug);
        $audit = SecurityAuditLog::query()->sole();
        $this->assertSame($user->id, $audit->target_user_id);
        $this->assertSame('setup_admin_role_assigned', $audit->action);
        $this->assertStringStartsWith('console:', $audit->actor);
        $this->assertNotNull($audit->occurred_at);
        $this->assertSame('setup:admin', $audit->context['command']);
        $this->assertArrayNotHasKey('password', $audit->context);
        $this->assertArrayNotHasKey('token', $audit->context);
    }

    public function test_already_configured_user_fails_without_creating_another_audit_record(): void
    {
        $admin = Role::query()->where('slug', 'admin')->firstOrFail();
        $user = User::factory()->create([
            'email' => 'configured@example.test',
            'role_id' => $admin->id,
        ]);

        $this->artisan('setup:admin', ['--user' => $user->email, '--force' => true])
            ->expectsOutput("User ID {$user->id} is already configured with administrative access. No changes were made.")
            ->assertExitCode(Command::FAILURE);

        $this->assertDatabaseCount('security_audit_logs', 0);
    }

    public function test_non_interactive_production_use_requires_force(): void
    {
        $user = User::factory()->create(['email' => 'production.candidate@example.test']);
        $originalRoleId = $user->role_id;
        $this->app->detectEnvironment(fn (): string => 'production');

        $exitCode = Artisan::call('setup:admin', [
            '--user' => $user->email,
            '--no-interaction' => true,
        ]);

        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('requires --force', Artisan::output());
        $this->assertSame($originalRoleId, $user->fresh()->role_id);
        $this->assertDatabaseCount('security_audit_logs', 0);
    }
}
