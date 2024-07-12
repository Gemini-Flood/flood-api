<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\OperationController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/updateToken', [AuthController::class, 'updateFCMToken']);
});

Route::prefix('weathers')->group(function () {
    Route::post('/prevision', [OperationController::class, 'getWeather']);
});

Route::prefix('floods')->group(function () {
    Route::get('/reports', [OperationController::class, 'getReports']);
    Route::get('/reports/{id}', [OperationController::class, 'getUserReports']);
    Route::post('/report', [OperationController::class, 'saveReport']);

    Route::get('/zones', [OperationController::class, 'getFloodZones']);
    Route::post('/actualizeZones', [OperationController::class, 'actualizeFloodZone']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
