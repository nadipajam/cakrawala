<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminTicketController extends Controller
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
                'bookingDetail.booking.flight',
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
            ->paginate(12)
            ->withQueryString();

        return view('admin.tickets.index', compact('tickets', 'search'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load([
            'bookingDetail.booking.user',
            'bookingDetail.booking.flight.airline',
            'bookingDetail.booking.flight.departureAirport',
            'bookingDetail.booking.flight.arrivalAirport',
            'bookingDetail.passenger',
            'bookingDetail.seat',
        ]);
        $ticket = $this->ticketService->ensureAssetsGenerated($ticket);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function regenerate(Ticket $ticket)
    {
        $booking = $ticket->bookingDetail->booking;
        $this->ticketService->issueForBooking($booking);

        return back()->with('status', 'Ticket berhasil diregenerate.');
    }

    public function downloadPdf(Ticket $ticket): BinaryFileResponse
    {
        $ticket = $this->ticketService->ensureAssetsGenerated($ticket);

        return response()->download(
            storage_path('app/public/'.$ticket->pdf_path),
            ($ticket->bookingDetail->ticket_number ?: 'ticket').'.pdf'
        );
    }

    public function qrCode(Ticket $ticket): BinaryFileResponse
    {
        $ticket = $this->ticketService->ensureAssetsGenerated($ticket);

        return response()->file(
            storage_path('app/public/'.$ticket->qr_code_path),
            ['Content-Type' => 'image/svg+xml']
        );
    }
}
