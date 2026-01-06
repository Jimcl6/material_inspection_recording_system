<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnnealingCheckController;
use App\Http\Controllers\TempRecordController;
use App\Http\Controllers\TorqueRecordController;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\ModificationLogController;

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

    // Production Batches
    Route::resource('production-batches', ProductionBatchController::class);
    Route::get('production-batches/{production_batch}/export', [ProductionBatchController::class, 'export'])
        ->name('production-batches.export');
    Route::get('production-batches/import', [ProductionBatchController::class, 'importForm'])
        ->name('production-batches.import.form');
    Route::post('production-batches/import', [ProductionBatchController::class, 'import'])
        ->name('production-batches.import');
    
    // Production Batch Checkpoints
    Route::get('production-batches/{production_batch}/checkpoints/create', [ProductionBatchController::class, 'createCheckpoint'])
        ->name('production-batches.checkpoints.create');

    // Modification Logs (read-only)
    Route::get('modification-logs', [ModificationLogController::class, 'index'])
        ->name('modification-logs.index');
    Route::get('modification-logs/{modification_log}', [ModificationLogController::class, 'show'])
        ->name('modification-logs.show');

    // Annealing Checks
    Route::resource('annealing-checks', AnnealingCheckController::class);
    
    // Additional routes for import/export
    Route::get('annealing-checks/{annealing_check}/export', [AnnealingCheckController::class, 'export'])
        ->name('annealing-checks.export');
    Route::get('annealing-checks/import', [AnnealingCheckController::class, 'importForm'])
        ->name('annealing-checks.import.form');
    Route::post('annealing-checks/import', [AnnealingCheckController::class, 'import'])
        ->name('annealing-checks.import');

    // Temperature Records
    Route::resource('temp-records', TempRecordController::class);
    Route::get('temp-records/{temperature_record}/export', [TempRecordController::class, 'export'])
        ->name('temp-records.export');
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

    
});