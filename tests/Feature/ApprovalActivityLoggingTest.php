<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\AnnealingCheck;
use App\Models\Role;
use App\Models\TempRecord;
use App\Models\TorqueRecord;
use App\Models\User;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApprovalActivityLoggingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_approving_multiple_annealing_checks_logs_one_activity_per_record(): void
    {
        config(['features.approvals' => true]);

        $user = $this->approver();
        $checks = collect([
            $this->annealingCheck($user, ['item_code' => 'AN-ACT-001']),
            $this->annealingCheck($user, ['item_code' => 'AN-ACT-002']),
        ]);

        $response = $this->actingAs($user)->post(route('annealing-checks.bulk-approve'), [
            'check_ids' => $checks->pluck('id')->all(),
            'notes' => 'Approved during audit test',
        ]);

        $response->assertRedirect(route('annealing-checks.approval'));

        foreach ($checks as $check) {
            $activity = Activity::where('type', 'approve')
                ->where('module', 'annealing')
                ->where('subject_type', AnnealingCheck::class)
                ->where('subject_id', $check->id)
                ->first();

            $this->assertNotNull($activity);
            $this->assertSame($user->id, $activity->user_id);
            $this->assertSame('pending', $activity->properties['previous_status']);
            $this->assertSame('approved', $activity->properties['new_status']);
            $this->assertSame('Approved during audit test', $activity->properties['notes']);
            $this->assertSame('bulk_approval', $activity->properties['source']);
        }
    }

    public function test_rejecting_welding_checksheet_logs_reject_activity(): void
    {
        config(['features.approvals' => true]);

        $user = $this->approver();
        $checksheet = $this->weldingChecksheet($user);

        $response = $this->actingAs($user)->post(route('welding-checksheets.bulk-reject'), [
            'checksheet_ids' => [$checksheet->id],
            'notes' => 'Needs correction',
        ]);

        $response->assertRedirect(route('welding-checksheets.approval'));

        $activity = Activity::where('type', 'reject')
            ->where('module', 'welding')
            ->where('subject_type', WeldingChecksheet::class)
            ->where('subject_id', $checksheet->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($user->id, $activity->user_id);
        $this->assertSame('pending', $activity->properties['previous_status']);
        $this->assertSame('rejected', $activity->properties['new_status']);
        $this->assertSame('Needs correction', $activity->properties['notes']);
        $this->assertSame('Needs correction', $activity->properties['rejection_reason']);
    }

    public function test_approving_temperature_record_logs_approve_activity(): void
    {
        config(['features.approvals' => true]);

        $user = $this->approver();
        $record = TempRecord::create([
            'date' => '2026-06-26',
            'model_series' => 'TMP-ACT-001',
            'equipment_type' => 'Soldering Iron',
            'control_no' => 'TMP-CN-001',
            'temp_am' => '25',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('temp-records.bulk-approve'), [
            'record_ids' => [$record->id],
            'notes' => 'Temperature approved',
        ]);

        $response->assertRedirect(route('temp-records.approval'));

        $activity = Activity::where('type', 'approve')
            ->where('module', 'temperature')
            ->where('subject_type', TempRecord::class)
            ->where('subject_id', $record->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($user->id, $activity->user_id);
        $this->assertSame('pending', $activity->properties['previous_status']);
        $this->assertSame('approved', $activity->properties['new_status']);
        $this->assertSame('Temperature approved', $activity->properties['notes']);
    }

    public function test_approving_torque_record_logs_approve_activity(): void
    {
        config(['features.approvals' => true]);

        $user = $this->approver();
        $record = TorqueRecord::create([
            'date' => '2026-06-26',
            'model_series' => 'TRQ-ACT-001',
            'driver_model' => 'Electric Driver',
            'line_assigned' => 'Line 1',
            'screw_type' => 'M4',
            'torque_am' => '10',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('torque-records.bulk-approve'), [
            'record_ids' => [$record->id],
            'notes' => 'Torque approved',
        ]);

        $response->assertRedirect(route('torque-records.approval'));

        $activity = Activity::where('type', 'approve')
            ->where('module', 'torque')
            ->where('subject_type', TorqueRecord::class)
            ->where('subject_id', $record->id)
            ->first();

        $this->assertNotNull($activity);
        $this->assertSame($user->id, $activity->user_id);
        $this->assertSame('pending', $activity->properties['previous_status']);
        $this->assertSame('approved', $activity->properties['new_status']);
        $this->assertSame('Torque approved', $activity->properties['notes']);
    }

    private function approver(): User
    {
        $role = Role::updateOrCreate(
            ['slug' => 'super_admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full system access',
                'is_system' => true,
                'is_active' => true,
            ]
        );

        $user = User::create([
            'name' => 'Approval Activity Tester',
            'email' => 'approval-activity-' . uniqid() . '@example.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function annealingCheck(User $user, array $overrides = []): AnnealingCheck
    {
        return AnnealingCheck::create(array_merge([
            'item_code' => 'AN-ACT',
            'receiving_date' => '2026-06-26',
            'supplier_lot_number' => 'LOT-ACT',
            'quantity' => 1,
            'annealing_date' => '2026-06-26',
            'machine_number' => 'M-01',
            'status' => 'pending',
            'submitted_at' => now(),
            'pic_id' => $user->id,
            'checked_by_id' => $user->id,
            'verified_by_id' => $user->id,
            'created_by' => $user->id,
        ], $overrides));
    }

    private function weldingChecksheet(User $user): WeldingChecksheet
    {
        $type = WeldingChecksheetType::updateOrCreate(
            ['key' => 'activity-test'],
            [
                'name' => 'Activity Test Type',
                'description' => 'Used by approval activity tests',
                'is_active' => true,
            ]
        );

        return WeldingChecksheet::create([
            'checksheet_type_id' => $type->id,
            'item_code' => 'WLD-ACT-001',
            'production_date' => '2026-06-26',
            'status' => 'pending',
            'submitted_at' => now(),
            'created_by' => $user->id,
        ]);
    }
}
