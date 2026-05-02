<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Services\BoardingPassService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookingCheckInWebController extends Controller
{
    public function __construct(
        protected BoardingPassService $boardingPassService
    ) {
    }

    public function index(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking->load([
            'flight.airline',
            'flight.departureAirport',
            'flight.arrivalAirport',
            'details.passenger',
            'details.seat',
            'details.ticket',
        ]);

        $checkInAvailability = [];
        foreach ($booking->details as $detail) {
            $checkInAvailability[$detail->id] = $this->boardingPassService->checkInAvailability($detail);
        }

        return view('user.bookings.checkin', [
            'booking' => $booking,
            'checkInAvailability' => $checkInAvailability,
        ]);
    }

    public function checkIn(Request $request, Booking $booking, BookingDetail $detail): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($detail->booking_id === $booking->id, 404);

        if (in_array($detail->boarding_status, ['checked_in', 'boarded'], true)) {
            return back()
                ->with('status', 'Passenger ini sudah check-in sebelumnya.')
                ->with('status_type', 'warning');
        }

        $this->boardingPassService->checkIn($detail);

        return back()
            ->with('status', 'Check-in passenger berhasil. Boarding pass sudah tersedia.')
            ->with('status_type', 'success');
    }

    public function downloadPdf(Request $request, Booking $booking, BookingDetail $detail): BinaryFileResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($detail->booking_id === $booking->id, 404);
        abort_unless(in_array($detail->boarding_status, ['checked_in', 'boarded'], true), 404);

        $detail = $this->boardingPassService->ensureAssetsGenerated($detail);
        abort_unless($detail->boarding_pass_pdf_path, 404);

        return response()->download(
            storage_path('app/public/'.$detail->boarding_pass_pdf_path),
            ($detail->ticket_number ?: 'boarding-pass').'-boarding-pass.pdf'
        );
    }

    public function qrCode(Request $request, Booking $booking, BookingDetail $detail): BinaryFileResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($detail->booking_id === $booking->id, 404);
        abort_unless(in_array($detail->boarding_status, ['checked_in', 'boarded'], true), 404);

        $detail = $this->boardingPassService->ensureAssetsGenerated($detail);
        abort_unless($detail->boarding_pass_qr_path, 404);

        return response()->file(
            storage_path('app/public/'.$detail->boarding_pass_qr_path),
            ['Content-Type' => 'image/svg+xml']
        );
    }
}
