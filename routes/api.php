<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\ScheduleController;
use Illuminate\Support\Facades\Route;

// ── auth routes (guest)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// ── uth routes (sanctum protected) 
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

// ── Schedule routes (guest) 
Route::prefix('schedules')->group(function () {
    Route::get('/',          [ScheduleController::class, 'index']);
    Route::get('{schedule}', [ScheduleController::class, 'show']);
});

// ── Booking routes (auth required)
Route::middleware('auth:sanctum')->prefix('bookings')->group(function () {
    Route::post('/',       [BookingController::class, 'store']);
    Route::get('/me',      [BookingController::class, 'myBookings']);
    Route::delete('/{id}', [BookingController::class, 'destroy']);
});