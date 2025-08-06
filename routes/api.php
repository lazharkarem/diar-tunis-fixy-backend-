<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\AdminController;

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

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Public property routes
Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{id}', [PropertyController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });

    // User routes
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Property management (Host only)
    Route::middleware('role:host')->group(function () {
        Route::prefix('host')->group(function () {
            Route::get('/properties', [PropertyController::class, 'hostProperties']);
            Route::post('/properties', [PropertyController::class, 'store']);
            Route::put('/properties/{id}', [PropertyController::class, 'update']);
            Route::delete('/properties/{id}', [PropertyController::class, 'destroy']);
            Route::get('/bookings', [BookingController::class, 'hostBookings']);
            Route::get('/earnings', [BookingController::class, 'hostEarnings']);
        });
    });

    // Guest routes
    Route::middleware('role:guest')->group(function () {
        Route::prefix('guest')->group(function () {
            Route::post('/bookings', [BookingController::class, 'store']);
            Route::get('/bookings', [BookingController::class, 'guestBookings']);
            Route::get('/bookings/{id}', [BookingController::class, 'show']);
            Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel']);
        });
    });

    // Admin routes
    Route::middleware('role:admin')->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/users', [AdminController::class, 'getAllUsers']);
            Route::get('/properties', [AdminController::class, 'getAllProperties']);
            Route::put('/properties/{id}/status', [AdminController::class, 'updatePropertyStatus']);
            Route::get('/bookings', [AdminController::class, 'getAllBookings']);
            Route::get('/statistics', [AdminController::class, 'getStatistics']);
        });
    });
});
