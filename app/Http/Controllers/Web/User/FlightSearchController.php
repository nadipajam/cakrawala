<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\BookingDetail;
use App\Models\Flight;
use App\Services\FlightService;
use App\Services\BookingExpiryService;
use App\Services\SeatMapService;
use App\Support\CabinClass;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class FlightSearchController extends Controller
{
    public function __construct(
        protected BookingExpiryService $bookingExpiryService,
        protected FlightService $flightService,
        protected SeatMapService $seatMapService,
    ) {
    }

    public function index(Request $request): View
    {
        $airports = Airport::query()->orderBy('city')->get();
        $airlines = Airline::query()->orderBy('name')->limit(12)->get();
        $filters = $this->resolveHomeFilters($request);

        $flights = $this->flightService
            ->search($filters)
            ->whereIn('status', ['scheduled', 'delayed'])
            ->limit(12)
            ->get();

        $featuredFlights = Flight::query()
            ->with(['airline', 'airplane', 'departureAirport', 'arrivalAirport'])
            ->whereIn('status', ['scheduled', 'delayed'])
            ->withCount('bookings')
            ->orderByDesc('bookings_count')
            ->orderBy('departure_time')
            ->limit(6)
            ->get();

        $seatAvailability = $this->mapSeatAvailability(
            $flights->merge($featuredFlights)
        );

        $userShortcuts = null;
        $recentBookings = collect();

        if ($request->user()) {
            $user = $request->user();

            $userShortcuts = [
                'bookings' => $user->bookings()->count(),
                'passengers' => $user->passengers()->count(),
                'pending_payments' => $user->bookings()->where('status', 'pending')->count(),
                'active_trips' => $user->bookings()->whereIn('status', ['confirmed', 'completed'])->count(),
            ];

            $recentBookings = $user->bookings()
                ->with(['flight.departureAirport', 'flight.arrivalAirport', 'details.ticket'])
                ->latest()
                ->limit(5)
                ->get();
        }

        return view('welcome', [
            'airports' => $airports,
            'airlines' => $airlines,
            'flights' => $flights,
            'featuredFlights' => $featuredFlights,
            'seatAvailability' => $seatAvailability,
            'filters' => $filters,
            'userShortcuts' => $userShortcuts,
            'recentBookings' => $recentBookings,
        ]);
    }

    public function search(Request $request)
    {
        return $this->results($request);
    }

    public function results(Request $request): View
    {
        $airports = Airport::query()->orderBy('city')->get();
        $airlines = Airline::query()->orderBy('name')->get();
        $filters = $this->resolveSearchFilters($request);

        $query = $this->flightService
            ->search([
                'departure_airport_id' => $filters['from'],
                'arrival_airport_id' => $filters['to'],
                'date' => $filters['date'],
                'airline_id' => $filters['airline_id'],
                'class' => $filters['class'],
            ])
            ->whereIn('status', ['scheduled', 'delayed']);

        $this->applySearchExtraFilters($query, $filters);

        $flights = $query
            ->paginate(12)
            ->withQueryString();

        $seatAvailability = $this->mapSeatAvailability($flights->getCollection());

        return view('flights.index', [
            'airports' => $airports,
            'airlines' => $airlines,
            'flights' => $flights,
            'filters' => $filters,
            'seatAvailability' => $seatAvailability,
        ]);
    }

    public function show(Request $request, Flight $flight): View
    {
        $flight->load(['airline', 'airplane.seats', 'departureAirport', 'arrivalAirport']);
        $this->bookingExpiryService->expirePendingBookings($flight->id);

        $availableSeats = $this->flightService->availableSeats($flight);
        $selectedClass = CabinClass::normalize($request->string('class')->toString() ?: null);
        $bookedSeatIds = BookingDetail::query()
            ->whereHas('booking', function (Builder $query) use ($flight) {
                $query->where('flight_id', $flight->id)
                    ->whereIn('status', ['pending', 'confirmed', 'completed']);
            })
            ->pluck('seat_id');

        return view('flights.show', [
            'flight' => $flight,
            'availableSeats' => $availableSeats,
            'bookedSeatIds' => $bookedSeatIds,
            'selectedClass' => $selectedClass,
            'availableSeatCounts' => $this->flightService->availableSeatCounts($flight),
            'classPrices' => $this->flightService->classPrices($flight),
            'seatMap' => $this->seatMapService->build($flight->airplane->seats, $availableSeats->pluck('id')),
        ]);
    }

    protected function mapSeatAvailability(Collection $flights): Collection
    {
        $uniqueFlights = $flights->unique('id')->values();

        if ($uniqueFlights->isEmpty()) {
            return collect();
        }

        $this->bookingExpiryService->expirePendingBookings();

        $flightIds = $uniqueFlights->pluck('id');

        $bookedCounts = BookingDetail::query()
            ->selectRaw('bookings.flight_id, count(*) as booked_count')
            ->join('bookings', 'bookings.id', '=', 'booking_details.booking_id')
            ->whereIn('bookings.flight_id', $flightIds)
            ->whereIn('bookings.status', ['pending', 'confirmed', 'completed'])
            ->groupBy('bookings.flight_id')
            ->pluck('booked_count', 'bookings.flight_id');

        return $uniqueFlights->mapWithKeys(function (Flight $flight) use ($bookedCounts) {
            $capacity = (int) ($flight->airplane->capacity ?? 0);
            $booked = (int) ($bookedCounts[$flight->id] ?? 0);

            return [$flight->id => max($capacity - $booked, 0)];
        });
    }

    protected function resolveHomeFilters(Request $request): array
    {
        return [
            'departure_airport_id' => $request->input('departure_airport_id', $request->input('from')),
            'arrival_airport_id' => $request->input('arrival_airport_id', $request->input('to')),
            'departure_date' => $request->input('departure_date', $request->input('date')),
            'date' => $request->input('departure_date', $request->input('date')),
            'return_date' => $request->input('return_date'),
            'passengers' => $request->input('passengers', 1),
            'class' => $request->input('class'),
        ];
    }

    protected function resolveSearchFilters(Request $request): array
    {
        return [
            'from' => $request->input('from', $request->input('departure_airport_id')),
            'to' => $request->input('to', $request->input('arrival_airport_id')),
            'date' => $request->input('date', $request->input('departure_date')),
            'airline_id' => $request->input('airline_id'),
            'class' => $request->input('class'),
            'max_price' => $request->input('max_price'),
            'time' => $request->input('time'),
            'sort' => $request->input('sort', 'time_asc'),
        ];
    }

    protected function applySearchExtraFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['max_price'])) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        if (! empty($filters['time'])) {
            match ($filters['time']) {
                'morning' => $query->whereRaw('HOUR(departure_time) BETWEEN 5 AND 11'),
                'afternoon' => $query->whereRaw('HOUR(departure_time) BETWEEN 12 AND 16'),
                'evening' => $query->whereRaw('HOUR(departure_time) BETWEEN 17 AND 20'),
                'night' => $query->where(function (Builder $timeQuery) {
                    $timeQuery->whereRaw('HOUR(departure_time) BETWEEN 0 AND 4')
                        ->orWhereRaw('HOUR(departure_time) BETWEEN 21 AND 23');
                }),
                default => null,
            };
        }

        match ($filters['sort']) {
            'price_asc' => $query->reorder()->orderBy('price'),
            'price_desc' => $query->reorder()->orderByDesc('price'),
            'time_desc' => $query->reorder()->orderByDesc('departure_time'),
            default => $query->reorder()->orderBy('departure_time'),
        };
    }
}
