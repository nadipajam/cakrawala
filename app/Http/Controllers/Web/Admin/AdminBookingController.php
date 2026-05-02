<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Boarding\UpdateBoardingStatusRequest;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Services\BoardingPassService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminBookingController extends Controller
{
    public function __construct(
        protected BoardingPassService $boardingPassService
    ) {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $date = trim((string) $request->string('date'));
        $user = trim((string) $request->string('user'));
        $flight = trim((string) $request->string('flight'));
        $paymentStatus = trim((string) $request->string('payment_status'));

        $bookings = Booking::query()
            ->with(['user', 'flight.airline', 'flight.departureAirport', 'flight.arrivalAirport', 'payments'])
            ->when($search !== '', fn ($query) => $query->where('booking_code', 'like', "%{$search}%"))
            ->when(in_array($status, ['pending', 'confirmed', 'cancelled', 'completed'], true), fn ($query) => $query->where('status', $status))
            ->when($date !== '', fn ($query) => $query->whereDate('created_at', $date))
            ->when($user !== '', fn ($query) => $query->whereHas('user', fn ($qq) => $qq->where('name', 'like', "%{$user}%")))
            ->when($flight !== '', fn ($query) => $query->whereHas('flight', fn ($qq) => $qq->where('flight_number', 'like', "%{$flight}%")))
            ->when(in_array($paymentStatus, ['pending', 'paid', 'failed', 'refunded'], true), fn ($query) => $query->whereHas('payments', fn ($qq) => $qq->where('payment_status', $paymentStatus)))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.bookings.index', compact('bookings', 'search', 'status', 'date', 'user', 'flight', 'paymentStatus'));
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
            'details.addons',
            'addons.bookingDetail.passenger',
            'changeRequests.user',
            'changeRequests.processedByUser',
            'changeRequests.preferredFlight.departureAirport',
            'changeRequests.preferredFlight.arrivalAirport',
            'payments',
        ]);

        return view('admin.bookings.show', compact('booking'));
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,confirmed,cancelled,completed'],
        ]);

        if ($booking->status === $data['status']) {
            return back()
                ->with('status', 'Status booking sudah sama, tidak ada perubahan.')
                ->with('status_type', 'warning');
        }

        $booking->update(['status' => $data['status']]);

        return back()
            ->with('status', 'Status booking berhasil diperbarui.')
            ->with('status_type', 'success');
    }

    public function cancel(Booking $booking)
    {
        if ($booking->status === 'cancelled') {
            return back()
                ->with('status', 'Booking ini sudah dibatalkan sebelumnya.')
                ->with('status_type', 'warning');
        }

        $booking->update([
            'status' => 'cancelled',
            'expired_at' => now(),
        ]);

        $booking->payments()
            ->where('payment_status', 'pending')
            ->update(['payment_status' => 'failed']);

        $booking->addons()
            ->whereIn('status', ['selected', 'paid'])
            ->update(['status' => 'cancelled']);

        return back()
            ->with('status', 'Booking berhasil dibatalkan.')
            ->with('status_type', 'success');
    }

    public function updateBoardingStatus(UpdateBoardingStatusRequest $request, Booking $booking, BookingDetail $detail)
    {
        abort_unless($detail->booking_id === $booking->id, 404);

        $targetStatus = $request->validated('boarding_status');
        if ($detail->boarding_status === $targetStatus) {
            return back()
                ->with('status', 'Status boarding sudah sama, tidak ada perubahan.')
                ->with('status_type', 'warning');
        }

        $this->boardingPassService->updateBoardingStatus($detail, $targetStatus);

        return back()
            ->with('status', 'Status boarding passenger berhasil diperbarui.')
            ->with('status_type', 'success');
    }

    public function downloadBoardingPassPdf(Booking $booking, BookingDetail $detail): BinaryFileResponse
    {
        abort_unless($detail->booking_id === $booking->id, 404);
        abort_unless(in_array($detail->boarding_status, ['checked_in', 'boarded'], true), 404);

        $detail = $this->boardingPassService->ensureAssetsGenerated($detail);
        abort_unless($detail->boarding_pass_pdf_path, 404);

        return response()->download(
            storage_path('app/public/'.$detail->boarding_pass_pdf_path),
            ($detail->ticket_number ?: 'boarding-pass').'-boarding-pass.pdf'
        );
    }

    public function boardingPassQr(Booking $booking, BookingDetail $detail): BinaryFileResponse
    {
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
