<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\GuestController;
use App\Http\Controllers\Api\HostController;
use App\Http\Controllers\Api\ServiceProviderController;
use App\Http\Controllers\Api\ServiceCustomerController;
use App\Http\Controllers\Api\Admin\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public property and data routes
Route::get('/properties', [PropertyController::class, 'index']);
Route::get('/properties/{id}', [PropertyController::class, 'show']);
Route::get('/popular-destinations', [GuestController::class, 'getPopularDestinations']);
Route::get('/property-categories', [GuestController::class, 'getPropertyCategories']);
Route::get('/service-categories', [GuestController::class, 'getServiceCategories']);
Route::get('/featured-properties', [GuestController::class, 'getFeaturedProperties']);
Route::get('/search-properties', [GuestController::class, 'searchProperties']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });

    // Guest routes
    Route::prefix('guest')->middleware('role:guest,service_customer')->group(function () {
        Route::prefix('bookings')->group(function () {
            Route::get('/', [GuestController::class, 'getBookings']);
            Route::post('/', [GuestController::class, 'createBooking']);
            Route::get('/{id}', [GuestController::class, 'getBooking']);
            Route::put('/{id}/cancel', [GuestController::class, 'cancelBooking']);
        });

        Route::prefix('favorites')->group(function () {
            Route::get('/', [GuestController::class, 'getFavorites']);
            Route::post('/{propertyId}', [GuestController::class, 'addToFavorites']);
            Route::delete('/{propertyId}', [GuestController::class, 'removeFromFavorites']);
        });
    });

    // Host routes
    Route::prefix('host')->middleware('role:host')->group(function () {
        Route::prefix('properties')->group(function () {
            Route::get('/', [HostController::class, 'getProperties']);
            Route::post('/', [HostController::class, 'createProperty']);
            Route::get('/{id}', [HostController::class, 'getProperty']);
            Route::put('/{id}', [HostController::class, 'updateProperty']);
            Route::delete('/{id}', [HostController::class, 'deleteProperty']);
        });

        Route::prefix('bookings')->group(function () {
            Route::get('/', [HostController::class, 'getBookings']);
            Route::put('/{id}/status', [HostController::class, 'updateBookingStatus']);
        });

        Route::prefix('experiences')->group(function () {
            Route::get('/', [HostController::class, 'getExperiences']);
            Route::post('/', [HostController::class, 'createExperience']);
            Route::put('/{id}', [HostController::class, 'updateExperience']);
            Route::delete('/{id}', [HostController::class, 'deleteExperience']);
        });

        Route::get('/earnings', [HostController::class, 'getEarnings']);
        Route::get('/statistics', [HostController::class, 'getStatistics']);
    });

    // Service Provider routes (for Fixy app)
    Route::prefix('service-provider')->middleware('role:service_provider')->group(function () {
        Route::prefix('services')->group(function () {
            Route::get('/', [ServiceProviderController::class, 'getServices']);
            Route::post('/', [ServiceProviderController::class, 'createService']);
            Route::put('/{id}', [ServiceProviderController::class, 'updateService']);
            Route::delete('/{id}', [ServiceProviderController::class, 'deleteService']);
        });

        Route::prefix('appointments')->group(function () {
            Route::get('/', [ServiceProviderController::class, 'getAppointments']);
            Route::put('/{id}/status', [ServiceProviderController::class, 'updateAppointmentStatus']);
        });

        Route::get('/earnings', [ServiceProviderController::class, 'getEarnings']);
        Route::get('/statistics', [ServiceProviderController::class, 'getStatistics']);
        Route::put('/availability', [ServiceProviderController::class, 'updateAvailability']);
    });

    // Service Customer routes (for Fixy app)
    Route::prefix('service-customer')->middleware('role:service_customer,guest')->group(function () {
        Route::get('/service-providers', [ServiceCustomerController::class, 'getServiceProviders']);
        Route::get('/service-providers/{id}', [ServiceCustomerController::class, 'getServiceProvider']);

        Route::prefix('appointments')->group(function () {
            Route::get('/', [ServiceCustomerController::class, 'getAppointments']);
            Route::post('/', [ServiceCustomerController::class, 'bookAppointment']);
            Route::get('/{id}', [ServiceCustomerController::class, 'getAppointment']);
            Route::put('/{id}/cancel', [ServiceCustomerController::class, 'cancelAppointment']);
        });
    });

    // Admin routes
    Route::prefix('admin')->middleware('role:admin')->group(function () {

        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminController::class, 'getUsers']);
            Route::post('/', [AdminController::class, 'createUser']);
            Route::put('/{id}', [AdminController::class, 'updateUser']);
            Route::delete('/{id}', [AdminController::class, 'deleteUser']);
        });

        // Property Management
        Route::prefix('properties')->group(function () {
            Route::get('/', [AdminController::class, 'getAllProperties']);
            Route::put('/{id}/status', [AdminController::class, 'updatePropertyStatus']);
            Route::delete('/{id}', [AdminController::class, 'deleteProperty']);
        });

        // Booking Management
        Route::prefix('bookings')->group(function () {
            Route::get('/', [AdminController::class, 'getAllBookings']);
            Route::put('/{id}/status', [AdminController::class, 'updateBookingStatus']);
        });

        // Experience Management
        Route::prefix('experiences')->group(function () {
            Route::get('/', [AdminController::class, 'getAllExperiences']);
            Route::put('/{id}/status', [AdminController::class, 'updateExperienceStatus']);
            Route::delete('/{id}', [AdminController::class, 'deleteExperience']);
        });

        // Service Provider Management
        Route::prefix('service-providers')->group(function () {
            Route::get('/', [AdminController::class, 'getAllServiceProviders']);
            Route::put('/{id}/status', [AdminController::class, 'updateServiceProviderStatus']);
        });

        // Service Appointment Management
        Route::prefix('service-appointments')->group(function () {
            Route::get('/', [AdminController::class, 'getAllServiceAppointments']);
        });

        // Payment Management
        Route::prefix('payments')->group(function () {
            Route::get('/', [AdminController::class, 'getAllPayments']);
        });

        // Statistics
        Route::get('/statistics', [AdminController::class, 'getStatistics']);
    });
});

// Fallback route for unmatched API requests
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API route not found'
    ], 404);
});
