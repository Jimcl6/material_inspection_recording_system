<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UserBadgeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_badge_page_requires_authentication(): void
    {
        $this->get(route('users.badges'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_access_or_reissue_badges(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('users.badges'))->assertRedirect(route('dashboard'));
        $this->actingAs($user)->post(route('users.badges.reissue'), ['user_ids' => [$user->id]])
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_page_contains_only_active_users_and_renders_the_badge_component(): void
    {
        $admin = $this->adminUser();
        $service = app(QrCodeService::class);
        $active = $this->syntheticUser('PRINT-001', 'active');
        $inactive = $this->syntheticUser('PRINT-002', 'inactive');
        $activeQr = $service->createOrUpdateQrCode($active, 'PRINT-001', 'regular');
        $service->createOrUpdateQrCode($inactive, 'PRINT-002', 'regular');

        $this->actingAs($admin)->get(route('users.badges'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('UserManagement/BadgePrint')
                ->has('users', 2)
                ->where('users.1.id', $active->id)
                ->where('users.1.qr_data', $activeQr->qr_data)
                ->where('users.1.can_reissue', true)
            );
    }

    public function test_admin_can_batch_reissue_active_badges_without_creating_duplicate_records(): void
    {
        $admin = $this->adminUser();
        $service = app(QrCodeService::class);
        $first = $this->syntheticUser('PRINT-003', 'active');
        $second = $this->syntheticUser('PRINT-004', 'active');
        $firstQr = $service->createOrUpdateQrCode($first, 'PRINT-003', 'regular');
        $secondQr = $service->createOrUpdateQrCode($second, 'PRINT-004', 'probationary');
        $firstBefore = $firstQr->qr_data;
        $secondBefore = $secondQr->qr_data;

        $this->actingAs($admin)->post(route('users.badges.reissue'), [
            'user_ids' => [$first->id, $second->id],
        ])->assertRedirect(route('users.badges'));

        $this->assertNotSame($firstBefore, $firstQr->fresh()->qr_data);
        $this->assertNotSame($secondBefore, $secondQr->fresh()->qr_data);
        $this->assertDatabaseCount('user_qr_codes', 2);
        $this->assertDatabaseHas('activities', [
            'user_id' => $admin->id,
            'subject_id' => $first->id,
            'module' => 'users',
        ]);
    }

    public function test_inactive_user_cannot_be_reissued_from_the_batch_page(): void
    {
        $admin = $this->adminUser();
        $inactive = $this->syntheticUser('PRINT-005', 'inactive');
        $qr = app(QrCodeService::class)->createOrUpdateQrCode($inactive, 'PRINT-005', 'regular');
        $before = $qr->qr_data;

        $this->actingAs($admin)->post(route('users.badges.reissue'), [
            'user_ids' => [$inactive->id],
        ])->assertSessionHasErrors('user_ids');

        $this->assertSame($before, $qr->fresh()->qr_data);
    }

    private function adminUser(): User
    {
        return User::factory()->create([
            'name' => 'A Test Badge Administrator',
            'role_id' => Role::query()->where('slug', 'admin')->value('id'),
            'employee_id' => 'TEST-ADMIN-001',
            'email' => 'badge.admin@example.test',
        ]);
    }

    private function syntheticUser(string $employeeId, string $status): User
    {
        return User::factory()->create([
            'name' => 'Synthetic Print User',
            'email' => strtolower($employeeId).'@example.test',
            'employee_id' => $employeeId,
            'status' => $status,
        ]);
    }
}
