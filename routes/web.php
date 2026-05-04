<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\Admin\AdminAirlineController;
use App\Http\Controllers\Web\Admin\AdminAirplaneController;
use App\Http\Controllers\Web\Admin\AdminAddonController;
use App\Http\Controllers\Web\Admin\AdminAirportController;
use App\Http\Controllers\Web\Admin\AdminBookingController;
use App\Http\Controllers\Web\Admin\AdminChangeRequestController;
use App\Http\Controllers\Web\Admin\AdminContactMessageController;
use App\Http\Controllers\Web\Admin\AdminDashboardController;
use App\Http\Controllers\Web\Admin\AdminFlightController;
use App\Http\Controllers\Web\Admin\AdminPassengerController as AdminPassengerWebController;
use App\Http\Controllers\Web\Admin\AdminPaymentController;
use App\Http\Controllers\Web\Admin\AdminProfileController;
use App\Http\Controllers\Web\Admin\AdminReportController;
use App\Http\Controllers\Web\Admin\AdminSeatController;
use App\Http\Controllers\Web\Admin\AdminTicketController;
use App\Http\Controllers\Web\Admin\AdminUserController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\User\BookingAddonWebController;
use App\Http\Controllers\Web\User\BookingChangeRequestWebController;
use App\Http\Controllers\Web\User\BookingCheckInWebController;
use App\Http\Controllers\Web\User\BookingWebController;
use App\Http\Controllers\Web\User\NotificationWebController;
use App\Http\Controllers\Web\User\PassengerWebController;
use App\Http\Controllers\Web\User\PaymentWebController;
use App\Http\Controllers\Web\User\FlightSearchController;
use App\Http\Controllers\Web\User\TicketWebController;
use App\Http\Controllers\Web\User\UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FlightSearchController::class, 'index'])->name('home');
Route::get('/home', [FlightSearchController::class, 'index'])->name('home.index');
Route::get('/flights', [FlightSearchController::class, 'results'])->name('flights.index');
Route::get('/flights/search', [FlightSearchController::class, 'search'])->name('flights.search');
Route::get('/flights/{flight}', [FlightSearchController::class, 'show'])->name('flights.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');
Route::get('/payments/{payment}/qris/scan', [PaymentWebController::class, 'scanQris'])
    ->middleware('signed')
    ->name('payments.qris.scan');
Route::get('/payments/midtrans/finish', [PaymentWebController::class, 'midtransFinish'])->name('payments.midtrans.finish');

