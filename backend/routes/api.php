<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CameraHeartbeatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ViolationController;
use App\Http\Controllers\ViolationDetailController;
use App\Http\Controllers\ViolationTypeController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('custom_api_key')->group(function () {
    Route::post('/camera-heartbeat', [CameraHeartbeatController::class, 'store']);
    Route::post('/violations', [ViolationController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/refresh', [AuthController::class, 'refreshToken']);
    Route::get('/profile', [ProfileController::class, 'show']);

    Route::apiResource('users', UserController::class)->except(['create', 'edit']);
    Route::apiResource('violation-types', ViolationTypeController::class);

    Route::apiResource('violations', ViolationController::class)->except(['create', 'edit', 'update', 'store']);
    Route::put('/violations/{violation}/status', [ViolationController::class, 'updateStatus']);

    Route::apiResource('violation-details', ViolationDetailController::class)->except(['create', 'edit', 'update', 'store']);
    Route::put('/violation-details/{violationDetail}/status', [ViolationDetailController::class, 'updateStatus']);
});
