<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;
use App\Models\UserPermission;

class PositionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Assigns default permissions to positions.
     *
     * @return void
     */
    public function run()
    {
        // Production Operator - view and create only for inspection modules
        $productionOperator = Position::where('name', 'Production Operator')->first();
        
        if ($productionOperator) {
            $this->assignProductionOperatorPermissions($productionOperator);
            $this->command->info('Production Operator permissions assigned.');
        } else {
            $this->command->warn('Production Operator position not found. Skipping...');
        }

        // You can add more position-specific permissions here
        // Example: Inspector, Quality Control, etc.
    }

    /**
     * Assign permissions to Production Operator
     * They can only view and create records, but cannot update, delete, import, or export
     */
    protected function assignProductionOperatorPermissions(Position $position): void
    {
        // Inspection modules that production operators can access
        $inspectionModules = [
            'annealing',
            'temperature', 
            'torque',
            'magnetism',
            'diaphragm',
            'material',
            'modification',
        ];

        // Actions allowed for production operators
        $allowedActions = ['view', 'create'];

        $permissionIds = [];

        foreach ($inspectionModules as $module) {
            foreach ($allowedActions as $action) {
                $permission = UserPermission::where('module', $module)
                    ->where('action', $action)
                    ->first();

                if ($permission) {
                    $permissionIds[] = $permission->id;
                }
            }
        }

        // Sync permissions (this will remove any existing and set only these)
        $position->permissions()->sync($permissionIds);

        $this->command->info("Assigned " . count($permissionIds) . " permissions to Production Operator.");
    }
}
