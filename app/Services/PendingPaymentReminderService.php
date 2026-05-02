<?php

namespace App\Services;

use App\Models\ContactMessage;
use App\Models\Payment;
use App\Support\PaymentMethodCatalog;
use Illuminate\Support\Collection;

class PendingPaymentReminderService
{
    public const SOURCE = 'payment_escalation';

    /**
     * @return array{checked:int,created:int,reopened:int,unchanged:int}
     */
    public function escalateOverdueNonQris(int $minutes = 30): array
    {
        $cutoff = now()->subMinutes(max($minutes, 1));
        $payments = $this->overduePayments($cutoff);

        $summary = [
            'checked' => $payments->count(),
            'created' => 0,
            'reopened' => 0,
            'unchanged' => 0,
        ];

        foreach ($payments as $payment) {
            $subject = $this->subjectFor($payment);

            $existing = ContactMessage::query()
                ->where('source', self::SOURCE)
                ->where('subject', $subject)
                ->latest()
                ->first();

            if (! $existing) {
                ContactMessage::query()->create([
                    'user_id' => $payment->booking?->user_id,
                    'name' => $this->nameFor($payment),
                    'email' => $this->emailFor($payment),
                    'phone' => $payment->payer_phone,
                    'subject' => $subject,
                    'message' => $this->messageFor($payment),
                    'source' => self::SOURCE,
                    'status' => 'open',
                    'internal_notes' => $this->internalNotesFor($payment),
                ]);

                $summary['created']++;
                continue;
            }

            if (in_array($existing->status, ['open', 'in_progress'], true)) {
                $summary['unchanged']++;
                continue;
            }

            $existing->update([
                'status' => 'open',
                'resolved_at' => null,
                'message' => $this->messageFor($payment),
                'internal_notes' => $this->internalNotesFor($payment),
            ]);

            $summary['reopened']++;
        }

        return $summary;
    }

    protected function overduePayments(\DateTimeInterface $cutoff): Collection
    {
        return Payment::query()
            ->with(['booking.user', 'booking.flight.departureAirport', 'booking.flight.arrivalAirport'])
            ->where('payment_status', 'pending')
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '<=', $cutoff)
            ->where(function ($query) {
                $query
                    ->where('payment_method', '!=', 'qris')
                    ->orWhereNull('payment_method');
            })
            ->whereHas('booking', fn ($bookingQuery) => $bookingQuery->where('status', 'pending'))
            ->orderBy('submitted_at')
            ->get()
            ->filter(fn (Payment $payment) => PaymentMethodCatalog::type($payment->payment_method) !== 'qris')
            ->values();
    }

    protected function subjectFor(Payment $payment): string
    {
        $bookingCode = (string) ($payment->booking?->booking_code ?? ('BOOKING-'.$payment->booking_id));

        return 'Follow-up verifikasi pembayaran '.$bookingCode;
    }

    protected function nameFor(Payment $payment): string
    {
        $userName = trim((string) ($payment->booking?->user?->name ?? ''));
        if ($userName !== '') {
            return $userName;
        }

        $payerName = trim((string) ($payment->payer_name ?? ''));
        if ($payerName !== '') {
            return $payerName;
        }

        return 'Customer Cakrawala';
    }

    protected function emailFor(Payment $payment): string
    {
        $email = trim((string) ($payment->booking?->user?->email ?? ''));
        if ($email !== '') {
            return $email;
        }

        return 'noreply-payment@cakrawala.local';
    }

    protected function messageFor(Payment $payment): string
    {
        $bookingCode = (string) ($payment->booking?->booking_code ?? '-');
        $customerName = (string) ($payment->booking?->user?->name ?? $payment->payer_name ?? '-');
        $flight = $payment->booking?->flight;
        $route = $flight
            ? sprintf(
                '%s -> %s',
                (string) ($flight->departureAirport?->code ?? '-'),
                (string) ($flight->arrivalAirport?->code ?? '-')
            )
            : '-';
        $overdueMinutes = (int) max(optional($payment->submitted_at)->diffInMinutes(now()) ?? 0, 0);

        return implode("\n", [
            'Pembayaran manual menunggu verifikasi selama '.$overdueMinutes.' menit.',
            'Booking: '.$bookingCode,
            'Customer: '.$customerName,
            'Metode: '.PaymentMethodCatalog::label($payment->payment_method),
            'Rute: '.$route,
            'Nominal: Rp'.number_format((float) $payment->amount, 0, ',', '.'),
            'Submitted: '.optional($payment->submitted_at)->format('d M Y H:i'),
            'Tindak lanjut: mohon verifikasi pembayaran pada dashboard pembayaran backoffice.',
        ]);
    }

    protected function internalNotesFor(Payment $payment): string
    {
        return '[AUTO-ESCALATION] payment_id='.$payment->id.' booking_id='.$payment->booking_id;
    }
}
