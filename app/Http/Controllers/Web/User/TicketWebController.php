<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class TicketWebController extends Controller
{
    public function __construct(
        protected TicketService $ticketService,
    ) {
    }

    public function show(Request $request, Ticket $ticket): View
    {
        $ticket->load([
            'bookingDetail.booking.user',
            'bookingDetail.booking.flight.airline',
            'bookingDetail.booking.flight.departureAirport',
            'bookingDetail.booking.flight.arrivalAirport',
            'bookingDetail.passenger',
            'bookingDetail.seat',
            'bookingDetail.booking.details.ticket',
            'bookingDetail.booking.details.passenger',
            'bookingDetail.booking.details.seat',
        ]);

        abort_unless($ticket->bookingDetail->booking->user_id === $request->user()->id, 403);

        $booking = $ticket->bookingDetail->booking;
        $ticketDetails = $this->prepareTicketDetails($booking);
        $detail = $ticketDetails->firstWhere('ticket.id', $ticket->id) ?? $ticketDetails->firstWhere('id', $ticket->booking_detail_id);

        abort_unless($detail !== null, 404);

        return view('user.tickets.show', [
            'ticket' => $detail->ticket,
            'detail' => $detail,
            'booking' => $booking,
            'flight' => $booking->flight,
            'relatedTicketDetails' => $ticketDetails,
        ]);
    }

    public function showBooking(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking->load([
            'flight.airline',
            'flight.departureAirport',
            'flight.arrivalAirport',
            'details.ticket',
            'details.passenger',
            'details.seat',
        ]);

        $ticketDetails = $this->prepareTicketDetails($booking);

        abort_if($ticketDetails->isEmpty(), 404);

        return view('user.tickets.index', [
            'booking' => $booking,
            'flight' => $booking->flight,
            'ticketDetails' => $ticketDetails,
        ]);
    }

    public function downloadAllPdfs(Request $request, Booking $booking): BinaryFileResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking->load([
            'details.ticket',
            'details.passenger',
            'details.seat',
        ]);

        $ticketDetails = $this->prepareTicketDetails($booking);

        abort_if($ticketDetails->isEmpty(), 404);
        abort_unless(class_exists(ZipArchive::class), 500, 'ZIP extension is not available.');

        $temporaryDirectory = storage_path('app/temp');
        File::ensureDirectoryExists($temporaryDirectory);

        $zipFilePath = $temporaryDirectory.'/'.Str::slug($booking->booking_code, '-').'-tickets-'.now()->format('YmdHis').'.zip';
        $zip = new ZipArchive();

        abort_unless($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'Unable to build ZIP file.');

        foreach ($ticketDetails as $detail) {
            $ticket = $detail->ticket;
            $pdfPath = storage_path('app/public/'.$ticket->pdf_path);

            if (! is_file($pdfPath)) {
                continue;
            }

            $zip->addFile($pdfPath, $this->zipFilenameForDetail($detail));
        }

        $zip->close();

        return response()->download(
            $zipFilePath,
            Str::slug($booking->booking_code, '-').'-tickets.zip'
        )->deleteFileAfterSend(true);
    }

    public function downloadPdf(Request $request, Ticket $ticket): BinaryFileResponse
    {
        $this->authorizeTicket($request, $ticket);
        $ticket = $this->ticketService->ensureAssetsGenerated($ticket);

        return response()->download(
            storage_path('app/public/'.$ticket->pdf_path),
            ($ticket->bookingDetail->ticket_number ?: 'ticket').'.pdf'
        );
    }

    public function qrCode(Request $request, Ticket $ticket): BinaryFileResponse
    {
        $this->authorizeTicket($request, $ticket);
        $ticket = $this->ticketService->ensureAssetsGenerated($ticket);

        return response()->file(
            storage_path('app/public/'.$ticket->qr_code_path),
            ['Content-Type' => 'image/svg+xml']
        );
    }

    protected function authorizeTicket(Request $request, Ticket $ticket): void
    {
        $ticket->loadMissing('bookingDetail.booking.user');

        abort_unless($ticket->bookingDetail->booking->user_id === $request->user()->id, 403);
    }

    /**
     * @return Collection<int, mixed>
     */
    protected function prepareTicketDetails(Booking $booking): Collection
    {
        return $booking->details
            ->filter(fn ($detail) => $detail->ticket !== null)
            ->values()
            ->map(function ($detail) {
                $detail->setRelation('ticket', $this->ticketService->ensureAssetsGenerated($detail->ticket));

                return $detail;
            });
    }

    protected function zipFilenameForDetail(mixed $detail): string
    {
        $ticketNumber = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) ($detail->ticket_number ?: 'ticket'));
        $passenger = preg_replace('/[^A-Za-z0-9._-]/', '_', Str::slug((string) ($detail->passenger?->full_name ?: 'passenger'), '-'));

        return trim($ticketNumber.'-'.$passenger, '-').'.pdf';
    }
}
