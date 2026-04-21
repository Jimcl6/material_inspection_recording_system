<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnnealingCheckController;
use App\Http\Controllers\TempRecordController;
use App\Http\Controllers\TorqueRecordController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\MagnetismController;
use App\Http\Controllers\ModificationLogController;
use App\Http\Controllers\MaterialPartController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DiaphragmWeldingController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes
require __DIR__.'/auth.php';

// Protected routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
        // Add more admin routes here
    });

    // Magnetism Checksheet (New structure with checksheets, batches, and checkpoints)
    // Import must come before resource routes
    Route::get('magnetism-checksheet/import', [MagnetismController::class, 'importForm'])
        ->middleware('module.permission:magnetism,import')
        ->name('magnetism-checksheet.import.form');
    Route::post('magnetism-checksheet/import', [MagnetismController::class, 'import'])
        ->middleware('module.permission:magnetism,import')
        ->name('magnetism-checksheet.import');
    Route::post('magnetism-checksheet/import/preview', [MagnetismController::class, 'importPreview'])
        ->middleware('module.permission:magnetism,import')
        ->name('magnetism-checksheet.import.preview');
    Route::post('magnetism-checksheet/import/execute', [MagnetismController::class, 'importExecute'])
        ->middleware('module.permission:magnetism,import')
        ->name('magnetism-checksheet.import.execute');
    
    // Main CRUD routes
    Route::get('magnetism-checksheet', [MagnetismController::class, 'index'])
        ->middleware('module.permission:magnetism,view')
        ->name('magnetism-checksheet.index');
    Route::get('magnetism-checksheet/create', [MagnetismController::class, 'create'])
        ->middleware('module.permission:magnetism,create')
        ->name('magnetism-checksheet.create');
    Route::post('magnetism-checksheet', [MagnetismController::class, 'store'])
        ->middleware('module.permission:magnetism,create')
        ->name('magnetism-checksheet.store');
    Route::get('magnetism-checksheet/{magnetism_checksheet}', [MagnetismController::class, 'show'])
        ->middleware('module.permission:magnetism,view')
        ->name('magnetism-checksheet.show');
    Route::get('magnetism-checksheet/{magnetism_checksheet}/edit', [MagnetismController::class, 'edit'])
        ->middleware('module.permission:magnetism,update')
        ->name('magnetism-checksheet.edit');
    Route::put('magnetism-checksheet/{magnetism_checksheet}', [MagnetismController::class, 'update'])
        ->middleware('module.permission:magnetism,update')
        ->name('magnetism-checksheet.update');
    Route::delete('magnetism-checksheet/{magnetism_checksheet}', [MagnetismController::class, 'destroy'])
        ->middleware('module.permission:magnetism,delete')
        ->name('magnetism-checksheet.destroy');
    Route::get('magnetism-checksheet/{magnetism_checksheet}/export', [MagnetismController::class, 'export'])
        ->middleware('module.permission:magnetism,export')
        ->name('magnetism-checksheet.export');
    
    // Batch routes (nested under checksheet)
    Route::post('magnetism-checksheet/{magnetism_checksheet}/batches', [MagnetismController::class, 'storeBatch'])
        ->middleware('module.permission:magnetism,create')
        ->name('magnetism-checksheet.batches.store');
    Route::put('magnetism-checksheet/{magnetism_checksheet}/batches/{batch}', [MagnetismController::class, 'updateBatch'])
        ->middleware('module.permission:magnetism,update')
        ->name('magnetism-checksheet.batches.update');
    Route::delete('magnetism-checksheet/{magnetism_checksheet}/batches/{batch}', [MagnetismController::class, 'destroyBatch'])
        ->middleware('module.permission:magnetism,delete')
        ->name('magnetism-checksheet.batches.destroy');
    Route::get('magnetism-checksheet/{magnetism_checksheet}/next-letter', [MagnetismController::class, 'nextLetter'])
        ->name('magnetism-checksheet.next-letter');
    Route::get('magnetism-checksheet/{magnetism_checksheet}/letter-for-lot', [MagnetismController::class, 'getLetterForLot'])
        ->name('magnetism-checksheet.letter-for-lot');
    
    // Checkpoint routes (bulk save for a production date)
    Route::put('magnetism-checksheet/{magnetism_checksheet}/checkpoints', [MagnetismController::class, 'updateCheckpoints'])
        ->middleware('module.permission:magnetism,update')
        ->name('magnetism-checksheet.checkpoints.update');

    // Modification Logs
    Route::get('modification-logs', [ModificationLogController::class, 'index'])
        ->middleware('module.permission:modification,view')
        ->name('modification-logs.index');
    Route::get('modification-logs/create', [ModificationLogController::class, 'create'])
        ->middleware('module.permission:modification,create')
        ->name('modification-logs.create');
    Route::post('modification-logs', [ModificationLogController::class, 'store'])
        ->middleware('module.permission:modification,create')
        ->name('modification-logs.store');
    Route::get('modification-logs/{modification_log}', [ModificationLogController::class, 'show'])
        ->middleware('module.permission:modification,view')
        ->name('modification-logs.show');
    Route::get('modification-logs/{modification_log}/edit', [ModificationLogController::class, 'edit'])
        ->middleware('module.permission:modification,update')
        ->name('modification-logs.edit');
    Route::put('modification-logs/{modification_log}', [ModificationLogController::class, 'update'])
        ->middleware('module.permission:modification,update')
        ->name('modification-logs.update');
    Route::delete('modification-logs/{modification_log}', [ModificationLogController::class, 'destroy'])
        ->middleware('module.permission:modification,delete')
        ->name('modification-logs.destroy');

    // Annealing Checks
    // Import/Export routes must come BEFORE the resource route
    Route::get('annealing-checks/import', [AnnealingCheckController::class, 'importForm'])
        ->middleware('module.permission:annealing,import')
        ->name('annealing-checks.import.form');
    Route::post('annealing-checks/import', [AnnealingCheckController::class, 'import'])
        ->middleware('module.permission:annealing,import')
        ->name('annealing-checks.import');
    Route::post('annealing-checks/import/preview', [AnnealingCheckController::class, 'importPreview'])
        ->middleware('module.permission:annealing,import')
        ->name('annealing-checks.import.preview');
    Route::post('annealing-checks/import/execute', [AnnealingCheckController::class, 'importExecute'])
        ->middleware('module.permission:annealing,import')
        ->name('annealing-checks.import.execute');
    Route::get('annealing-checks/export', [AnnealingCheckController::class, 'export'])
        ->middleware('module.permission:annealing,export')
        ->name('annealing-checks.export');
    Route::get('annealing-checks/debug', [AnnealingCheckController::class, 'debug']);
    
    // Approval routes (admin/inspector only)
    Route::middleware(['auth', 'module.permission:annealing,approve'])->group(function () {
        Route::get('annealing-checks/approval', [AnnealingCheckController::class, 'approval'])
            ->name('annealing-checks.approval');
        Route::post('annealing-checks/bulk-approve', [AnnealingCheckController::class, 'bulkApprove'])
            ->name('annealing-checks.bulk-approve');
        Route::post('annealing-checks/bulk-reject', [AnnealingCheckController::class, 'bulkReject'])
            ->name('annealing-checks.bulk-reject');
    });
    
    // Annealing Checks Resource Routes with permission middleware
    Route::get('annealing-checks', [AnnealingCheckController::class, 'index'])
        ->middleware('module.permission:annealing,view')
        ->name('annealing-checks.index');
    Route::get('annealing-checks/create', [AnnealingCheckController::class, 'create'])
        ->middleware('module.permission:annealing,create')
        ->name('annealing-checks.create');
    Route::post('annealing-checks', [AnnealingCheckController::class, 'store'])
        ->middleware('module.permission:annealing,create')
        ->name('annealing-checks.store');
    Route::get('annealing-checks/{annealing_check}', [AnnealingCheckController::class, 'show'])
        ->middleware('module.permission:annealing,view')
        ->name('annealing-checks.show');
    Route::get('annealing-checks/{annealing_check}/edit', [AnnealingCheckController::class, 'edit'])
        ->middleware('module.permission:annealing,update')
        ->name('annealing-checks.edit');
    Route::put('annealing-checks/{annealing_check}', [AnnealingCheckController::class, 'update'])
        ->middleware('module.permission:annealing,update')
        ->name('annealing-checks.update');
    Route::patch('annealing-checks/{annealing_check}', [AnnealingCheckController::class, 'update'])
        ->middleware('module.permission:annealing,update');
    Route::delete('annealing-checks/{annealing_check}', [AnnealingCheckController::class, 'destroy'])
        ->middleware('module.permission:annealing,delete')
        ->name('annealing-checks.destroy');
    
    // Temperature Records with permission middleware
    Route::get('temp-records', [TempRecordController::class, 'index'])
        ->middleware('module.permission:temperature,view')
        ->name('temp-records.index');
    Route::get('temp-records/create', [TempRecordController::class, 'create'])
        ->middleware('module.permission:temperature,create')
        ->name('temp-records.create');
    Route::post('temp-records', [TempRecordController::class, 'store'])
        ->middleware('module.permission:temperature,create')
        ->name('temp-records.store');
    Route::get('temp-records/{temp_record}', [TempRecordController::class, 'show'])
        ->middleware('module.permission:temperature,view')
        ->name('temp-records.show');
    Route::get('temp-records/{temp_record}/edit', [TempRecordController::class, 'edit'])
        ->middleware('module.permission:temperature,update')
        ->name('temp-records.edit');
    Route::put('temp-records/{temp_record}', [TempRecordController::class, 'update'])
        ->middleware('module.permission:temperature,update')
        ->name('temp-records.update');
    Route::patch('temp-records/{temp_record}', [TempRecordController::class, 'update'])
        ->middleware('module.permission:temperature,update');
    Route::delete('temp-records/{temp_record}', [TempRecordController::class, 'destroy'])
        ->middleware('module.permission:temperature,delete')
        ->name('temp-records.destroy');
    Route::get('temp-records-import', [TempRecordController::class, 'importForm'])
        ->middleware('module.permission:temperature,import')
        ->name('temp-records.import.form');
    Route::post('temp-records-import/preview', [TempRecordController::class, 'importPreview'])
        ->middleware('module.permission:temperature,import')
        ->name('temp-records.import.preview');
    Route::post('temp-records-import/execute', [TempRecordController::class, 'importExecute'])
        ->middleware('module.permission:temperature,import')
        ->name('temp-records.import.execute');

    // Torque Records with permission middleware
    Route::get('torque-records', [TorqueRecordController::class, 'index'])
        ->middleware('module.permission:torque,view')
        ->name('torque-records.index');
    Route::get('torque-records/create', [TorqueRecordController::class, 'create'])
        ->middleware('module.permission:torque,create')
        ->name('torque-records.create');
    Route::post('torque-records', [TorqueRecordController::class, 'store'])
        ->middleware('module.permission:torque,create')
        ->name('torque-records.store');
    Route::get('torque-records/{torque_record}', [TorqueRecordController::class, 'show'])
        ->middleware('module.permission:torque,view')
        ->name('torque-records.show');
    Route::get('torque-records/{torque_record}/edit', [TorqueRecordController::class, 'edit'])
        ->middleware('module.permission:torque,update')
        ->name('torque-records.edit');
    Route::put('torque-records/{torque_record}', [TorqueRecordController::class, 'update'])
        ->middleware('module.permission:torque,update')
        ->name('torque-records.update');
    Route::patch('torque-records/{torque_record}', [TorqueRecordController::class, 'update'])
        ->middleware('module.permission:torque,update');
    Route::delete('torque-records/{torque_record}', [TorqueRecordController::class, 'destroy'])
        ->middleware('module.permission:torque,delete')
        ->name('torque-records.destroy');
    Route::get('torque-records/{torque_record}/export', [TorqueRecordController::class, 'export'])
        ->middleware('module.permission:torque,export')
        ->name('torque-records.export');
    Route::get('torque-records-import', [TorqueRecordController::class, 'importForm'])
        ->middleware('module.permission:torque,import')
        ->name('torque-records.import.form');
    Route::post('torque-records-import', [TorqueRecordController::class, 'import'])
        ->middleware('module.permission:torque,import')
        ->name('torque-records.import');
    Route::post('torque-records-import/preview', [TorqueRecordController::class, 'importPreview'])
        ->middleware('module.permission:torque,import')
        ->name('torque-records.import.preview');
    Route::post('torque-records-import/execute', [TorqueRecordController::class, 'importExecute'])
        ->middleware('module.permission:torque,import')
        ->name('torque-records.import.execute');

    // Material Parts with permission middleware
    Route::get('material-monitoring-checksheets', [MaterialPartController::class, 'index'])
        ->middleware('module.permission:material,view')
        ->name('material-monitoring-checksheets.index');
    Route::get('material-monitoring-checksheets/create', [MaterialPartController::class, 'create'])
        ->middleware('module.permission:material,create')
        ->name('material-monitoring-checksheets.create');
    Route::get('material-monitoring-checksheets/for-ai', [MaterialPartController::class, 'getForAI'])
        ->name('material-monitoring-checksheets.for-ai');
    Route::post('material-monitoring-checksheets', [MaterialPartController::class, 'store'])
        ->middleware('module.permission:material,create')
        ->name('material-monitoring-checksheets.store');
    Route::get('material-monitoring-checksheets/{material_monitoring_checksheet}', [MaterialPartController::class, 'show'])
        ->middleware('module.permission:material,view')
        ->name('material-monitoring-checksheets.show');
    Route::get('material-monitoring-checksheets/{material_monitoring_checksheet}/edit', [MaterialPartController::class, 'edit'])
        ->middleware('module.permission:material,update')
        ->name('material-monitoring-checksheets.edit');
    Route::put('material-monitoring-checksheets/{material_monitoring_checksheet}', [MaterialPartController::class, 'update'])
        ->middleware('module.permission:material,update')
        ->name('material-monitoring-checksheets.update');
    Route::patch('material-monitoring-checksheets/{material_monitoring_checksheet}', [MaterialPartController::class, 'update'])
        ->middleware('module.permission:material,update');
    Route::delete('material-monitoring-checksheets/{material_monitoring_checksheet}', [MaterialPartController::class, 'destroy'])
        ->middleware('module.permission:material,delete')
        ->name('material-monitoring-checksheets.destroy');

    // Diaphragm Welding Checksheet
    // Import/Export routes must come BEFORE the resource route
    Route::get('diaphragm-welding/import', [DiaphragmWeldingController::class, 'importForm'])
        ->middleware('module.permission:diaphragm,import')
        ->name('diaphragm-welding.import.form');
    Route::post('diaphragm-welding/import', [DiaphragmWeldingController::class, 'import'])
        ->middleware('module.permission:diaphragm,import')
        ->name('diaphragm-welding.import');
    Route::post('diaphragm-welding/import/preview', [DiaphragmWeldingController::class, 'importPreview'])
        ->middleware('module.permission:diaphragm,import')
        ->name('diaphragm-welding.import.preview');
    Route::post('diaphragm-welding/import/execute', [DiaphragmWeldingController::class, 'importExecute'])
        ->middleware('module.permission:diaphragm,import')
        ->name('diaphragm-welding.import.execute');
    Route::get('diaphragm-welding/export', [DiaphragmWeldingController::class, 'export'])
        ->middleware('module.permission:diaphragm,export')
        ->name('diaphragm-welding.export');
    Route::get('diaphragm-welding/item-code-rules', [DiaphragmWeldingController::class, 'getItemCodeRules'])
        ->name('diaphragm-welding.item-code-rules');

    // Approval routes (admin/inspector only)
    Route::middleware(['auth', 'module.permission:diaphragm,approve'])->group(function () {
        Route::get('diaphragm-welding/approval', [DiaphragmWeldingController::class, 'approval'])
            ->name('diaphragm-welding.approval');
        Route::post('diaphragm-welding/bulk-approve', [DiaphragmWeldingController::class, 'bulkApprove'])
            ->name('diaphragm-welding.bulk-approve');
        Route::post('diaphragm-welding/bulk-reject', [DiaphragmWeldingController::class, 'bulkReject'])
            ->name('diaphragm-welding.bulk-reject');
    });

    // Diaphragm Welding Resource Routes with permission middleware
    Route::get('diaphragm-welding', [DiaphragmWeldingController::class, 'index'])
        ->middleware('module.permission:diaphragm,view')
        ->name('diaphragm-welding.index');
    Route::get('diaphragm-welding/create', [DiaphragmWeldingController::class, 'create'])
        ->middleware('module.permission:diaphragm,create')
        ->name('diaphragm-welding.create');
    Route::post('diaphragm-welding', [DiaphragmWeldingController::class, 'store'])
        ->middleware('module.permission:diaphragm,create')
        ->name('diaphragm-welding.store');
    Route::get('diaphragm-welding/{diaphragm_welding}', [DiaphragmWeldingController::class, 'show'])
        ->middleware('module.permission:diaphragm,view')
        ->name('diaphragm-welding.show');
    Route::get('diaphragm-welding/{diaphragm_welding}/edit', [DiaphragmWeldingController::class, 'edit'])
        ->middleware('module.permission:diaphragm,update')
        ->name('diaphragm-welding.edit');
    Route::put('diaphragm-welding/{diaphragm_welding}', [DiaphragmWeldingController::class, 'update'])
        ->middleware('module.permission:diaphragm,update')
        ->name('diaphragm-welding.update');
    Route::patch('diaphragm-welding/{diaphragm_welding}', [DiaphragmWeldingController::class, 'update'])
        ->middleware('module.permission:diaphragm,update');
    Route::delete('diaphragm-welding/{diaphragm_welding}', [DiaphragmWeldingController::class, 'destroy'])
        ->middleware('module.permission:diaphragm,delete')
        ->name('diaphragm-welding.destroy');

    // User Management (Admin only)
    Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
        // Custom routes must come BEFORE the resource route
        Route::get('users/scanner', [UserManagementController::class, 'scanner'])
            ->name('users.scanner');
        Route::post('users/scan', [UserManagementController::class, 'processScan'])
            ->name('users.scan');
        Route::post('users/bulk-action', [UserManagementController::class, 'bulkAction'])
            ->name('users.bulk-action');
        Route::post('users/{user}/regenerate-qr', [UserManagementController::class, 'regenerateQr'])
            ->name('users.regenerate-qr');
        
        Route::resource('users', UserManagementController::class)->names([
            'index' => 'users.index',
            'create' => 'users.create',
            'store' => 'users.store',
            'show' => 'users.show',
            'edit' => 'users.edit',
            'update' => 'users.update',
            'destroy' => 'users.destroy',
        ]);
    });

    // Super Admin Only Routes - Department, Position, Role Management
    Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
        // Departments
        Route::resource('departments', DepartmentController::class);
        Route::post('departments/{department}/toggle-status', [DepartmentController::class, 'toggleStatus'])
            ->name('departments.toggle-status');

        // Positions
        Route::resource('positions', PositionController::class);
        Route::post('positions/{position}/toggle-status', [PositionController::class, 'toggleStatus'])
            ->name('positions.toggle-status');
        Route::post('positions/{position}/permissions', [PositionController::class, 'syncPermissions'])
            ->name('positions.sync-permissions');

        // Roles
        Route::resource('roles', RoleController::class);
        Route::post('roles/{role}/toggle-status', [RoleController::class, 'toggleStatus'])
            ->name('roles.toggle-status');
        Route::post('roles/{role}/permissions', [RoleController::class, 'syncPermissions'])
            ->name('roles.sync-permissions');
    });

    // Admin + Super Admin Routes - Activity Logs
    Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
        Route::get('activity-logs', [ActivityLogController::class, 'index'])
            ->name('activity-logs.index');
        Route::get('activity-logs/{activity}', [ActivityLogController::class, 'show'])
            ->name('activity-logs.show');
        Route::delete('activity-logs/{activity}', [ActivityLogController::class, 'destroy'])
            ->name('activity-logs.destroy');
        Route::post('activity-logs/bulk-delete', [ActivityLogController::class, 'bulkDestroy'])
            ->name('activity-logs.bulk-destroy');
    });
});