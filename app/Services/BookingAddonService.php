<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingDetail;
use App\Models\Payment;
use App\Models\User;
use App\Support\AddonCatalog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingAddonService
{
    public function __construct(
        protected PortalNotificationService $notificationService
    ) {
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function catalog(): array
    {
        return AddonCatalog::all();
    }

    public function add(User $actor, Booking $booking, array $data): BookingAddon
    {
        $this->guardBookingAccess($actor, $booking);
        $this->guardBookingState($booking);

        $addonCode = (string) ($data['addon_code'] ?? '');
        $config = AddonCatalog::find($addonCode);

        if (! $config) {
            throw ValidationException::withMessages([
                'addon_code' => ['Add-on tidak ditemukan.'],
            ]);
        }

        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $maxQty = (int) ($config['max_qty'] ?? 5);

        if ($quantity > $maxQty) {
            throw ValidationException::withMessages([
                'quantity' => ["Maksimum kuantitas untuk add-on ini adalah {$maxQty}."],
            ]);
        }

        $bookingDetail = null;
        if (($config['scope'] ?? 'passenger') === 'passenger') {
            $detailId = (int) ($data['booking_detail_id'] ?? 0);
            $bookingDetail = $booking->details()->find($detailId);

            if (! $bookingDetail) {
                throw ValidationException::withMessages([
                    'booking_detail_id' => ['Pilih passenger yang valid untuk add-on ini.'],
                ]);
            }
        }

        return DB::transaction(function () use ($booking, $bookingDetail, $addonCode, $config, $quantity, $data) {
            $addon = BookingAddon::create([
                'booking_id' => $booking->id,
                'booking_detail_id' => $bookingDetail?->id,
                'addon_code' => $addonCode,
                'addon_type' => (string) $config['type'],
                'addon_name' => (string) $config['name'],
                'quantity' => $quantity,
                'unit_price' => (float) $config['unit_price'],
                'total_price' => (float) $config['unit_price'] * $quantity,
                'status' => 'selected',
                'notes' => $data['notes'] ?? null,
            ]);

            $booking = $this->recalculateBookingTotal($booking);
            $this->syncPendingPayment($booking);

            $addon->loadMissing('booking.user', 'bookingDetail.passenger');
            $this->notificationService->addonAdded($addon);

            return $addon;
        });
    }

    public function cancel(User $actor, BookingAddon $addon): BookingAddon
    {
        $addon->loadMissing('booking.user', 'booking.details', 'booking.payments');
        $booking = $addon->booking;

        if (! $booking) {
            throw ValidationException::withMessages([
                'addon' => ['Booking add-on tidak valid.'],
            ]);
        }

        $this->guardBookingAccess($actor, $booking);
        $this->guardBookingState($booking);

        if ($addon->status !== 'selected') {
            throw ValidationException::withMessages([
                'addon' => ['Hanya add-on dengan status selected yang bisa dibatalkan.'],
            ]);
        }

        return DB::transaction(function () use ($addon, $booking) {
            $addon->update(['status' => 'cancelled']);

            $booking = $this->recalculateBookingTotal($booking);
            $this->syncPendingPayment($booking);

            $addon->loadMissing('booking.user', 'bookingDetail.passenger');
            $this->notificationService->addonCancelled($addon);

            return $addon->fresh();
        });
    }

    public function markPaidForBooking(Booking $booking): void
    {
        $booking->addons()
            ->where('status', 'selected')
            ->update(['status' => 'paid']);
    }

    public function recalculateBookingTotal(Booking $booking): Booking
    {
        $booking->loadMissing([
            'details',
            'addons',
            'payments',
        ]);

        $baseTotal = (float) $booking->details->sum(fn (BookingDetail $detail) => (float) $detail->price);
        $addonTotal = (float) $booking->addons
            ->whereIn('status', ['selected', 'paid'])
            ->sum(fn (BookingAddon $addon) => (float) $addon->total_price);

        $booking->update([
            'total_price' => $baseTotal + $addonTotal,
        ]);

        return $booking->fresh(['payments', 'addons', 'details']);
    }

    public function syncPendingPayment(Booking $booking): void
    {
        $booking->loadMissing('payments', 'addons');

        $hasAnyPaidPayment = $booking->payments->contains(fn (Payment $payment) => $payment->payment_status === 'paid');
        $latestPendingPayment = $booking->payments
            ->where('payment_status', 'pending')
            ->sortByDesc('created_at')
            ->first();
        $unpaidAddonTotal = (float) $booking->addons
            ->where('status', 'selected')
            ->sum(fn (BookingAddon $addon) => (float) $addon->total_price);

        if (! $hasAnyPaidPayment) {
            if ($latestPendingPayment) {
                $latestPendingPayment->update(['amount' => $booking->total_price]);

                return;
            }

            Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => 'unassigned',
                'amount' => $booking->total_price,
                'payment_status' => 'pending',
            ]);

            return;
        }

        if ($unpaidAddonTotal <= 0) {
            if ($latestPendingPayment) {
                if ($latestPendingPayment->payment_method === 'unassigned') {
                    $latestPendingPayment->delete();
                } else {
                    $latestPendingPayment->update([
                        'amount' => 0,
                        'payment_status' => 'failed',
                    ]);
                }
            }

            return;
        }

        if ($latestPendingPayment) {
            $latestPendingPayment->update(['amount' => $unpaidAddonTotal]);

            return;
        }

        Payment::create([
            'booking_id' => $booking->id,
            'payment_method' => 'unassigned',
            'amount' => $unpaidAddonTotal,
            'payment_status' => 'pending',
        ]);
    }

    /**
     * @return Collection<int, BookingAddon>
     */
    public function addonsForBooking(Booking $booking): Collection
    {
        return $booking->addons()
            ->with(['bookingDetail.passenger'])
            ->latest()
            ->get();
    }

    protected function guardBookingAccess(User $actor, Booking $booking): void
    {
        if ($booking->user_id !== $actor->id && ! $actor->isBackoffice()) {
            throw ValidationException::withMessages([
                'booking' => ['Booking tidak dapat diakses.'],
            ]);
        }
    }

    protected function guardBookingState(Booking $booking): void
    {
        if (! in_array($booking->status, ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages([
                'booking' => ['Add-on hanya tersedia untuk booking pending/confirmed.'],
            ]);
        }
    }
}
