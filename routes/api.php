<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MaterialSubLotTitleController;
use App\Http\Controllers\Api\MaterialTypeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum', 'account.active'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware([
    'auth:sanctum',
    'account.active',
    'verified',
    'module.permission:material,view',
    'throttle:material-api',
])->group(function (): void {
    Route::get('/material-types/{materialType}/sub-lot-titles', [MaterialSubLotTitleController::class, 'index']);
    Route::get('/material-types/{materialType}/sub-lot-fields', [MaterialTypeController::class, 'getSubLotFields']);
    Route::get('/material-types', [MaterialTypeController::class, 'getAllMaterialTypes']);
});
