<?php

namespace Database\Seeders;

use App\Models\AnnealingCheck;
use App\Models\ApprovalNotification;
use App\Models\Department;
use App\Models\MagnetismBatch;
use App\Models\MagnetismCheckpoint;
use App\Models\MagnetismChecksheet;
use App\Models\MaterialPart;
use App\Models\Position;
use App\Models\Role;
use App\Models\TempRecord;
use App\Models\TorqueReading;
use App\Models\TorqueRecord;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\WeldingChecksheet;
use App\Models\WeldingChecksheetSample;
use App\Models\WeldingChecksheetType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class MirsTestingSeeder extends Seeder
{
    public function run(): void
    {
        if (! App::environment('testing')) {
            throw new RuntimeException('MirsTestingSeeder may only run in the testing environment.');
        }

        DB::transaction(function (): void {
            $permission = UserPermission::factory()->create([
                'name' => 'View Synthetic Materials',
                'slug' => 'material.view',
                'module' => 'material',
                'action' => 'view',
            ]);

            $role = Role::factory()->create([
                'name' => 'Synthetic Inspector',
                'slug' => 'testing_inspector',
            ]);
            $role->permissions()->attach($permission);

            $department = Department::factory()->create([
                'name' => 'Synthetic Quality Department',
                'code' => 'TEST-QA',
            ]);
            $position = Position::factory()->create([
                'name' => 'Synthetic Quality Inspector',
                'code' => 'TEST-QI',
                'department_id' => $department->id,
            ]);

            $activeUser = User::factory()->active()->create([
                'name' => 'Synthetic Active User',
                'email' => 'active.user@example.test',
                'employee_id' => 'TEST-EMP-001',
                'role_id' => $role->id,
                'department_id' => $department->id,
                'position_id' => $position->id,
            ]);
            User::factory()->inactive()->create([
                'name' => 'Synthetic Inactive User',
                'email' => 'inactive.user@example.test',
                'employee_id' => 'TEST-EMP-002',
                'role_id' => $role->id,
                'department_id' => $department->id,
                'position_id' => $position->id,
            ]);
            User::factory()->suspended()->create([
                'name' => 'Synthetic Suspended User',
                'email' => 'suspended.user@example.test',
                'employee_id' => 'TEST-EMP-003',
                'role_id' => $role->id,
                'department_id' => $department->id,
                'position_id' => $position->id,
            ]);

            $annealing = AnnealingCheck::factory()->pendingApproval()->create([
                'pic_id' => $activeUser->id,
                'checked_by_id' => $activeUser->id,
                'created_by' => $activeUser->id,
            ]);
            TempRecord::factory()->pendingApproval()->create();
            $torque = TorqueRecord::factory()->pendingApproval()->create();
            TorqueReading::factory()->create([
                'torque_record_id' => $torque->id,
            ]);

            $magnetism = MagnetismChecksheet::factory()->create();
            MagnetismBatch::factory()->create([
                'checksheet_id' => $magnetism->id,
            ]);
            MagnetismCheckpoint::factory()->create([
                'checksheet_id' => $magnetism->id,
            ]);

            MaterialPart::factory()->create();

            $weldingType = WeldingChecksheetType::factory()->create();
            $welding = WeldingChecksheet::factory()->pendingApproval()->create([
                'checksheet_type_id' => $weldingType->id,
                'operator_id' => $activeUser->id,
                'checked_by_id' => $activeUser->id,
                'created_by' => $activeUser->id,
            ]);
            WeldingChecksheetSample::factory()->create([
                'checksheet_id' => $welding->id,
            ]);

            ApprovalNotification::factory()->create([
                'annealing_check_id' => $annealing->id,
                'module' => 'annealing',
                'approvable_type' => AnnealingCheck::class,
                'approvable_id' => $annealing->id,
                'user_id' => $activeUser->id,
            ]);
        });
    }
}
