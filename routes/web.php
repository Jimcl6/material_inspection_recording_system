<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AnnealingCheckController;

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

    // Annealing Checks
    Route::resource('annealing-checks', AnnealingCheckController::class);
    
    // Additional routes for import/export
    Route::get('annealing-checks/{annealing_check}/export', [AnnealingCheckController::class, 'export'])
        ->name('annealing-checks.export');
    Route::get('annealing-checks/import', [AnnealingCheckController::class, 'importForm'])
        ->name('annealing-checks.import.form');
    Route::post('annealing-checks/import', [AnnealingCheckController::class, 'import'])
        ->name('annealing-checks.import');
});