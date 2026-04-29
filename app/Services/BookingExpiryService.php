<?php

namespace App\Services;

use App\Models\Booking;

class BookingExpiryService
{
    public function __construct(
        protected PortalNotificationService $notificationService
    ) {
    }

    public function expireIfNeeded(Booking $booking): Booking
    {
        if (! $this->shouldExpire($booking)) {
            return $booking;
        }

        $this->expirePendingBookings($booking->flight_id, [$booking->id]);

        return $booking->fresh([
            'payments',
            'addons',
            'details.ticket',
        ]) ?? $booking;
    }

    /**
     * @param  array<int, int>|null  $bookingIds
     */
    public function expirePendingBookings(?int $flightId = null, ?array $bookingIds = null): int
    {
        $query = Booking::query()
            ->with(['user', 'payments', 'addons'])
            ->where('status', 'pending')
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now());

        if ($flightId !== null) {
            $query->where('flight_id', $flightId);
        }

        if ($bookingIds !== null && $bookingIds !== []) {
            $query->whereIn('id', $bookingIds);
        }

        $bookings = $query->get();

        foreach ($bookings as $booking) {
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

            $this->notificationService->bookingExpired($booking);
        }

        return $bookings->count();
    }

    protected function shouldExpire(Booking $booking): bool
    {
        return $booking->status === 'pending'
            && $booking->expired_at !== null
            && $booking->expired_at->isPast();
    }
}
