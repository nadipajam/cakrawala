<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\BookingExpiryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected BookingExpiryService $bookingExpiryService
    ) {
    }

    public function index(Request $request)
    {
        $this->bookingExpiryService->expirePendingBookings();

        $status = trim((string) $request->string('status'));
        $search = trim((string) $request->string('search'));

        $payments = Payment::query()
            ->with(['booking.user', 'booking.flight'])
            ->when(in_array($status, ['pending', 'paid', 'failed', 'refunded'], true), fn ($query) => $query->where('payment_status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($qq) use ($search) {
                    $qq->where('transaction_code', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhereHas('booking', fn ($q) => $q->where('booking_code', 'like', "%{$search}%"))
                        ->orWhereHas('booking.user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'status', 'search'));
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'booking.user',
            'booking.flight.airline',
            'booking.flight.departureAirport',
            'booking.flight.arrivalAirport',
            'booking.details.passenger',
            'booking.details.seat',
            'booking.details.ticket',
        ]);

        return view('admin.payments.show', compact('payment'));
    }

    public function proof(Payment $payment): StreamedResponse|RedirectResponse
    {
        if (! $payment->proof_file) {
            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('status', 'Bukti pembayaran tidak ditemukan.')
                ->with('status_type', 'warning');
        }

        if (str_starts_with($payment->proof_file, 'http://') || str_starts_with($payment->proof_file, 'https://')) {
            return redirect()->away($payment->proof_file);
        }

        if (! Storage::disk('public')->exists($payment->proof_file)) {
            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('status', 'File bukti pembayaran tidak tersedia di storage.')
                ->with('status_type', 'warning');
        }

        return Storage::disk('public')->response($payment->proof_file);
    }

    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'transaction_code' => ['nullable', 'string', 'max:100'],
        ]);

        if ($payment->payment_status === 'paid') {
            return back()
                ->with('status', 'Payment ini sudah terverifikasi sebelumnya.')
                ->with('status_type', 'warning');
        }

        if ($payment->payment_status === 'failed' || $payment->payment_status === 'refunded') {
            return back()
                ->with('status', 'Payment ini sudah diproses ke status akhir dan tidak bisa diverifikasi ulang.')
                ->with('status_type', 'warning');
        }

        $this->paymentService->settlePayment(
            $payment,
            $request->filled('transaction_code')
                ? $request->string('transaction_code')->toString()
                : null
        );

        return back()
            ->with('status', 'Payment berhasil diverifikasi.')
            ->with('status_type', 'success');
    }

    public function reject(Request $request, Payment $payment)
    {
        $request->validate([
            'transaction_code' => ['nullable', 'string', 'max:100'],
        ]);

        if ($payment->payment_status === 'failed') {
            return back()
                ->with('status', 'Payment ini sudah ditolak sebelumnya.')
                ->with('status_type', 'warning');
        }

        if ($payment->payment_status === 'paid' || $payment->payment_status === 'refunded') {
            return back()
                ->with('status', 'Payment ini sudah diproses ke status akhir dan tidak bisa ditolak ulang.')
                ->with('status_type', 'warning');
        }

        $this->paymentService->verifyPayment($payment, [
            'payment_status' => 'failed',
            'transaction_code' => $request->string('transaction_code')->toString(),
        ]);

        return back()
            ->with('status', 'Payment berhasil ditolak.')
            ->with('status_type', 'success');
    }

    public function refreshMidtransStatus(Payment $payment): RedirectResponse
    {
        if ($payment->payment_method !== 'midtrans_snap') {
            return back()
                ->with('status', 'Sync Midtrans hanya tersedia untuk pembayaran Midtrans Snap.')
                ->with('status_type', 'warning');
        }

        try {
            $payment = $this->paymentService->syncFromMidtransGateway($payment, 8, 500);

            $message = $payment->payment_status === 'paid'
                ? 'Status Midtrans berhasil diperbarui dan pembayaran sudah lunas.'
                : 'Status Midtrans berhasil dicek ulang. Jika user belum menyelesaikan Snap, status akan tetap pending.';

            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('status', $message)
                ->with('status_type', 'success');
        } catch (\Throwable $exception) {
            return redirect()
                ->route('admin.payments.show', $payment)
                ->with('status', 'Gagal sync status Midtrans: '.$exception->getMessage())
                ->with('status_type', 'warning');
        }
    }
}
