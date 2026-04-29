<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $paymentStatus = trim((string) $request->string('payment_status'));
        $date = trim((string) $request->string('date'));

        $bookings = Booking::query()
            ->with([
                'user',
                'flight.airline',
                'flight.departureAirport',
                'flight.arrivalAirport',
                'details.passenger',
                'details.seat',
                'details.ticket',
                'payments',
            ])
            ->when($search !== '', fn ($query) => $query->where('booking_code', 'like', "%{$search}%"))
            ->when(in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true), fn ($query) => $query->where('status', $status))
            ->when(in_array($paymentStatus, ['pending', 'paid', 'failed', 'refunded'], true), fn ($query) => $query->whereHas('payments', fn ($qq) => $qq->where('payment_status', $paymentStatus)))
            ->when($date !== '', fn ($query) => $query->whereDate('created_at', $date))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse($bookings, 'Data booking berhasil diambil');
    }

    public function show(Booking $booking)
    {
        $booking->load([
            'user',
            'flight.airline',
            'flight.airplane',
            'flight.departureAirport',
            'flight.arrivalAirport',
            'details.passenger',
            'details.seat',
            'details.ticket',
            'payments',
        ]);

        return $this->successResponse($booking, 'Detail booking berhasil diambil');
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        $booking->update(['status' => $data['status']]);

        return $this->successResponse($booking->fresh(), 'Status booking berhasil diperbarui');
    }

    public function cancel(Booking $booking)
    {
        $booking->update([
            'status' => 'cancelled',
            'expired_at' => now(),
        ]);

        $booking->payments()
            ->where('payment_status', 'pending')
            ->update(['payment_status' => 'failed']);

        return $this->successResponse($booking->fresh('payments'), 'Booking berhasil dibatalkan');
    }
}