Route::get('/dashboard', function () {
    return auth()->user()->isBackoffice()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('user.dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::middleware('role:customer,user')->group(function () {
        Route::get('/user/dashboard', [UserDashboardController::class, 'index'])->name('user.dashboard');
        Route::get('/booking', [BookingWebController::class, 'create'])->name('booking.create');
        Route::post('/booking', [BookingWebController::class, 'store'])->name('booking.store');
        Route::post('/bookings', [BookingWebController::class, 'store'])->name('bookings.store');
        Route::get('/my-bookings', [BookingWebController::class, 'index'])->name('my-bookings.index');
        Route::get('/my-bookings/change-requests', [BookingChangeRequestWebController::class, 'index'])->name('my-bookings.change-requests.index');
        Route::post('/my-bookings/change-requests', [BookingChangeRequestWebController::class, 'store'])->name('my-bookings.change-requests.store');
        Route::get('/my-bookings/{booking}', [BookingWebController::class, 'show'])->name('my-bookings.show');
        Route::get('/my-bookings/{booking}/check-in', [BookingCheckInWebController::class, 'index'])->name('my-bookings.checkin.index');
        Route::post('/my-bookings/{booking}/check-in/{detail}', [BookingCheckInWebController::class, 'checkIn'])->name('my-bookings.checkin.store');
        Route::get('/my-bookings/{booking}/check-in/{detail}/boarding-pass/pdf', [BookingCheckInWebController::class, 'downloadPdf'])->name('my-bookings.checkin.pdf');
        Route::get('/my-bookings/{booking}/check-in/{detail}/boarding-pass/qr', [BookingCheckInWebController::class, 'qrCode'])->name('my-bookings.checkin.qr');
        Route::get('/my-bookings/{booking}/addons', [BookingAddonWebController::class, 'index'])->name('my-bookings.addons.index');
        Route::post('/my-bookings/{booking}/addons', [BookingAddonWebController::class, 'store'])->name('my-bookings.addons.store');
        Route::delete('/my-bookings/{booking}/addons/{addon}', [BookingAddonWebController::class, 'destroy'])->name('my-bookings.addons.destroy');
        Route::get('/my-bookings/{booking}/tickets', [TicketWebController::class, 'showBooking'])->name('my-bookings.tickets');
        Route::get('/my-bookings/{booking}/tickets/download-all', [TicketWebController::class, 'downloadAllPdfs'])->name('my-bookings.tickets.download-all');
        Route::post('/my-bookings/{booking}/cancel', [BookingWebController::class, 'cancel'])->name('my-bookings.cancel');

        Route::get('/payment', [PaymentWebController::class, 'create'])->name('payments.create');
        Route::post('/payment', [PaymentWebController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}/proof', [PaymentWebController::class, 'proof'])->name('payments.proof');
        Route::get('/payments/{payment}', [PaymentWebController::class, 'show'])->name('payments.show');
        Route::post('/payments/{payment}/midtrans/refresh', [PaymentWebController::class, 'refreshMidtransStatus'])->name('payments.midtrans.refresh');
        Route::post('/payments/{payment}/midtrans/simulate', [PaymentWebController::class, 'simulateMidtransStatus'])->name('payments.midtrans.simulate');
        Route::get('/payments/{payment}/qris', [PaymentWebController::class, 'showQris'])->name('payments.qris.show');

        Route::get('/passengers', [PassengerWebController::class, 'index'])->name('passengers.index');
        Route::post('/passengers', [PassengerWebController::class, 'store'])->name('passengers.store');
        Route::put('/passengers/{passenger}', [PassengerWebController::class, 'update'])->name('passengers.update');
        Route::delete('/passengers/{passenger}', [PassengerWebController::class, 'destroy'])->name('passengers.destroy');

        Route::get('/tickets/{ticket}', [TicketWebController::class, 'show'])->name('tickets.show');
        Route::get('/tickets/{ticket}/pdf', [TicketWebController::class, 'downloadPdf'])->name('tickets.pdf');
        Route::get('/tickets/{ticket}/qr', [TicketWebController::class, 'qrCode'])->name('tickets.qr');
        Route::get('/notifications', [NotificationWebController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationWebController::class, 'markAllRead'])->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationWebController::class, 'markRead'])->name('notifications.read');
    });

    Route::middleware('role:admin,staff,manager')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/contact-messages', [AdminContactMessageController::class, 'index'])->name('contact-messages.index');
        Route::get('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'show'])->name('contact-messages.show');
        Route::patch('/contact-messages/{contactMessage}', [AdminContactMessageController::class, 'update'])->name('contact-messages.update');

        Route::middleware('role:admin,manager')->group(function () {
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/{user}', [AdminUserController::class, 'show'])->whereNumber('user')->name('users.show');
            Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        });

        Route::middleware('role:admin')->group(function () {
            Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->whereNumber('user')->name('users.edit');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->whereNumber('user')->name('users.update');
        });

        Route::get('/passengers', [AdminPassengerWebController::class, 'index'])->name('passengers.index');
        Route::get('/passengers/{passenger}', [AdminPassengerWebController::class, 'show'])->name('passengers.show');

        Route::middleware('role:admin')->group(function () {
            Route::resource('airports', AdminAirportController::class);
            Route::resource('airlines', AdminAirlineController::class);
            Route::resource('airplanes', AdminAirplaneController::class);
            Route::post('/airplanes/{airplane}/generate-seats', [AdminAirplaneController::class, 'generateSeats'])->name('airplanes.generate-seats');

            Route::resource('seats', AdminSeatController::class);

            Route::get('/flights/create', [AdminFlightController::class, 'create'])->name('flights.create');
            Route::post('/flights', [AdminFlightController::class, 'store'])->name('flights.store');
            Route::get('/flights/{flight}/edit', [AdminFlightController::class, 'edit'])->name('flights.edit');
            Route::put('/flights/{flight}', [AdminFlightController::class, 'update'])->name('flights.update');
            Route::delete('/flights/{flight}', [AdminFlightController::class, 'destroy'])->name('flights.destroy');
        });

        Route::get('/flights', [AdminFlightController::class, 'index'])->name('flights.index');
        Route::get('/flights/{flight}', [AdminFlightController::class, 'show'])->name('flights.show');

        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
        Route::get('/bookings/{booking}/details/{detail}/boarding-pass/pdf', [AdminBookingController::class, 'downloadBoardingPassPdf'])->name('bookings.boarding-pass.pdf');
        Route::get('/bookings/{booking}/details/{detail}/boarding-pass/qr', [AdminBookingController::class, 'boardingPassQr'])->name('bookings.boarding-pass.qr');

        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}/proof', [AdminPaymentController::class, 'proof'])->name('payments.proof');
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');

        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [AdminTicketController::class, 'show'])->name('tickets.show');
        Route::get('/tickets/{ticket}/pdf', [AdminTicketController::class, 'downloadPdf'])->name('tickets.pdf');
        Route::get('/tickets/{ticket}/qr', [AdminTicketController::class, 'qrCode'])->name('tickets.qr');

        Route::middleware('role:admin,staff')->group(function () {
            Route::patch('/flights/{flight}/status', [AdminFlightController::class, 'updateStatus'])->name('flights.status');
            Route::patch('/bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.status');
            Route::post('/bookings/{booking}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
            Route::patch('/bookings/{booking}/details/{detail}/boarding-status', [AdminBookingController::class, 'updateBoardingStatus'])->name('bookings.boarding-status');
            Route::post('/payments/{payment}/midtrans/refresh', [AdminPaymentController::class, 'refreshMidtransStatus'])->name('payments.midtrans.refresh');
            Route::post('/payments/{payment}/verify', [AdminPaymentController::class, 'verify'])->name('payments.verify');
            Route::post('/payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');
            Route::patch('/addons/{addon}/status', [AdminAddonController::class, 'updateStatus'])->name('addons.status');
            Route::post('/tickets/{ticket}/regenerate', [AdminTicketController::class, 'regenerate'])->name('tickets.regenerate');
        });

        Route::get('/profile', [AdminProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password');
        Route::get('/addons', [AdminAddonController::class, 'index'])->name('addons.index');
        Route::get('/change-requests', [AdminChangeRequestController::class, 'index'])->name('change-requests.index');
        Route::get('/change-requests/{changeRequest}', [AdminChangeRequestController::class, 'show'])->name('change-requests.show');
        Route::patch('/change-requests/{changeRequest}', [AdminChangeRequestController::class, 'update'])->name('change-requests.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
