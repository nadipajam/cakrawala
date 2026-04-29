<?php

namespace App\Services;

use App\Models\BookingDetail;
use App\Models\Flight;
use App\Models\Seat;
use App\Support\CabinClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class FlightService
{
    public function __construct(
        protected BookingExpiryService $bookingExpiryService
    ) {
    }

    public function search(array $filters = []): Builder
    {
        $departureDate = $filters['departure_date'] ?? $filters['date'] ?? null;

        return Flight::query()
            ->with(['airline', 'airplane', 'departureAirport', 'arrivalAirport'])
            ->when($filters['departure_airport_id'] ?? null, fn (Builder $query, $id) => $query->where('departure_airport_id', $id))
            ->when($filters['arrival_airport_id'] ?? null, fn (Builder $query, $id) => $query->where('arrival_airport_id', $id))
            ->when($departureDate, fn (Builder $query, $date) => $query->whereDate('departure_time', $date))
            ->when($filters['passengers'] ?? null, function (Builder $query, $passengers) {
                $count = max((int) $passengers, 1);

                $query->whereHas('airplane', fn (Builder $airplane) => $airplane->where('capacity', '>=', $count));
            })
            ->when($filters['class'] ?? null, function (Builder $query, $class) {
                $query->whereHas('airplane.seats', fn (Builder $seat) => $seat->where('class', $class));
            })
            ->when($filters['airline_id'] ?? null, fn (Builder $query, $id) => $query->where('airline_id', $id))
            ->when($filters['status'] ?? null, fn (Builder $query, $status) => $query->where('status', $status))
            ->orderBy('departure_time');
    }

    public function availableSeats(Flight $flight, ?string $class = null): Collection
    {
        $this->bookingExpiryService->expirePendingBookings($flight->id);

        $bookedSeatIds = BookingDetail::query()
            ->whereHas('booking', function (Builder $query) use ($flight) {
                $query->where('flight_id', $flight->id)
                    ->whereIn('status', ['pending', 'confirmed', 'completed']);
            })
            ->pluck('seat_id');

        return Seat::query()
            ->where('airplane_id', $flight->airplane_id)
            ->when(CabinClass::isValid($class), fn (Builder $query) => $query->where('class', $class))
            ->whereNotIn('id', $bookedSeatIds)
            ->orderByRaw('LENGTH(seat_number)')
            ->orderBy('seat_number')
            ->get();
    }

    public function classPrices(Flight $flight): Collection
    {
        return collect(CabinClass::all())
            ->mapWithKeys(fn (array $config, string $class) => [$class => CabinClass::price((float) $flight->price, $class)]);
    }

    public function availableSeatCounts(Flight $flight): Collection
    {
        return collect(CabinClass::ORDER)
            ->mapWithKeys(fn (string $class) => [$class => $this->availableSeats($flight, $class)->count()]);
    }
}
