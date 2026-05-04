<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Services\PaymentService;
use App\Services\BookingExpiryService;
use App\Support\PaymentMethodCatalog;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentWebController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected BookingExpiryService $bookingExpiryService,
        protected MidtransService $midtransService
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
                ->with('status', 'Booking tidak ditemukan.')
                ->with('status_type', 'warning');
        }

        $booking = $this->bookingExpiryService->expireIfNeeded($booking);

        if ($booking->status !== 'pending' && $booking->status !== 'confirmed') {
            return redirect()
                ->route('my-bookings.show', $booking)
                ->with('status', 'Booking tidak lagi tersedia untuk pembayaran.')
                ->with('status_type', 'warning');
        }

        return view('user.payments.create', [
            'booking' => $booking,
            'latestPayment' => $booking->payments->sortByDesc('created_at')->first(),
            'midtransConfigured' => $this->midtransConfigured(),
            'midtransLocalMode' => $this->midtransRunsInLocalMode(),
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $booking = Booking::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail($request->integer('booking_id'));

        $booking = $this->bookingExpiryService->expireIfNeeded($booking);

        $payment = $this->paymentService->createPayment($booking, $request->validated());

        if ($payment->payment_method === 'midtrans_snap') {
            if (! $payment->midtrans_redirect_url) {
                return redirect()
                    ->route('payments.show', $payment)
                    ->with('status', 'Gagal membuat sesi Midtrans. Coba ulang beberapa saat lagi.')
                    ->with('status_type', 'error');
            }

            return redirect()->away($payment->midtrans_redirect_url);
        }

        if ($payment->payment_method === 'qris') {
            return redirect()
                ->route('payments.qris.show', $payment)
                ->with('status', 'QRIS siap dipindai. Scan QR untuk menyelesaikan pembayaran demo.')
                ->with('status_type', 'info');
        }

        return redirect()
            ->route('payments.show', $payment)
            ->with('status', 'Pembayaran berhasil dikirim dan sedang menunggu persetujuan staff.')
            ->with('status_type', 'success');
    }

    public function show(Request $request, Payment $payment): View
    {
        $payment->load('booking.flight.departureAirport', 'booking.flight.arrivalAirport');
        abort_unless($payment->booking->user_id === $request->user()->id, 403);
        if ($payment->payment_method === 'midtrans_snap' && $payment->payment_status === 'pending') {
            try {
                $payment = $this->paymentService->syncFromMidtransGateway($payment, 6, 500);
                $payment->load('booking.flight.departureAirport', 'booking.flight.arrivalAirport');
            } catch (\Throwable) {
                // Keep current state if Midtrans status check fails.
            }
        }
        $payment->setRelation('booking', $this->bookingExpiryService->expireIfNeeded($payment->booking));

        return view('user.payments.show', [
            'payment' => $payment,
            'paymentLabel' => PaymentMethodCatalog::label($payment->payment_method),
            'paymentMethod' => PaymentMethodCatalog::all()[$payment->payment_method] ?? null,
            'qrisUrl' => $payment->payment_method === 'qris' && $payment->payment_status === 'pending' ? route('payments.qris.show', $payment) : null,
            'midtransUrl' => $payment->payment_method === 'midtrans_snap' && $payment->payment_status === 'pending' ? $payment->midtrans_redirect_url : null,
            'midtransLocalMode' => $this->midtransRunsInLocalMode(),
            'midtransSimulatorEnabled' => (bool) config('services.midtrans.local_simulator', false) && ! (bool) config('services.midtrans.is_production', false),
        ]);
    }

    public function midtransFinish(Request $request): RedirectResponse
    {
        $orderId = trim((string) $request->query('order_id'));

        if ($orderId === '') {
            return redirect()
                ->route('my-bookings.index')
                ->with('status', 'Kembali dari Midtrans. Status pembayaran akan diperbarui otomatis.')
                ->with('status_type', 'info');
        }

        $payment = Payment::query()
            ->with('booking')
            ->where('midtrans_order_id', $orderId)
            ->orWhere('transaction_code', $orderId)
            ->latest()
            ->first();

        if (! $payment || $payment->booking->user_id !== $request->user()?->id) {
            return redirect()
                ->route('my-bookings.index')
                ->with('status', 'Kembali dari Midtrans. Status pembayaran akan diperbarui otomatis.')
                ->with('status_type', 'info');
        }

        if ($payment->payment_method === 'midtrans_snap' && $payment->payment_status === 'pending') {
            try {
                $payment = $this->paymentService->syncFromMidtransGateway($payment, 20, 1000);
            } catch (\Throwable) {
                // Keep normal redirect flow even if status sync fails.
            }
        }

        if ($payment->payment_method === 'midtrans_snap' && $payment->payment_status === 'pending') {
            $resultData = $request->query('result_data');
            $resultPayload = [];
            if (is_string($resultData) && $resultData !== '') {
                $decoded = json_decode($resultData, true);
                if (is_array($decoded)) {
                    $resultPayload = $decoded;
                }
            }

            $statusCode = (string) ($request->query('status_code', $resultPayload['status_code'] ?? ''));
            $transactionStatus = (string) ($request->query('transaction_status', $resultPayload['transaction_status'] ?? ''));

            if ($transactionStatus === '' && $statusCode === '200') {
                $transactionStatus = 'settlement';
            }

            $fallbackPayload = [
                'order_id' => $orderId,
                'status_code' => $statusCode,
                'transaction_status' => $transactionStatus,
                'fraud_status' => (string) $request->query('fraud_status', $resultPayload['fraud_status'] ?? ''),
                'transaction_id' => (string) $request->query('transaction_id', $resultPayload['transaction_id'] ?? ''),
                'payment_type' => (string) $request->query('payment_type', $resultPayload['payment_type'] ?? ''),
            ];

            $mappedStatus = $this->midtransService->mapMidtransStatusToPaymentStatus($fallbackPayload);
            if ($mappedStatus === 'paid') {
                $payment = $this->paymentService->syncFromMidtransWebhook($payment, $fallbackPayload);
            }
        }

        $statusMessage = $payment->payment_status === 'paid'
            ? 'Pembayaran Midtrans sudah sukses dan dikonfirmasi otomatis.'
            : 'Kembali dari Midtrans. Status pembayaran akan diperbarui otomatis.';
        $statusType = $payment->payment_status === 'paid' ? 'success' : 'info';

        return redirect()
            ->route('payments.show', $payment)
            ->with('status', $statusMessage)
            ->with('status_type', $statusType);
    }

    public function refreshMidtransStatus(Request $request, Payment $payment): RedirectResponse
    {
        $payment->load('booking');
        abort_unless($payment->booking->user_id === $request->user()->id, 403);

        if ($payment->payment_method !== 'midtrans_snap') {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Refresh status hanya tersedia untuk pembayaran Midtrans.')
                ->with('status_type', 'warning');
        }

        try {
            $payment = $this->paymentService->syncFromMidtransGateway($payment, 8, 500);

            $message = $payment->payment_status === 'paid'
                ? 'Status Midtrans berhasil diperbarui. Pembayaran sudah lunas.'
                : 'Status Midtrans dicek ulang. Jika user belum menyelesaikan pembayaran di Snap, status akan tetap pending.';

            return redirect()
                ->route('payments.show', $payment)
                ->with('status', $message)
                ->with('status_type', 'success');
        } catch (\Throwable $exception) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Gagal refresh status Midtrans: '.$exception->getMessage())
                ->with('status_type', 'warning');
        }
    }

    public function simulateMidtransStatus(Request $request, Payment $payment): RedirectResponse
    {
        $payment->load('booking');
        abort_unless($payment->booking->user_id === $request->user()->id, 403);

        if ($payment->payment_method !== 'midtrans_snap') {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Simulasi internal hanya tersedia untuk pembayaran Midtrans.')
                ->with('status_type', 'warning');
        }

        $enabled = (bool) config('services.midtrans.local_simulator', false);
        $isProduction = (bool) config('services.midtrans.is_production', false);

        if (! $enabled || $isProduction) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Simulasi internal dimatikan. Aktifkan MIDTRANS_LOCAL_SIMULATOR=true pada mode sandbox.')
                ->with('status_type', 'warning');
        }

        if ($payment->payment_status === 'paid') {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Pembayaran ini sudah berstatus lunas.')
                ->with('status_type', 'info');
        }

        $orderId = (string) ($payment->midtrans_order_id ?: $payment->transaction_code ?: 'SIM-'.$payment->booking_id.'-'.$payment->id);

        try {
            $this->paymentService->syncFromMidtransWebhook($payment, [
                'order_id' => $orderId,
                'transaction_id' => 'SIMTX-'.$payment->id.'-'.now()->format('YmdHis'),
                'payment_type' => 'qris',
                'status_code' => '200',
                'transaction_status' => 'settlement',
                'fraud_status' => 'accept',
            ]);

            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Simulasi internal berhasil: status pembayaran menjadi lunas.')
                ->with('status_type', 'success');
        } catch (\Throwable $exception) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Simulasi internal gagal: '.$exception->getMessage())
                ->with('status_type', 'warning');
        }
    }

    public function proof(Request $request, Payment $payment): StreamedResponse|RedirectResponse
    {
        abort_unless($payment->booking->user_id === $request->user()->id, 403);

        if (! $payment->proof_file) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Bukti pembayaran tidak ditemukan.')
                ->with('status_type', 'warning');
        }

        if (str_starts_with($payment->proof_file, 'http://') || str_starts_with($payment->proof_file, 'https://')) {
            return redirect()->away($payment->proof_file);
        }

        if (! Storage::disk('public')->exists($payment->proof_file)) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'File bukti pembayaran tidak tersedia di storage.')
                ->with('status_type', 'warning');
        }

        return Storage::disk('public')->response($payment->proof_file);
    }

    public function showQris(Request $request, Payment $payment): View|RedirectResponse
    {
        $payment->load([
            'booking.flight.airline',
            'booking.flight.departureAirport',
            'booking.flight.arrivalAirport',
        ]);
        abort_unless($payment->booking->user_id === $request->user()->id, 403);

        if ($payment->payment_method !== 'qris') {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Halaman QRIS hanya tersedia untuk metode pembayaran QRIS.')
                ->with('status_type', 'warning');
        }

        $payment->setRelation('booking', $this->bookingExpiryService->expireIfNeeded($payment->booking));

        if ($payment->booking->status !== 'pending') {
            return redirect()
                ->route('my-bookings.show', $payment->booking)
                ->with('status', 'Sesi pembayaran QRIS sudah berakhir atau booking sudah tidak pending.')
                ->with('status_type', 'warning');
        }

        if ($payment->payment_status === 'paid') {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Pembayaran QRIS sudah selesai diproses.')
                ->with('status_type', 'warning');
        }

        if ($payment->payment_status !== 'pending') {
            return redirect()
                ->route('payments.show', $payment)
                ->with('status', 'Pembayaran QRIS ini tidak lagi aktif untuk dipindai.')
                ->with('status_type', 'warning');
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
        abort_unless($payment->payment_method === 'qris', 404);

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

    protected function midtransConfigured(): bool
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $clientKey = (string) config('services.midtrans.client_key');

        return $serverKey !== ''
            && $clientKey !== ''
            && ! str_contains($serverKey, 'REPLACE_WITH')
            && ! str_contains($clientKey, 'REPLACE_WITH');
    }

    protected function midtransRunsInLocalMode(): bool
    {
        return trim((string) config('services.midtrans.notification_url', '')) === '';
    }
}
