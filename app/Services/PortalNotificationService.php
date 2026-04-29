<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingChangeRequest;
use App\Models\BookingDetail;
use App\Models\ContactMessage;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\PortalNotification;

class PortalNotificationService
{
    public function notify(?User $user, string $title, string $message, string $type = 'general', ?string $actionUrl = null, array $meta = []): void
    {
        if (! $user) {
            return;
        }

        $user->notify(new PortalNotification([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'action_url' => $actionUrl,
            'meta' => $meta,
        ]));
    }

    public function bookingCreated(Booking $booking): void
    {
        $this->notify(
            $booking->user,
            'Booking berhasil dibuat',
            'Booking '.$booking->booking_code.' sudah tercatat. Lanjutkan pembayaran sebelum '.$booking->expired_at?->format('d M Y H:i').'.',
            'booking',
            route('my-bookings.show', $booking),
            ['booking_id' => $booking->id]
        );
    }

    public function bookingCancelled(Booking $booking): void
    {
        $this->notify(
            $booking->user,
            'Booking dibatalkan',
            'Booking '.$booking->booking_code.' telah dibatalkan.',
            'booking',
            route('my-bookings.show', $booking),
            ['booking_id' => $booking->id]
        );
    }

    public function bookingExpired(Booking $booking): void
    {
        $this->notify(
            $booking->user,
            'Booking expired',
            'Booking '.$booking->booking_code.' expired karena pembayaran tidak diselesaikan dalam 5 menit. Kursi telah dikembalikan ke inventory.',
            'booking',
            route('my-bookings.show', $booking),
            ['booking_id' => $booking->id]
        );
    }

    public function paymentUpdated(Payment $payment): void
    {
        $booking = $payment->booking;

        $message = match ($payment->payment_status) {
            'paid' => 'Pembayaran untuk booking '.$booking?->booking_code.' sudah berhasil diverifikasi.',
            'failed' => 'Pembayaran untuk booking '.$booking?->booking_code.' ditolak atau gagal diproses.',
            'refunded' => 'Dana untuk booking '.$booking?->booking_code.' telah ditandai sebagai refund.',
            default => 'Status pembayaran booking '.$booking?->booking_code.' diperbarui menjadi '.ucfirst((string) $payment->payment_status).'.',
        };

        $this->notify(
            $booking?->user,
            'Update pembayaran',
            $message,
            'payment',
            $booking ? route('payments.show', $payment) : null,
            ['payment_id' => $payment->id]
        );
    }

    public function paymentInstructionReady(Payment $payment): void
    {
        $booking = $payment->booking;

        $this->notify(
            $booking?->user,
            'Instruksi pembayaran siap',
            'Instruksi pembayaran untuk booking '.$booking?->booking_code.' sudah tersimpan. Lengkapi pembayaran sebelum '.optional($booking?->expired_at)->format('d M Y H:i').'.',
            'payment',
            $booking ? route('payments.show', $payment) : null,
            ['payment_id' => $payment->id]
        );
    }

    public function ticketsIssued(Booking $booking): void
    {
        $this->notify(
            $booking->user,
            'E-ticket tersedia',
            'Seluruh e-ticket untuk booking '.$booking->booking_code.' sudah siap dibuka dan diunduh.',
            'ticket',
            route('my-bookings.tickets', $booking),
            ['booking_id' => $booking->id]
        );
    }

    public function checkInCompleted(BookingDetail $detail): void
    {
        $booking = $detail->booking;

        $this->notify(
            $booking?->user,
            'Check-in berhasil',
            'Passenger '.$detail->passenger?->full_name.' untuk booking '.$booking?->booking_code.' sudah check-in. Boarding pass siap diunduh.',
            'checkin',
            $booking ? route('my-bookings.show', $booking) : null,
            ['booking_id' => $booking?->id, 'booking_detail_id' => $detail->id]
        );
    }

    public function addonAdded(BookingAddon $addon): void
    {
        $booking = $addon->booking;

        $this->notify(
            $booking?->user,
            'Add-on ditambahkan',
            $addon->addon_name.' berhasil ditambahkan ke booking '.$booking?->booking_code.'.',
            'addon',
            $booking ? route('my-bookings.addons.index', $booking) : null,
            ['booking_id' => $booking?->id, 'addon_id' => $addon->id]
        );
    }

    public function addonCancelled(BookingAddon $addon): void
    {
        $booking = $addon->booking;

        $this->notify(
            $booking?->user,
            'Add-on dibatalkan',
            $addon->addon_name.' pada booking '.$booking?->booking_code.' berhasil dibatalkan.',
            'addon',
            $booking ? route('my-bookings.addons.index', $booking) : null,
            ['booking_id' => $booking?->id, 'addon_id' => $addon->id]
        );
    }

    public function changeRequestSubmitted(BookingChangeRequest $request): void
    {
        $booking = $request->booking;

        $this->notify(
            $request->user,
            'Permintaan perubahan terkirim',
            'Request '.strtoupper((string) $request->request_type).' untuk booking '.$booking?->booking_code.' sudah diterima.',
            'change_request',
            route('my-bookings.change-requests.index'),
            ['booking_id' => $booking?->id, 'change_request_id' => $request->id]
        );
    }

    public function changeRequestUpdated(BookingChangeRequest $request): void
    {
        $booking = $request->booking;

        $this->notify(
            $request->user,
            'Update permintaan perubahan',
            'Status request '.strtoupper((string) $request->request_type).' untuk booking '.$booking?->booking_code.' sekarang '.ucfirst(str_replace('_', ' ', $request->status)).'.',
            'change_request',
            route('my-bookings.change-requests.index'),
            ['booking_id' => $booking?->id, 'change_request_id' => $request->id]
        );
    }

    public function contactMessageReceived(ContactMessage $message): void
    {
        $this->notify(
            $message->user,
            'Pesan bantuan diterima',
            'Permintaan bantuan dengan subjek "'.$message->subject.'" sudah masuk ke tim operasional kami.',
            'support',
            route('contact'),
            ['contact_message_id' => $message->id]
        );
    }

    public function contactMessageUpdated(ContactMessage $message): void
    {
        $this->notify(
            $message->user,
            'Update permintaan bantuan',
            'Status permintaan "'.$message->subject.'" diperbarui menjadi '.ucfirst(str_replace('_', ' ', $message->status)).'.',
            'support',
            route('contact'),
            ['contact_message_id' => $message->id]
        );
    }
}
