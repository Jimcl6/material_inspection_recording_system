<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class RegenerateUserQrsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_makes_no_changes(): void
    {
        $user = $this->syntheticUser('TEST-QR-001', 'active');
        $qr = app(QrCodeService::class)->createOrUpdateQrCode($user, 'TEST-QR-001', 'regular');
        $before = $qr->qr_data;

        $this->artisan('mirs:regenerate-user-qrs', ['--dry-run' => true])
            ->expectsOutput('Stored badges eligible: 1')
            ->expectsOutput('Dry run complete. No QR records were changed.')
            ->assertSuccessful();

        $this->assertSame($before, $qr->fresh()->qr_data);
        $this->assertDatabaseCount('user_qr_codes', 1);
    }

    public function test_rerunning_updates_one_consistent_record_and_preserves_metadata(): void
    {
        $user = $this->syntheticUser('TEST-QR-002', 'active');
        $service = app(QrCodeService::class);
        $qr = $service->createOrUpdateQrCode($user, 'TEST-QR-002', 'contractual');
        $qr->update([
            'is_active' => false,
            'last_scanned_at' => now()->subDay(),
            'contract_end_date' => now()->addMonth()->toDateString(),
        ]);
        $originalId = $qr->id;
        $originalLastScan = $qr->last_scanned_at;

        $this->artisan('mirs:regenerate-user-qrs')->assertSuccessful();
        $firstPayload = $qr->fresh()->qr_data;
        $this->artisan('mirs:regenerate-user-qrs')->assertSuccessful();

        $regenerated = $qr->fresh();
        $this->assertSame($originalId, $regenerated->id);
        $this->assertDatabaseCount('user_qr_codes', 1);
        $this->assertFalse($regenerated->is_active);
        $this->assertTrue($originalLastScan->equalTo($regenerated->last_scanned_at));
        $this->assertSame('contractual', $regenerated->employment_status);
        $this->assertNotSame($firstPayload, $regenerated->qr_data);
        $this->assertSame('TEST-QR-002', $service->validateQrData($regenerated->qr_data)['employee_id']);
    }

    public function test_active_only_excludes_inactive_users(): void
    {
        $service = app(QrCodeService::class);
        $active = $this->syntheticUser('TEST-QR-003', 'active');
        $inactive = $this->syntheticUser('TEST-QR-004', 'inactive');
        $activeQr = $service->createOrUpdateQrCode($active, 'TEST-QR-003', 'regular');
        $inactiveQr = $service->createOrUpdateQrCode($inactive, 'TEST-QR-004', 'regular');
        $activeBefore = $activeQr->qr_data;
        $inactiveBefore = $inactiveQr->qr_data;

        Artisan::call('mirs:regenerate-user-qrs', ['--active-only' => true]);
        $output = Artisan::output();

        $this->assertNotSame($activeBefore, $activeQr->fresh()->qr_data);
        $this->assertSame($inactiveBefore, $inactiveQr->fresh()->qr_data);
        $this->assertStringContainsString("Eligible user IDs: {$active->id}", $output);
        $this->assertStringNotContainsString($activeBefore, $output);
        $this->assertStringNotContainsString($inactiveBefore, $output);
    }

    public function test_regenerated_synthetic_badge_still_resolves_to_the_active_user(): void
    {
        $service = app(QrCodeService::class);
        $user = $this->syntheticUser('TEST-QR-005', 'active');
        $service->createOrUpdateQrCode($user, 'TEST-QR-005', 'probationary');

        $this->artisan('mirs:regenerate-user-qrs')->assertSuccessful();

        $this->assertTrue($service->findUserByQrData($user->qrCode()->sole()->qr_data)->is($user));
    }

    private function syntheticUser(string $employeeId, string $status): User
    {
        return User::factory()->create([
            'name' => 'Synthetic Badge User',
            'email' => strtolower($employeeId).'@example.test',
            'employee_id' => $employeeId,
            'status' => $status,
        ]);
    }
}
