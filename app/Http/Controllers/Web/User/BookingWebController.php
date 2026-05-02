<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\Flight;
use App\Services\BookingService;
use App\Services\BookingExpiryService;
use App\Services\FlightService;
use App\Services\SeatMapService;
use App\Support\CabinClass;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingWebController extends Controller
{
    public function __construct(
        protected BookingService $bookingService,
        protected BookingExpiryService $bookingExpiryService,
        protected FlightService $flightService,
        protected SeatMapService $seatMapService,
    ) {
    }

    public function index(Request $request): View
    {
        $bookings = $request->user()->bookings()
            ->with([
                'flight.airline',
                'flight.departureAirport',
                'flight.arrivalAirport',
                'details.passenger',
                'details.seat',
                'payments',
            ])
            ->latest()
            ->paginate(10);

        $bookings->getCollection()->transform(
            fn (Booking $booking) => $this->bookingExpiryService->expireIfNeeded($booking)
        );

        return view('user.bookings.index', [
            'bookings' => $bookings,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $flightId = $request->integer('flight');
        $selectedClass = CabinClass::normalize($request->string('class')->toString() ?: null);
        $flight = Flight::query()
            ->with(['airline', 'airplane.seats', 'departureAirport', 'arrivalAirport'])
            ->find($flightId);

        if (! $flight) {
            return redirect()
                ->route('flights.index')
                ->with('status', 'Pilih flight terlebih dahulu sebelum booking.')
                ->with('status_type', 'warning');
        }

        $this->bookingExpiryService->expirePendingBookings($flight->id);

        $availableSeats = $this->flightService->availableSeats($flight);
        $availableSeatCounts = $this->flightService->availableSeatCounts($flight);
        $bookedSeatIds = BookingDetail::query()
            ->whereHas('booking', function ($query) use ($flight) {
                $query->where('flight_id', $flight->id)
                    ->whereIn('status', ['pending', 'confirmed', 'completed']);
            })
            ->pluck('seat_id');

        $passengers = $request->user()
            ->passengers()
            ->orderBy('full_name')
            ->get();

        return view('user.booking.create', [
            'flight' => $flight,
            'passengers' => $passengers,
            'availableSeats' => $availableSeats,
            'bookedSeatIds' => $bookedSeatIds,
            'seatMap' => $this->seatMapService->build($flight->airplane->seats, $availableSeats->pluck('id')),
            'selectedClass' => $selectedClass,
            'availableSeatCounts' => $availableSeatCounts,
            'classPrices' => $this->flightService->classPrices($flight),
        ]);
    }

    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = $this->bookingService->createBooking($request->user(), $request->validated());

        return redirect()
            ->route('payments.create', ['booking' => $booking->id])
            ->with('status', 'Booking berhasil dibuat dengan kode '.$booking->booking_code)
            ->with('status_type', 'success');
    }

    public function show(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking = $this->bookingExpiryService->expireIfNeeded($booking);

        $booking->load([
            'flight.airline',
            'flight.departureAirport',
            'flight.arrivalAirport',
            'details.passenger',
            'details.seat',
            'details.ticket',
            'details.addons',
            'addons.bookingDetail.passenger',
            'changeRequests.preferredFlight.departureAirport',
            'changeRequests.preferredFlight.arrivalAirport',
            'changeRequests.processedByUser',
            'payments',
        ]);

        return view('user.bookings.show', [
            'booking' => $booking,
            'latestPayment' => $booking->payments->sortByDesc('created_at')->first(),
        ]);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->user_id !== $request->user()->id) {
            abort(403);
        }

        if ($booking->status === 'cancelled') {
            return redirect()
                ->route('my-bookings.show', $booking)
                ->with('status', 'Booking ini sudah dibatalkan sebelumnya.')
                ->with('status_type', 'warning');
        }

        $this->bookingService->cancelBooking($request->user(), $booking);

        return redirect()
            ->route('my-bookings.show', $booking)
            ->with('status', 'Booking berhasil dibatalkan.')
            ->with('status_type', 'success');
    }
}
