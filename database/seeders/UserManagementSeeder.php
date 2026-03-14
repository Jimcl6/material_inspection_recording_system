<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;
use App\Models\UserPermission;

class UserManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create departments
        $departments = [
            ['name' => 'Production', 'code' => 'PROD', 'description' => 'Production Department'],
            ['name' => 'Quality Control', 'code' => 'QC', 'description' => 'Quality Control Department'],
            ['name' => 'Maintenance', 'code' => 'MAINT', 'description' => 'Maintenance Department'],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Human Resources Department'],
            ['name' => 'Engineering', 'code' => 'ENG', 'description' => 'Engineering Department'],
            ['name' => 'Warehouse', 'code' => 'WH', 'description' => 'Warehouse Department'],
        ];

        foreach ($departments as $dept) {
            Department::create($dept);
        }

        // Create positions
        $productionDept = Department::where('code', 'PROD')->first();
        $qcDept = Department::where('code', 'QC')->first();
        $maintDept = Department::where('code', 'MAINT')->first();
        $hrDept = Department::where('code', 'HR')->first();
        $engDept = Department::where('code', 'ENG')->first();
        $whDept = Department::where('code', 'WH')->first();

        $positions = [
            // Production
            ['name' => 'Production Operator', 'code' => 'PROD_OP', 'department_id' => $productionDept->id],
            ['name' => 'Production Supervisor', 'code' => 'PROD_SUP', 'department_id' => $productionDept->id],
            ['name' => 'Production Manager', 'code' => 'PROD_MGR', 'department_id' => $productionDept->id],
            
            // Quality Control
            ['name' => 'QC Inspector', 'code' => 'QC_INSP', 'department_id' => $qcDept->id],
            ['name' => 'QC Supervisor', 'code' => 'QC_SUP', 'department_id' => $qcDept->id],
            ['name' => 'QC Manager', 'code' => 'QC_MGR', 'department_id' => $qcDept->id],
            
            // Maintenance
            ['name' => 'Maintenance Technician', 'code' => 'MAINT_TECH', 'department_id' => $maintDept->id],
            ['name' => 'Maintenance Supervisor', 'code' => 'MAINT_SUP', 'department_id' => $maintDept->id],
            
            // HR
            ['name' => 'HR Staff', 'code' => 'HR_STAFF', 'department_id' => $hrDept->id],
            ['name' => 'HR Manager', 'code' => 'HR_MGR', 'department_id' => $hrDept->id],
            
            // Engineering
            ['name' => 'Process Engineer', 'code' => 'ENG_PROC', 'department_id' => $engDept->id],
            ['name' => 'Quality Engineer', 'code' => 'ENG_QE', 'department_id' => $engDept->id],
            
            // Warehouse
            ['name' => 'Warehouse Staff', 'code' => 'WH_STAFF', 'department_id' => $whDept->id],
            ['name' => 'Warehouse Supervisor', 'code' => 'WH_SUP', 'department_id' => $whDept->id],
        ];

        foreach ($positions as $pos) {
            Position::create($pos);
        }

        // Create permissions
        $modules = [
            'users' => ['create', 'read', 'update', 'delete', 'scan'],
            'annealing' => ['create', 'read', 'update', 'delete', 'verify'],
            'material' => ['create', 'read', 'update', 'delete'],
            'temperature' => ['create', 'read', 'update', 'delete'],
            'torque' => ['create', 'read', 'update', 'delete'],
            'production' => ['create', 'read', 'update', 'delete'],
            'reports' => ['read', 'export'],
            'dashboard' => ['read'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                UserPermission::createOrUpdate(
                    ucfirst($action) . ' ' . ucfirst($module),
                    $module,
                    $action
                );
            }
        }

        $this->command->info('User management data seeded successfully!');
    }
}
