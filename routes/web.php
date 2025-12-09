<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductionBatchController;
use App\Http\Controllers\InspectionCheckpointController;
use App\Http\Controllers\InspectionSampleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [ProductionBatchController::class, 'index'])->name('batches.index');

// Static routes first
Route::get('/batches/create', [ProductionBatchController::class, 'create'])->name('batches.create');
Route::get('/batches/next-letter', [ProductionBatchController::class, 'nextLetter'])->name('batches.nextLetter');
Route::post('/batches', [ProductionBatchController::class, 'store'])->name('batches.store');
Route::get('/batches/{batch:BatchID}/edit', [ProductionBatchController::class, 'edit'])
    ->whereNumber('batch')->name('batches.edit');
Route::put('/batches/{batch:BatchID}', [ProductionBatchController::class, 'update'])
    ->whereNumber('batch')->name('batches.update');
Route::delete('/batches/{batch:BatchID}', [ProductionBatchController::class, 'destroy'])
    ->whereNumber('batch')->name('batches.destroy');
// Parameter route last and constrained
Route::get('/batches/{batch:BatchID}', [ProductionBatchController::class, 'show'])
    ->whereNumber('batch')->name('batches.show');

// Checkpoints
Route::post('/batches/{batch:BatchID}/checkpoints', [InspectionCheckpointController::class, 'store'])
    ->whereNumber('batch')->name('checkpoints.store');
Route::get('/checkpoints/{checkpoint:CheckpointID}/edit', [InspectionCheckpointController::class, 'edit'])
    ->whereNumber('checkpoint')->name('checkpoints.edit');
Route::put('/checkpoints/{checkpoint:CheckpointID}', [InspectionCheckpointController::class, 'update'])
    ->whereNumber('checkpoint')->name('checkpoints.update');
Route::delete('/checkpoints/{checkpoint:CheckpointID}', [InspectionCheckpointController::class, 'destroy'])
    ->whereNumber('checkpoint')->name('checkpoints.destroy');

// Samples
Route::post('/checkpoints/{checkpoint:CheckpointID}/samples', [InspectionSampleController::class, 'store'])
    ->whereNumber('checkpoint')->name('samples.store');
Route::put('/samples/{sample:SampleID}', [InspectionSampleController::class, 'update'])
    ->whereNumber('sample')->name('samples.update');
Route::delete('/samples/{sample:SampleID}', [InspectionSampleController::class, 'destroy'])->name('samples.destroy');
