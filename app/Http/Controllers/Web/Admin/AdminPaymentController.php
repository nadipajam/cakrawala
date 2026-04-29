<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\BookingExpiryService;
use Illuminate\Http\Request;

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

    public function verify(Request $request, Payment $payment)
    {
        $request->validate([
            'transaction_code' => ['nullable', 'string', 'max:100'],
        ]);

        $this->paymentService->settlePayment(
            $payment,
            $request->filled('transaction_code')
                ? $request->string('transaction_code')->toString()
                : null
        );

        return back()->with('status', 'Payment berhasil diverifikasi.');
    }

    public function reject(Request $request, Payment $payment)
    {
        $request->validate([
            'transaction_code' => ['nullable', 'string', 'max:100'],
        ]);

        $this->paymentService->verifyPayment($payment, [
            'payment_status' => 'failed',
            'transaction_code' => $request->string('transaction_code')->toString(),
        ]);

        return back()->with('status', 'Payment berhasil ditolak.');
    }
}
