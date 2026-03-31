<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnnealingCheckController;
use App\Http\Controllers\TempRecordController;
use App\Http\Controllers\TorqueRecordController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\ModificationLogController;
use App\Http\Controllers\MaterialPartController;
use App\Http\Controllers\UserManagementController;
use App\Models\AnnealingCheck;

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

    // Magnetism Checksheet
    Route::get('magnetism-checksheet', [ProductionBatchController::class, 'index'])
        ->name('magnetism-checksheet.index');
    Route::get('magnetism-checksheet/create', [ProductionBatchController::class, 'create'])
        ->name('magnetism-checksheet.create');
    Route::post('magnetism-checksheet', [ProductionBatchController::class, 'store'])
        ->name('magnetism-checksheet.store');
    Route::get('magnetism-checksheet/{magnetism_checksheet}', [ProductionBatchController::class, 'show'])
        ->name('magnetism-checksheet.show');
    Route::get('magnetism-checksheet/{magnetism_checksheet}/edit', [ProductionBatchController::class, 'edit'])
        ->name('magnetism-checksheet.edit');
    Route::put('magnetism-checksheet/{magnetism_checksheet}', [ProductionBatchController::class, 'update'])
        ->name('magnetism-checksheet.update');
    Route::delete('magnetism-checksheet/{magnetism_checksheet}', [ProductionBatchController::class, 'destroy'])
        ->name('magnetism-checksheet.destroy');
    Route::get('magnetism-checksheet/{magnetism_checksheet}/export', [ProductionBatchController::class, 'export'])
        ->name('magnetism-checksheet.export');
    Route::get('magnetism-checksheet/import', [ProductionBatchController::class, 'importForm'])
        ->name('magnetism-checksheet.import.form');
    Route::post('magnetism-checksheet/import', [ProductionBatchController::class, 'import'])
        ->name('magnetism-checksheet.import');
    Route::get('magnetism-checksheet/next-letter', [ProductionBatchController::class, 'nextLetter'])
        ->name('magnetism-checksheet.next-letter');
    
    // Magnetism Checksheet Checkpoints
    Route::get('magnetism-checksheet/{magnetism_checksheet}/checkpoints/create', [ProductionBatchController::class, 'createCheckpoint'])
        ->name('magnetism-checksheet.checkpoints.create');
    Route::post('magnetism-checksheet/{magnetism_checksheet}/checkpoints', [ProductionBatchController::class, 'storeCheckpoint'])
        ->name('magnetism-checksheet.checkpoints.store');
    Route::get('magnetism-checksheet/{magnetism_checksheet}/checkpoints/{checkpoint}/edit', [ProductionBatchController::class, 'editCheckpoint'])
        ->name('magnetism-checksheet.checkpoints.edit');
    Route::put('magnetism-checksheet/{magnetism_checksheet}/checkpoints/{checkpoint}', [ProductionBatchController::class, 'updateCheckpoint'])
        ->name('magnetism-checksheet.checkpoints.update');
    Route::delete('magnetism-checksheet/{magnetism_checksheet}/checkpoints/{checkpoint}', [ProductionBatchController::class, 'destroyCheckpoint'])
        ->name('magnetism-checksheet.checkpoints.destroy');

    // Modification Logs
    Route::get('modification-logs', [ModificationLogController::class, 'index'])
        ->name('modification-logs.index');
    Route::get('modification-logs/create', [ModificationLogController::class, 'create'])
        ->name('modification-logs.create');
    Route::post('modification-logs', [ModificationLogController::class, 'store'])
        ->name('modification-logs.store');
    Route::get('modification-logs/{modification_log}', [ModificationLogController::class, 'show'])
        ->name('modification-logs.show');
    Route::get('modification-logs/{modification_log}/edit', [ModificationLogController::class, 'edit'])
        ->name('modification-logs.edit');
    Route::put('modification-logs/{modification_log}', [ModificationLogController::class, 'update'])
        ->name('modification-logs.update');
    Route::delete('modification-logs/{modification_log}', [ModificationLogController::class, 'destroy'])
        ->name('modification-logs.destroy');

    // Annealing Checks
    // Import/Export routes must come BEFORE the resource route
    Route::get('annealing-checks/import', [AnnealingCheckController::class, 'importForm'])
        ->name('annealing-checks.import.form');
    Route::post('annealing-checks/import', [AnnealingCheckController::class, 'import'])
        ->name('annealing-checks.import');
    Route::get('annealing-checks/export', [AnnealingCheckController::class, 'export'])
        ->name('annealing-checks.export');
    Route::get('annealing-checks/debug', [AnnealingCheckController::class, 'debug']);
    
    // Approval routes (admin/inspector only)
    Route::middleware(['auth', 'role:admin,inspector'])->group(function () {
        Route::get('annealing-checks/approval', [AnnealingCheckController::class, 'approval'])
            ->name('annealing-checks.approval');
        Route::post('annealing-checks/bulk-approve', [AnnealingCheckController::class, 'bulkApprove'])
            ->name('annealing-checks.bulk-approve');
        Route::post('annealing-checks/bulk-reject', [AnnealingCheckController::class, 'bulkReject'])
            ->name('annealing-checks.bulk-reject');
    });
    
    Route::resource('annealing-checks', AnnealingCheckController::class);
    
    // Temperature Records
    Route::resource('temp-records', TempRecordController::class);
    Route::get('temp-records/import', [TempRecordController::class, 'importForm'])
        ->name('temp-records.import.form');
    Route::post('temp-records/import', [TempRecordController::class, 'import'])
        ->name('temp-records.import');

    // Torque Records
    Route::resource('torque-records', TorqueRecordController::class);
    Route::get('torque-records/{torque_record}/export', [TorqueRecordController::class, 'export'])
        ->name('torque-records.export');
    Route::get('torque-records/import', [TorqueRecordController::class, 'importForm'])
        ->name('torque-records.import.form');
    Route::post('torque-records/import', [TorqueRecordController::class, 'import'])
        ->name('torque-records.import');

    // Material Parts
    Route::resource('material-monitoring-checksheets', MaterialPartController::class);
    Route::get('material-monitoring-checksheets/for-ai', [MaterialPartController::class, 'getForAI'])
        ->name('material-monitoring-checksheets.for-ai');

    // User Management (Admin only)
    Route::middleware(['auth', 'role:admin'])->group(function () {
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

    
});