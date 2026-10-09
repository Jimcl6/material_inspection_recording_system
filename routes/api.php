<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MaterialTypeController;
use App\Http\Controllers\MaterialSubLotTitleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'account.active'])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware([
    'auth:sanctum',
    'account.active',
    'password.changed',
    'module.permission:material,view',
    'throttle:material-api',
])->group(function (): void {
    Route::get('/material-types/{materialType}/sub-lot-titles', [MaterialSubLotTitleController::class, 'index']);
    Route::get('/material-types/{materialType}/sub-lot-fields', [MaterialTypeController::class, 'getSubLotFields']);
    Route::get('/material-types', [MaterialTypeController::class, 'getAllMaterialTypes']);
});
