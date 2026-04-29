<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\AdminAnalyticsService;

class DashboardController extends Controller
{
    public function __construct(
        protected AdminAnalyticsService $analyticsService
    ) {
    }

    public function summary()
    {
        return $this->successResponse($this->analyticsService->summary(), 'Data dashboard berhasil diambil');
    }

    public function recentBookings()
    {
        $bookings = Booking::query()
            ->with(['user', 'flight.airline', 'flight.departureAirport', 'flight.arrivalAirport', 'payments'])
            ->latest()
            ->limit(10)
            ->get();

        return $this->successResponse($bookings, 'Data booking terbaru berhasil diambil');
    }

    public function recentPayments()
    {
        $payments = Payment::query()
            ->with(['booking.user', 'booking.flight.airline', 'booking.flight.departureAirport', 'booking.flight.arrivalAirport'])
            ->latest()
            ->limit(10)
            ->get();

        return $this->successResponse($payments, 'Data payment terbaru berhasil diambil');
    }
}
