<?php

use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\HealthCheckController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthCheckController::class);
    Route::get('/me' ,AuthenticatedUserController::class)
        ->middleware('auth:sanctum');
    Route::post('/auth/login', LoginController::class);
    Route::post('/auth/register', RegisterController::class);
});