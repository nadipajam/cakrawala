<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        protected TicketService $ticketService
    ) {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));

        $tickets = Ticket::query()
            ->with([
                'bookingDetail.booking.flight.airline',
                'bookingDetail.booking.flight.departureAirport',
                'bookingDetail.booking.flight.arrivalAirport',
                'bookingDetail.booking.user',
                'bookingDetail.passenger',
                'bookingDetail.seat',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('bookingDetail', function ($qq) use ($search) {
                    $qq->where('ticket_number', 'like', "%{$search}%")
                        ->orWhereHas('booking', fn ($q) => $q->where('booking_code', 'like', "%{$search}%"))
                        ->orWhereHas('passenger', fn ($q) => $q->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('booking.flight', fn ($q) => $q->where('flight_number', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse($tickets, 'Data ticket berhasil diambil');
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'bookingDetail.booking.flight.airline',
            'bookingDetail.booking.flight.departureAirport',
            'bookingDetail.booking.flight.arrivalAirport',
            'bookingDetail.booking.user',
            'bookingDetail.passenger',
            'bookingDetail.seat',
        ]);

        return $this->successResponse($ticket, 'Detail ticket berhasil diambil');
    }

    public function regenerate(Ticket $ticket)
    {
        $booking = $ticket->bookingDetail->booking;
        $this->ticketService->issueForBooking($booking);

        return $this->successResponse($ticket->fresh('bookingDetail.booking'), 'Ticket berhasil diregenerate');
    }
}
