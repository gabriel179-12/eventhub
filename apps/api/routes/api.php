<?php

use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\HealthCheckController;
use App\Http\Controllers\Api\V1\Auth\AuthenticatedUserController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\CreateOrganizerController;
use App\Http\Controllers\Api\V1\UpdateOrganizerController;
use App\Http\Controllers\Api\V1\AddOrganizerMemberController;
use App\Http\Controllers\Api\V1\ListMyOrganizersController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\CreateEventController;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', HealthCheckController::class);
    Route::get('/me' ,AuthenticatedUserController::class)
        ->middleware('auth:sanctum');
    Route::post('/auth/login', LoginController::class);
    Route::post('/auth/logout', LogoutController::class)
        ->middleware('auth:sanctum');
    Route::post('/auth/register', RegisterController::class);
    Route::post('/organizers', CreateOrganizerController::class)
        ->middleware('auth:sanctum');
    Route::get('/organizers', ListMyOrganizersController::class)
        ->middleware('auth:sanctum');
    Route::post(
        '/organizers/{organizer:slug}/members',
        AddOrganizerMemberController::class,
    )->middleware('auth:sanctum');
    Route::patch(
        '/organizers/{organizer:slug}',
        UpdateOrganizerController::class,
    )->middleware('auth:sanctum');
    Route::post(
        '/organizers/{organizer:slug}/events',
        CreateEventController::class,
    )->middleware('auth:sanctum');
});