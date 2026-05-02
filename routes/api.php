<?php

use App\Http\Controllers\Api\AirlineController;
use App\Http\Controllers\Api\AirplaneController;
use App\Http\Controllers\Api\AirportController;
use App\Http\Controllers\Api\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\PassengerController as AdminPassengerController;
use App\Http\Controllers\Api\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Api\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\Admin\SeatController as AdminSeatController;
use App\Http\Controllers\Api\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\FlightController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\PassengerController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.')->group(function () {
    Route::post('/payments/midtrans/notification', MidtransWebhookController::class)->name('payments.midtrans.notification');

    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/airports', [AirportController::class, 'index']);
    Route::get('/airlines', [AirlineController::class, 'index']);
    Route::get('/airplanes', [AirplaneController::class, 'index']);
    Route::get('/flights', [FlightController::class, 'index']);
    Route::get('/flights/{flight}', [FlightController::class, 'show']);
    Route::get('/flights/{flight}/available-seats', [FlightController::class, 'availableSeats']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::put('/profile', [ProfileController::class, 'update']);

        Route::apiResource('passengers', PassengerController::class);

        Route::get('/my-bookings', [BookingController::class, 'myBookings']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);

        Route::middleware('role:admin,staff,manager')->prefix('admin')->group(function () {
            Route::get('/dashboard/summary', [AdminDashboardController::class, 'summary']);
            Route::get('/dashboard/recent-bookings', [AdminDashboardController::class, 'recentBookings']);
            Route::get('/dashboard/recent-payments', [AdminDashboardController::class, 'recentPayments']);

            Route::middleware('role:admin,manager')->group(function () {
                Route::get('/users', [AdminUserController::class, 'index']);
                Route::get('/users/{user}', [AdminUserController::class, 'show']);
                Route::get('/users/{user}/bookings', [AdminUserController::class, 'bookings']);
                Route::get('/users/{user}/passengers', [AdminUserController::class, 'passengers']);

                Route::get('/reports/bookings', [AdminReportController::class, 'bookings']);
                Route::get('/reports/payments', [AdminReportController::class, 'payments']);
                Route::get('/reports/revenue', [AdminReportController::class, 'revenue']);
                Route::get('/reports/popular-routes', [AdminReportController::class, 'popularRoutes']);
            });

            Route::get('/passengers', [AdminPassengerController::class, 'index']);
            Route::get('/passengers/{passenger}', [AdminPassengerController::class, 'show']);

            Route::get('/airports', [AirportController::class, 'index']);
            Route::get('/airports/{airport}', [AirportController::class, 'show']);

            Route::get('/airlines', [AirlineController::class, 'index']);
            Route::get('/airlines/{airline}', [AirlineController::class, 'show']);

            Route::get('/airplanes', [AirplaneController::class, 'index']);
            Route::get('/airplanes/{airplane}', [AirplaneController::class, 'show']);

            Route::get('/seats', [AdminSeatController::class, 'index']);
            Route::get('/seats/{seat}', [AdminSeatController::class, 'show']);

            Route::get('/flights', [FlightController::class, 'index']);
            Route::get('/flights/{flight}', [FlightController::class, 'show']);

            Route::get('/bookings', [AdminBookingController::class, 'index']);
            Route::get('/bookings/{booking}', [AdminBookingController::class, 'show']);

            Route::get('/payments', [PaymentController::class, 'adminIndex']);
            Route::get('/payments/{payment}', [PaymentController::class, 'show']);

            Route::get('/tickets', [AdminTicketController::class, 'index']);
            Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show']);

            Route::get('/profile', [AdminProfileController::class, 'show']);
            Route::put('/profile', [AdminProfileController::class, 'update']);
            Route::put('/profile/password', [AdminProfileController::class, 'updatePassword']);

            Route::middleware('role:admin,staff')->group(function () {
                Route::patch('/flights/{flight}/status', [FlightController::class, 'updateStatus']);
                Route::patch('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus']);
                Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel']);
                Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify']);
                Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject']);
                Route::post('/tickets/{ticket}/regenerate', [AdminTicketController::class, 'regenerate']);
            });

            Route::middleware('role:admin')->group(function () {
                Route::post('/airports', [AirportController::class, 'store']);
                Route::put('/airports/{airport}', [AirportController::class, 'update']);
                Route::delete('/airports/{airport}', [AirportController::class, 'destroy']);

                Route::post('/airlines', [AirlineController::class, 'store']);
                Route::put('/airlines/{airline}', [AirlineController::class, 'update']);
                Route::delete('/airlines/{airline}', [AirlineController::class, 'destroy']);

                Route::post('/airplanes', [AirplaneController::class, 'store']);
                Route::put('/airplanes/{airplane}', [AirplaneController::class, 'update']);
                Route::delete('/airplanes/{airplane}', [AirplaneController::class, 'destroy']);
                Route::post('/airplanes/{airplane}/generate-seats', [AirplaneController::class, 'generateSeats']);

                Route::post('/seats', [AdminSeatController::class, 'store']);
                Route::put('/seats/{seat}', [AdminSeatController::class, 'update']);
                Route::delete('/seats/{seat}', [AdminSeatController::class, 'destroy']);

                Route::post('/flights', [FlightController::class, 'store']);
                Route::put('/flights/{flight}', [FlightController::class, 'update']);
                Route::delete('/flights/{flight}', [FlightController::class, 'destroy']);
            });
        });
    });
});
