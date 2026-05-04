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
        protected BookingExpiryService $bookingExpiryService,
        protected MidtransService $midtransService
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

        $payment = DB::transaction(function () use ($booking, $data) {
            $isMidtrans = $data['payment_method'] === 'midtrans_snap';

            if ($isMidtrans) {
                $booking->payments()
                    ->where('payment_status', 'pending')
                    ->update(['payment_status' => 'failed']);

                $payment = new Payment(['booking_id' => $booking->id]);
            } else {
                $payment = $booking->payments()
                    ->where('payment_status', 'pending')
                    ->latest()
                    ->first();

                if (! $payment) {
                    $payment = new Payment(['booking_id' => $booking->id]);
                }
            }

            $amount = $booking->total_price;
            if (! $isMidtrans && $payment && (float) $payment->amount > 0) {
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
                'transaction_code' => $isMidtrans ? null : $payment->transaction_code,
                'midtrans_order_id' => $isMidtrans ? null : $payment->midtrans_order_id,
                'midtrans_transaction_id' => $isMidtrans ? null : $payment->midtrans_transaction_id,
                'midtrans_snap_token' => $isMidtrans ? null : $payment->midtrans_snap_token,
                'midtrans_redirect_url' => $isMidtrans ? null : $payment->midtrans_redirect_url,
                'midtrans_payment_type' => $isMidtrans ? null : $payment->midtrans_payment_type,
                'midtrans_status_code' => $isMidtrans ? null : $payment->midtrans_status_code,
                'midtrans_payload' => $isMidtrans ? null : $payment->midtrans_payload,
            ]);

            if (! empty($data['proof_file'])) {
                $payment->proof_file = $data['proof_file']->store('payments', 'public');
            } elseif (! PaymentMethodCatalog::requiresProof($data['payment_method'])) {
                $payment->proof_file = null;
            }

            $payment->save();

            $isQris = PaymentMethodCatalog::type($data['payment_method']) === 'qris';
            $booking->update([
                'expired_at' => $isQris ? now()->addMinutes(5) : null,
            ]);

            return $payment->load('booking.user');
        });

        if ($payment->payment_method === 'midtrans_snap') {
            $payment = $this->bootstrapMidtransPayment($payment);
        }

        $this->notificationService->paymentInstructionReady($payment);

        return $payment;
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

        if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'payment' => ['Booking tidak lagi tersedia untuk diselesaikan pembayarannya.'],
            ]);
        }

        return $this->verifyPayment($payment, [
            'payment_status' => 'paid',
            'transaction_code' => $transactionCode ?? $this->generateTransactionCode($payment),
        ]);
    }

    public function syncFromMidtransWebhook(Payment $payment, array $payload): Payment
    {
        $mappedStatus = $this->midtransService->mapMidtransStatusToPaymentStatus($payload);

        $payment->update([
            'midtrans_order_id' => (string) ($payload['order_id'] ?? $payment->midtrans_order_id),
            'midtrans_transaction_id' => (string) ($payload['transaction_id'] ?? $payment->midtrans_transaction_id),
            'midtrans_payment_type' => (string) ($payload['payment_type'] ?? $payment->midtrans_payment_type),
            'midtrans_status_code' => (string) ($payload['status_code'] ?? $payment->midtrans_status_code),
            'midtrans_payload' => $payload,
        ]);

        if ($mappedStatus === 'paid') {
            return $this->settlePayment($payment, (string) ($payload['order_id'] ?? $payment->transaction_code));
        }

        if (in_array($mappedStatus, ['failed', 'refunded'], true) && $payment->payment_status !== $mappedStatus) {
            return $this->verifyPayment($payment, [
                'payment_status' => $mappedStatus,
                'transaction_code' => (string) ($payload['order_id'] ?? $payment->transaction_code),
            ]);
        }

        return $payment->fresh('booking.user');
    }

    public function syncFromMidtransGateway(Payment $payment, int $maxAttempts = 1, int $delayMilliseconds = 0): Payment
    {
        if ($payment->payment_method !== 'midtrans_snap') {
            return $payment->fresh('booking.user');
        }

        $orderId = (string) ($payment->midtrans_order_id ?: $payment->transaction_code);
        if ($orderId === '') {
            return $payment->fresh('booking.user');
        }

        $attempts = max($maxAttempts, 1);
        $delayMicroseconds = max($delayMilliseconds, 0) * 1000;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                $payload = $this->midtransService->getTransactionStatus($orderId);
                $syncedPayment = $this->syncFromMidtransWebhook($payment, $payload);
            } catch (\Throwable) {
                if ($attempt < $attempts && $delayMicroseconds > 0) {
                    usleep($delayMicroseconds);
                }

                continue;
            }

            if ($syncedPayment->payment_status !== 'pending') {
                return $syncedPayment;
            }

            $payment = $syncedPayment->fresh();

            if ($attempt < $attempts && $delayMicroseconds > 0) {
                usleep($delayMicroseconds);
            }
        }

        return $payment->fresh('booking.user');
    }

    protected function bootstrapMidtransPayment(Payment $payment): Payment
    {
        $payment->loadMissing('booking.user');
        $amount = (int) round((float) $payment->amount);
        $orderId = $this->midtransService->generateOrderId($payment->booking_id, $payment->id);

        try {
            $result = $this->midtransService->createSnapTransaction($payment->booking, $amount, $orderId);
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'payment' => ['Gagal membuat transaksi Midtrans: '.$exception->getMessage()],
            ]);
        }

        $payment->update([
            'transaction_code' => $orderId,
            'midtrans_order_id' => $orderId,
            'midtrans_snap_token' => $result['token'],
            'midtrans_redirect_url' => $result['redirect_url'],
            'midtrans_payload' => $result['payload'],
            'midtrans_status_code' => '201',
        ]);

        return $payment->fresh('booking.user');
    }

    protected function generateTransactionCode(Payment $payment): string
    {
        return 'TRX-'.$payment->booking_id.'-'.strtoupper(substr(md5((string) $payment->id.microtime(true)), 0, 8));
    }
}
