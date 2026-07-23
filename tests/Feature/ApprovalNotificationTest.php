<?php

namespace Tests\Feature;

use App\Events\ApprovalNotificationsChanged;
use App\Models\ApprovalNotification;
use App\Models\Role;
use App\Models\TempRecord;
use App\Models\TorqueRecord;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetType;
use App\Services\ApprovalNotificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ApprovalNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pending_submission_notifies_only_module_approvers(): void
    {
        config(['features.approvals' => true]);
        Event::fake([ApprovalNotificationsChanged::class]);

        $operator = $this->userWithPermissions('temperature-operator', [['temperature', 'create']]);
        $approver = $this->userWithPermissions('temperature-approver', [['temperature', 'approve']]);
        $viewer = $this->userWithPermissions('temperature-viewer', [['temperature', 'view']]);

        $response = $this->actingAs($operator)->post(route('temp-records.store'), [
            'date' => '2026-07-02',
            'model_series' => 'TMP-NOTIFY-001',
            'equipment_type' => 'Soldering Iron',
            'control_no' => 'TMP-NOTIFY-CN-001',
            'temp_am' => '25',
        ]);

        $response->assertRedirect();

        $record = TempRecord::where('model_series', 'TMP-NOTIFY-001')->firstOrFail();
        $this->assertSame('pending', $record->status);

        $this->assertDatabaseHas('approval_notifications', [
            'user_id' => $approver->id,
            'module' => 'temperature',
            'approvable_type' => TempRecord::class,
            'approvable_id' => $record->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('approval_notifications', [
            'user_id' => $operator->id,
            'approvable_type' => TempRecord::class,
            'approvable_id' => $record->id,
        ]);

        $this->assertDatabaseMissing('approval_notifications', [
            'user_id' => $viewer->id,
            'approvable_type' => TempRecord::class,
            'approvable_id' => $record->id,
        ]);

        Event::assertDispatched(
            ApprovalNotificationsChanged::class,
            fn (ApprovalNotificationsChanged $event) => $event->userId === $approver->id
                && $event->summary['pendingCount'] === 1
        );
    }

    public function test_summary_endpoint_returns_current_users_actionable_approvals(): void
    {
        config(['features.approvals' => true]);
        Event::fake([ApprovalNotificationsChanged::class]);

        $approver = $this->userWithPermissions('summary-approver', [['temperature', 'approve']]);
        $nonApprover = $this->userWithPermissions('summary-non-approver', [['torque', 'view']]);
        $record = TempRecord::create([
            'date' => '2026-07-02',
            'model_series' => 'TMP-SUMMARY-001',
            'equipment_type' => 'Soldering Iron',
            'control_no' => 'TMP-SUMMARY-CN-001',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        app(ApprovalNotificationService::class)->notifyApprovers($record, 'new_submission', 'temperature');

        $response = $this->actingAs($approver)->getJson(route('approval-notifications.summary'));

        $response->assertOk()
            ->assertJsonPath('pendingCount', 1)
            ->assertJsonPath('hasApprovalAccess', true)
            ->assertJsonPath('notifications.0.module', 'temperature')
            ->assertJsonPath('modules.0.module', 'temperature')
            ->assertJsonPath('modules.0.pendingCount', 1);

        $this->actingAs($nonApprover)
            ->getJson(route('approval-notifications.summary'))
            ->assertOk()
            ->assertJsonPath('pendingCount', 0)
            ->assertJsonPath('notifications', []);
    }

    public function test_acting_on_record_marks_all_related_approver_notifications_acted(): void
    {
        config(['features.approvals' => true]);
        Event::fake([ApprovalNotificationsChanged::class]);

        $approverOne = $this->userWithPermissions('torque-approver-one', [['torque', 'approve']]);
        $approverTwo = $this->userWithPermissions('torque-approver-two', [['torque', 'approve']]);
        $record = TorqueRecord::create([
            'date' => '2026-07-02',
            'model_series' => 'TRQ-NOTIFY-001',
            'driver_model' => 'Electric Driver',
            'line_assigned' => 'Line 1',
            'screw_type' => 'M4',
            'torque_am' => '10',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        app(ApprovalNotificationService::class)->notifyApprovers($record, 'new_submission', 'torque');

        $testApproverIds = [$approverOne->id, $approverTwo->id];

        $this->assertSame(2, ApprovalNotification::where('approvable_type', TorqueRecord::class)
            ->where('approvable_id', $record->id)
            ->whereIn('user_id', $testApproverIds)
            ->where('status', 'pending')
            ->count());

        $response = $this->actingAs($approverOne)->post(route('torque-records.bulk-approve'), [
            'record_ids' => [$record->id],
            'notes' => 'Approved through notification test',
        ]);

        $response->assertRedirect(route('torque-records.approval'));

        $this->assertSame(0, ApprovalNotification::where('approvable_type', TorqueRecord::class)
            ->where('approvable_id', $record->id)
            ->whereIn('user_id', $testApproverIds)
            ->where('status', 'pending')
            ->count());
        $this->assertSame(2, ApprovalNotification::where('approvable_type', TorqueRecord::class)
            ->where('approvable_id', $record->id)
            ->whereIn('user_id', $testApproverIds)
            ->where('status', 'acted')
            ->count());

        foreach ($testApproverIds as $approverId) {
            Event::assertDispatched(
                ApprovalNotificationsChanged::class,
                fn (ApprovalNotificationsChanged $event) => $event->userId === $approverId
            );
        }
    }

    public function test_welding_submission_creates_approver_notifications(): void
    {
        config(['features.approvals' => true]);
        Event::fake([ApprovalNotificationsChanged::class]);

        $operator = $this->userWithPermissions('welding-operator', [['welding', 'create']]);
        $approver = $this->userWithPermissions('welding-approver', [['welding', 'approve']]);
        $type = WeldingChecksheetType::create([
            'key' => 'notification-test',
            'name' => 'Notification Test',
            'check_items' => [
                ['key' => 'visual', 'label' => 'Visual Check'],
            ],
            'is_active' => true,
        ]);

        $response = $this->actingAs($operator)->post(route('welding-checksheets.store'), [
            'checksheet_type_id' => $type->id,
            'item_code' => 'WLD-NOTIFY-001',
            'production_date' => '2026-07-02',
            'machine_no' => 'W-01',
            'job_number' => 'JOB-NOTIFY-001',
            'samples' => [
                [
                    'check_item_key' => 'visual',
                    'check_item_label' => 'Visual Check',
                    'sample_values' => ['P', 'P', 'P', 'P', 'P'],
                ],
            ],
        ]);

        $response->assertRedirect(route('welding-checksheets.index'));

        $checksheet = WeldingChecksheet::where('item_code', 'WLD-NOTIFY-001')->firstOrFail();
        $this->assertSame('pending', $checksheet->status);

        $this->assertDatabaseHas('approval_notifications', [
            'user_id' => $approver->id,
            'module' => 'welding',
            'approvable_type' => WeldingChecksheet::class,
            'approvable_id' => $checksheet->id,
            'status' => 'pending',
        ]);
    }

    private function userWithPermissions(string $slug, array $permissions): User
    {
        $role = Role::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => ucwords(str_replace('-', ' ', $slug)),
                'description' => 'Created by approval notification tests',
                'is_system' => false,
                'is_active' => true,
            ]
        );

        foreach ($permissions as [$module, $action]) {
            $permission = UserPermission::createOrUpdate(
                ucfirst($action) . ' ' . ucfirst($module),
                $module,
                $action,
                'Permission for approval notification tests'
            );

            $role->grantPermission($permission);
        }

        $user = User::create([
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'email' => $slug . '-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }
}
