<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Seat;
use App\Models\User;
use App\Support\CabinClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        protected PortalNotificationService $notificationService,
        protected BookingExpiryService $bookingExpiryService
    ) {
    }

    public function createBooking(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {
            $flight = Flight::query()->with('airplane')->findOrFail($data['flight_id']);
            $this->bookingExpiryService->expirePendingBookings($flight->id);
            $seatClass = CabinClass::normalize($data['seat_class'] ?? null);

            $requestedPassengers = collect($data['passengers']);
            $passengerIds = $requestedPassengers->pluck('passenger_id');
            $seatIds = $requestedPassengers->pluck('seat_id');

            if ($passengerIds->unique()->count() !== $passengerIds->count()) {
                throw ValidationException::withMessages([
                    'passengers' => ['Passenger tidak boleh dipilih lebih dari sekali dalam satu booking.'],
                ]);
            }

            if ($seatIds->unique()->count() !== $seatIds->count()) {
                throw ValidationException::withMessages([
                    'passengers' => ['Seat tidak boleh dipilih lebih dari sekali dalam satu booking.'],
                ]);
            }

            $ownedPassengers = Passenger::query()
                ->where('user_id', $user->id)
                ->whereIn('id', $passengerIds)
                ->count();

            if ($ownedPassengers !== $passengerIds->count()) {
                throw ValidationException::withMessages([
                    'passengers' => ['Ada passenger yang tidak dimiliki user login.'],
                ]);
            }

            $validSeats = Seat::query()
                ->where('airplane_id', $flight->airplane_id)
                ->where('class', $seatClass)
                ->whereIn('id', $seatIds)
                ->count();

            if ($validSeats !== $seatIds->count()) {
                throw ValidationException::withMessages([
                    'passengers' => ['Ada seat yang tidak valid untuk flight ini atau tidak sesuai class kabin terpilih.'],
                ]);
            }

            $seatAlreadyBooked = BookingDetail::query()
                ->whereIn('seat_id', $seatIds)
                ->whereHas('booking', function (Builder $query) use ($flight) {
                    $query->where('flight_id', $flight->id)
                        ->whereIn('status', ['pending', 'confirmed', 'completed']);
                })
                ->exists();

            if ($seatAlreadyBooked) {
                throw ValidationException::withMessages([
                    'seat_id' => ['Seat sudah dipesan.'],
                ]);
            }

            $totalPassengers = $requestedPassengers->count();
            $pricePerPassenger = CabinClass::price((float) $flight->price, $seatClass);
            $totalPrice = $pricePerPassenger * $totalPassengers;

            $booking = Booking::create([
                'user_id' => $user->id,
                'flight_id' => $flight->id,
                'booking_code' => $this->generateBookingCode(),
                'total_passengers' => $totalPassengers,
                'total_price' => $totalPrice,
                'status' => 'pending',
                // Timeout tidak dipasang saat booking dibuat.
                // Batas 5 menit hanya berlaku ketika metode QRIS dipilih saat pembayaran.
                'expired_at' => null,
            ]);

            foreach ($requestedPassengers as $item) {
                BookingDetail::create([
                    'booking_id' => $booking->id,
                    'passenger_id' => $item['passenger_id'],
                    'seat_id' => $item['seat_id'],
                    'price' => $pricePerPassenger,
                ]);
            }

            Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => 'unassigned',
                'amount' => $totalPrice,
                'payment_status' => 'pending',
            ]);

            $booking = $booking->load([
                'flight.airline',
                'flight.departureAirport',
                'flight.arrivalAirport',
                'details.passenger',
                'details.seat',
                'details.ticket',
                'payments',
            ]);

            $this->notificationService->bookingCreated($booking);

            return $booking;
        });
    }

    public function cancelBooking(User $user, Booking $booking): Booking
    {
        if ($booking->user_id !== $user->id && ! $user->isBackoffice()) {
            throw ValidationException::withMessages([
                'booking' => ['Booking tidak dapat diakses.'],
            ]);
        }

        if ($booking->status !== 'pending') {
            throw ValidationException::withMessages([
                'booking' => ['Hanya booking pending yang dapat dibatalkan.'],
            ]);
        }

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

        $booking = $booking->fresh([
            'flight',
            'details.passenger',
            'details.seat',
            'payments',
        ]);

        $this->notificationService->bookingCancelled($booking);

        return $booking;
    }

    protected function generateBookingCode(): string
    {
        return 'BK-' . now()->format('YmdHis') . '-' . random_int(100, 999);
    }
}
