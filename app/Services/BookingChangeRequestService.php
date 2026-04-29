<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingChangeRequest;
use App\Models\User;
use App\Support\BookingChangeRequestCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingChangeRequestService
{
    public function __construct(
        protected PortalNotificationService $notificationService
    ) {
    }

    public function submit(User $user, Booking $booking, array $data): BookingChangeRequest
    {
        if ($booking->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'booking' => ['Booking tidak dapat diakses.'],
            ]);
        }

        if (! in_array($booking->status, ['pending', 'confirmed', 'completed'], true)) {
            throw ValidationException::withMessages([
                'booking' => ['Booking dengan status ini tidak dapat diajukan perubahan.'],
            ]);
        }

        if (! BookingChangeRequestCatalog::isValidType($data['request_type'] ?? null)) {
            throw ValidationException::withMessages([
                'request_type' => ['Jenis permintaan tidak valid.'],
            ]);
        }

        $hasOpenRequest = BookingChangeRequest::query()
            ->where('booking_id', $booking->id)
            ->whereIn('status', ['submitted', 'in_review', 'approved'])
            ->exists();

        if ($hasOpenRequest) {
            throw ValidationException::withMessages([
                'booking' => ['Masih ada request aktif untuk booking ini. Selesaikan request sebelumnya terlebih dahulu.'],
            ]);
        }

        return DB::transaction(function () use ($user, $booking, $data) {
            $request = BookingChangeRequest::create([
                'booking_id' => $booking->id,
                'user_id' => $user->id,
                'request_type' => $data['request_type'],
                'reason' => $data['reason'],
                'preferred_flight_id' => $data['preferred_flight_id'] ?? null,
                'status' => 'submitted',
            ]);

            $request->loadMissing('booking.user');
            $this->notificationService->changeRequestSubmitted($request);

            return $request;
        });
    }

    public function process(User $actor, BookingChangeRequest $request, array $data): BookingChangeRequest
    {
        if (! $actor->isBackoffice()) {
            throw ValidationException::withMessages([
                'user' => ['Akses diproses hanya untuk tim backoffice.'],
            ]);
        }

        $nextStatus = $data['status'] ?? null;
        if (! in_array($nextStatus, BookingChangeRequestCatalog::statuses(), true)) {
            throw ValidationException::withMessages([
                'status' => ['Status request tidak valid.'],
            ]);
        }

        return DB::transaction(function () use ($request, $actor, $data, $nextStatus) {
            $request->loadMissing([
                'booking.payments',
                'booking.details.seat',
                'booking.flight',
                'preferredFlight',
                'user',
            ]);

            $request->fill([
                'status' => $nextStatus,
                'admin_notes' => $data['admin_notes'] ?? $request->admin_notes,
                'resolution_amount' => $data['resolution_amount'] ?? $request->resolution_amount,
                'resolution_details' => $data['resolution_details'] ?? $request->resolution_details,
                'processed_by' => $actor->id,
                'processed_at' => now(),
            ])->save();

            if ($nextStatus === 'completed') {
                $this->applyCompletion($request);
            }

            $request = $request->fresh([
                'booking.user',
                'booking.flight.departureAirport',
                'booking.flight.arrivalAirport',
                'preferredFlight.departureAirport',
                'preferredFlight.arrivalAirport',
                'user',
                'processedByUser',
            ]);

            $this->notificationService->changeRequestUpdated($request);

            return $request;
        });
    }

    protected function applyCompletion(BookingChangeRequest $request): void
    {
        $booking = $request->booking;
        if (! $booking) {
            return;
        }

        if (in_array($request->request_type, ['refund', 'cancel_request'], true)) {
            $booking->update([
                'status' => 'cancelled',
                'expired_at' => now(),
            ]);

            $booking->payments()
                ->where('payment_status', 'pending')
                ->update(['payment_status' => 'failed']);

            $latestPaidPayment = $booking->payments()
                ->where('payment_status', 'paid')
                ->latest('paid_at')
                ->first();

            if ($latestPaidPayment) {
                $latestPaidPayment->update([
                    'payment_status' => 'refunded',
                ]);
            }

            return;
        }

        if ($request->request_type !== 'reschedule' || ! $request->preferred_flight_id) {
            return;
        }

        $preferredFlight = $request->preferredFlight;
        $currentFlight = $booking->flight;

        if (! $preferredFlight || ! $currentFlight) {
            return;
        }

        if ($preferredFlight->airplane_id !== $currentFlight->airplane_id) {
            throw ValidationException::withMessages([
                'preferred_flight_id' => ['Auto reschedule hanya didukung jika tipe pesawat sama agar alokasi seat tetap valid.'],
            ]);
        }

        if ($preferredFlight->departure_time?->lte(now())) {
            throw ValidationException::withMessages([
                'preferred_flight_id' => ['Flight tujuan reschedule harus berada di masa depan.'],
            ]);
        }

        $booking->update([
            'flight_id' => $preferredFlight->id,
            'status' => in_array($booking->status, ['confirmed', 'completed'], true) ? $booking->status : 'pending',
        ]);
    }

    public function queryForAdmin(array $filters = []): Builder
    {
        return BookingChangeRequest::query()
            ->with([
                'booking.user',
                'booking.flight.departureAirport',
                'booking.flight.arrivalAirport',
                'preferredFlight.departureAirport',
                'preferredFlight.arrivalAirport',
                'processedByUser',
            ])
            ->when(($filters['status'] ?? '') !== '', fn (Builder $query) => $query->where('status', $filters['status']))
            ->when(($filters['type'] ?? '') !== '', fn (Builder $query) => $query->where('request_type', $filters['type']))
            ->when(($filters['search'] ?? '') !== '', function (Builder $query) use ($filters) {
                $search = (string) $filters['search'];

                $query->where(function (Builder $nested) use ($search) {
                    $nested
                        ->whereHas('booking', fn (Builder $booking) => $booking->where('booking_code', 'like', '%'.$search.'%'))
                        ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', '%'.$search.'%'));
                });
            });
    }
}
