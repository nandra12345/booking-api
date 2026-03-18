<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ScheduleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ── Guest routes ───────────────────────────────────────────────────────────
Route::prefix('schedules')->group(function () {
    Route::get('/',    [ScheduleController::class, 'index']);
    Route::get('{schedule}', [ScheduleController::class, 'show']);
});

// ── Authenticated routes ────────────────────────────────────────────────────
Route::middleware('auth:sanctum')->prefix('bookings')->group(function () {
    Route::post('/',     [BookingController::class, 'store']);
    Route::get('/me',    [BookingController::class, 'myBookings']);
    Route::delete('/{id}', [BookingController::class, 'destroy']);
});