<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Support\PaymentMethodCatalog;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        protected TicketService $ticketService,
        protected BookingAddonService $bookingAddonService,
        protected PortalNotificationService $notificationService,
        protected BookingExpiryService $bookingExpiryService
    ) {
    }

    public function createPayment(Booking $booking, array $data): Payment
    {
        $booking = $this->bookingExpiryService->expireIfNeeded($booking)->fresh(['payments', 'user']);

        if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'booking_id' => ['Booking tidak dapat diproses untuk pembayaran.'],
            ]);
        }

        return DB::transaction(function () use ($booking, $data) {
            $payment = $booking->payments()
                ->where('payment_status', 'pending')
                ->latest()
                ->first();

            if (! $payment) {
                $payment = new Payment(['booking_id' => $booking->id]);
            }

            $amount = $booking->total_price;
            if ($payment && (float) $payment->amount > 0) {
                $amount = (float) $payment->amount;
            }

            $payment->fill([
                'payment_method' => $data['payment_method'],
                'payer_name' => $data['payer_name'] ?? null,
                'payer_phone' => $data['payer_phone'] ?? null,
                'payer_bank_name' => $data['payer_bank_name'] ?? null,
                'payer_bank_account_number' => $data['payer_bank_account_number'] ?? null,
                'payment_notes' => $data['payment_notes'] ?? null,
                'amount' => $amount,
                'payment_status' => 'pending',
                'submitted_at' => now(),
            ]);

            if (! empty($data['proof_file'])) {
                $payment->proof_file = $data['proof_file']->store('payments', 'public');
            } elseif (! PaymentMethodCatalog::requiresProof($data['payment_method'])) {
                $payment->proof_file = null;
            }

            $payment->save();

            $payment = $payment->load('booking.user');

            $this->notificationService->paymentInstructionReady($payment);

            return $payment;
        });
    }

    public function verifyPayment(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $payment->update([
                'payment_status' => $data['payment_status'],
                'transaction_code' => $data['transaction_code'] ?? $payment->transaction_code,
                'paid_at' => $data['payment_status'] === 'paid' ? now() : null,
            ]);

            $booking = $payment->booking()->with('details.ticket')->firstOrFail();

            if ($payment->payment_status === 'paid') {
                $booking->update([
                    'status' => 'confirmed',
                    'expired_at' => null,
                ]);
                $this->ticketService->issueForBooking($booking);
                $this->bookingAddonService->markPaidForBooking($booking);
            }

            if ($payment->payment_status === 'refunded') {
                $booking->update(['status' => 'cancelled']);
                $booking->addons()
                    ->whereIn('status', ['selected', 'paid'])
                    ->update(['status' => 'cancelled']);
            }

            $payment = $payment->fresh('booking.user');

            $this->notificationService->paymentUpdated($payment);

            return $payment;
        });
    }

    public function settlePayment(Payment $payment, ?string $transactionCode = null): Payment
    {
        if ($payment->payment_status === 'paid') {
            return $payment->fresh('booking');
        }

        $booking = $payment->booking()->firstOrFail();
        $booking = $this->bookingExpiryService->expireIfNeeded($booking);

        if ($booking->status !== 'pending') {
            throw ValidationException::withMessages([
                'payment' => ['Booking tidak lagi tersedia untuk diselesaikan pembayarannya.'],
            ]);
        }

        return $this->verifyPayment($payment, [
            'payment_status' => 'paid',
            'transaction_code' => $transactionCode ?? $this->generateTransactionCode($payment),
        ]);
    }

    protected function generateTransactionCode(Payment $payment): string
    {
        return 'TRX-'.$payment->booking_id.'-'.strtoupper(substr(md5((string) $payment->id.microtime(true)), 0, 8));
    }
}
