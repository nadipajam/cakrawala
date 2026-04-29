<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Ticket;
use App\Support\CabinClass;
use chillerlan\QRCode\Output\QRMarkupSVG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TicketService
{
    public function __construct(
        protected PortalNotificationService $notificationService
    ) {
    }

    public function issueForBooking(Booking $booking): Booking
    {
        $booking->loadMissing([
            'details.ticket',
            'details.passenger',
            'details.seat',
            'flight.airline',
            'flight.departureAirport',
            'flight.arrivalAirport',
        ]);
        $hadExistingTickets = $booking->details->every(fn ($detail) => $detail->ticket !== null);

        foreach ($booking->details as $detail) {
            if (! $detail->ticket_number) {
                $detail->update([
                    'ticket_number' => $this->generateTicketNumber($booking->booking_code, $detail->id),
                ]);
            }

            $ticket = Ticket::updateOrCreate(
                ['booking_detail_id' => $detail->id],
                ['issued_at' => now()]
            );

            $this->generateAssets($ticket->fresh([
                'bookingDetail.booking.flight.airline',
                'bookingDetail.booking.flight.departureAirport',
                'bookingDetail.booking.flight.arrivalAirport',
                'bookingDetail.passenger',
                'bookingDetail.seat',
            ]));
        }

        $booking = $booking->fresh(['user', 'details.ticket']);

        if (! $hadExistingTickets) {
            $this->notificationService->ticketsIssued($booking);
        }

        return $booking;
    }

    public function generateTicketNumber(string $bookingCode, int $detailId): string
    {
        return 'TK-' . preg_replace('/[^A-Z0-9]/', '', strtoupper($bookingCode)) . '-' . str_pad((string) $detailId, 4, '0', STR_PAD_LEFT);
    }

    public function ensureAssetsGenerated(Ticket $ticket): Ticket
    {
        $ticket->loadMissing([
            'bookingDetail.booking.flight.airline',
            'bookingDetail.booking.flight.departureAirport',
            'bookingDetail.booking.flight.arrivalAirport',
            'bookingDetail.passenger',
            'bookingDetail.seat',
        ]);

        $qrExists = $ticket->qr_code_path && Storage::disk('public')->exists($ticket->qr_code_path);
        $pdfExists = $ticket->pdf_path && Storage::disk('public')->exists($ticket->pdf_path);

        if ($qrExists && $pdfExists) {
            return $ticket;
        }

        return $this->generateAssets($ticket);
    }

    protected function generateAssets(Ticket $ticket): Ticket
    {
        $detail = $ticket->bookingDetail;
        $booking = $detail->booking;
        $directory = $this->ticketDirectory($booking->booking_code, $detail->id);
        $qrPath = $directory.'/qr.svg';
        $pdfPath = $directory.'/ticket.pdf';
        $qrMarkup = $this->buildQrMarkup($this->qrPayload($ticket));

        Storage::disk('public')->put($qrPath, $qrMarkup);
        Storage::disk('public')->put($pdfPath, $this->buildPdf($ticket, $qrMarkup));

        $ticket->forceFill([
            'qr_code_path' => $qrPath,
            'pdf_path' => $pdfPath,
            'issued_at' => $ticket->issued_at ?? now(),
        ])->save();

        return $ticket->fresh();
    }

    protected function buildQrMarkup(string $payload): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64' => false,
            'imageTransparent' => false,
            'scale' => 6,
        ]);

        return (new QRCode($options))->render($payload);
    }

    protected function buildPdf(Ticket $ticket, string $qrMarkup): string
    {
        $options = new Options([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('pdf.ticket', [
            'ticket' => $ticket,
            'detail' => $ticket->bookingDetail,
            'booking' => $ticket->bookingDetail->booking,
            'flight' => $ticket->bookingDetail->booking->flight,
            'qrMarkup' => $qrMarkup,
            'cabinLabel' => CabinClass::shortLabel($ticket->bookingDetail->seat?->class),
        ])->render());
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }

    protected function qrPayload(Ticket $ticket): string
    {
        $detail = $ticket->bookingDetail;
        $booking = $detail->booking;
        $flight = $booking->flight;

        return implode("\n", array_filter([
            'Cakrawala E-Ticket',
            'Ticket: '.$detail->ticket_number,
            'Booking: '.$booking->booking_code,
            'Passenger: '.$detail->passenger?->full_name,
            'Seat: '.trim(($detail->seat?->seat_number ?? '-').' / '.CabinClass::shortLabel($detail->seat?->class)),
            'Flight: '.$flight?->flight_number,
            'Route: '.($flight?->departureAirport?->code ?? '-').' -> '.($flight?->arrivalAirport?->code ?? '-'),
            'Departure: '.optional($flight?->departure_time)->format('d M Y H:i'),
        ]));
    }

    protected function ticketDirectory(string $bookingCode, int $detailId): string
    {
        return 'tickets/'.Str::slug(Str::lower($bookingCode), '-').'/'.$detailId;
    }
}
