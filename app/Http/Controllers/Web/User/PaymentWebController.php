<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\BookingExpiryService;
use App\Support\PaymentMethodCatalog;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PaymentWebController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected BookingExpiryService $bookingExpiryService
    ) {
    }

    public function create(Request $request): View|RedirectResponse
    {
        $bookingId = $request->integer('booking');

        $booking = Booking::query()
            ->with(['flight.airline', 'flight.departureAirport', 'flight.arrivalAirport', 'payments'])
            ->where('user_id', $request->user()->id)
            ->find($bookingId);

        if (! $booking) {
            return redirect()
                ->route('my-bookings.index')
                ->with('status', 'Booking tidak ditemukan.');
        }

        $booking = $this->bookingExpiryService->expireIfNeeded($booking);

        if ($booking->status !== 'pending' && $booking->status !== 'confirmed') {
            return redirect()
                ->route('my-bookings.show', $booking)
                ->with('status', 'Booking tidak lagi tersedia untuk pembayaran.');
        }

        return view('user.payments.create', [
            'booking' => $booking,
            'latestPayment' => $booking->payments->sortByDesc('created_at')->first(),
            'paymentMethods' => PaymentMethodCatalog::all(),
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $booking = Booking::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($request->integer('booking_id'));

        $booking = $this->bookingExpiryService->expireIfNeeded($booking);

        $payment = $this->paymentService->createPayment($booking, $request->validated());

        if ($payment->payment_method === 'qris') {
            return redirect()
                ->route('payments.qris.show', $payment)
                ->with('status', 'QRIS siap dipindai. Scan QR untuk menyelesaikan pembayaran demo.');
        }

        return redirect()
            ->route('payments.show', $payment)
            ->with('status', 'Pembayaran berhasil dikirim dengan status '.$payment->payment_status.'.');
    }

    public function show(Request $request, Payment $payment): View
    {
        $payment->load('booking.flight.departureAirport', 'booking.flight.arrivalAirport');
        abort_unless($payment->booking->user_id === $request->user()->id, 403);
        $payment->setRelation('booking', $this->bookingExpiryService->expireIfNeeded($payment->booking));

        return view('user.payments.show', [
            'payment' => $payment,
            'paymentLabel' => PaymentMethodCatalog::label($payment->payment_method),
            'paymentMethod' => PaymentMethodCatalog::all()[$payment->payment_method] ?? null,
            'qrisUrl' => $payment->payment_method === 'qris' && $payment->payment_status === 'pending' ? route('payments.qris.show', $payment) : null,
        ]);
    }

    public function showQris(Request $request, Payment $payment): View|RedirectResponse
    {
        $payment->load([
            'booking.flight.airline',
            'booking.flight.departureAirport',
            'booking.flight.arrivalAirport',
        ]);
        abort_unless($payment->booking->user_id === $request->user()->id, 403);

        $payment->setRelation('booking', $this->bookingExpiryService->expireIfNeeded($payment->booking));

        if ($payment->booking->status !== 'pending') {
            return redirect()
                ->route('my-bookings.show', $payment->booking)
                ->with('status', 'Sesi pembayaran QRIS sudah berakhir atau booking sudah tidak pending.');
        }

        if ($payment->payment_status === 'paid') {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Pembayaran QRIS sudah selesai diproses.');
        }

        $scanUrl = URL::temporarySignedRoute(
            'payments.qris.scan',
            now()->addHours(12),
            [
                'payment' => $payment->id,
                'amount' => (int) round((float) $payment->amount),
                'booking' => $payment->booking?->booking_code,
            ]
        );

        return view('user.payments.qris', [
            'payment' => $payment,
            'paymentLabel' => PaymentMethodCatalog::label($payment->payment_method),
            'scanUrl' => $scanUrl,
            'qrCode' => $this->buildQrCode($scanUrl),
        ]);
    }

    public function scanQris(Request $request, Payment $payment): View
    {
        abort_unless($request->hasValidSignature(), 403);

        $payment->load([
            'booking.user',
            'booking.flight.airline',
            'booking.flight.departureAirport',
            'booking.flight.arrivalAirport',
        ]);

        $payment = $this->paymentService->settlePayment($payment);
        $payment->load([
            'booking.user',
            'booking.flight.airline',
            'booking.flight.departureAirport',
            'booking.flight.arrivalAirport',
        ]);

        return view('user.payments.qris-scanned', [
            'payment' => $payment,
            'paymentLabel' => PaymentMethodCatalog::label($payment->payment_method),
        ]);
    }

    protected function buildQrCode(string $payload): string
    {
        $options = new QROptions([
            'outputBase64' => true,
            'scale' => 6,
        ]);

        return (new QRCode($options))->render($payload);
    }
}
