<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeRequest\StoreBookingChangeRequest;
use App\Models\Booking;
use App\Models\BookingChangeRequest;
use App\Models\Flight;
use App\Services\BookingChangeRequestService;
use App\Support\BookingChangeRequestCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingChangeRequestWebController extends Controller
{
    public function __construct(
        protected BookingChangeRequestService $changeRequestService
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        $bookings = $user->bookings()
            ->with(['flight.airline', 'flight.departureAirport', 'flight.arrivalAirport'])
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->latest()
            ->get();

        $changeRequests = BookingChangeRequest::query()
            ->with([
                'booking.flight.departureAirport',
                'booking.flight.arrivalAirport',
                'preferredFlight.departureAirport',
                'preferredFlight.arrivalAirport',
                'processedByUser',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $upcomingFlights = Flight::query()
            ->with(['airline', 'departureAirport', 'arrivalAirport'])
            ->where('departure_time', '>', now())
            ->orderBy('departure_time')
            ->limit(100)
            ->get();

        return view('user.change-requests.index', [
            'bookings' => $bookings,
            'changeRequests' => $changeRequests,
            'requestTypes' => BookingChangeRequestCatalog::types(),
            'upcomingFlights' => $upcomingFlights,
            'preselectedBookingId' => $request->integer('booking'),
        ]);
    }

    public function store(StoreBookingChangeRequest $request): RedirectResponse
    {
        $booking = Booking::query()
            ->where('user_id', $request->user()->id)
            ->findOrFail((int) $request->validated('booking_id'));

        $this->changeRequestService->submit($request->user(), $booking, $request->validated());

        return back()->with('status', 'Permintaan perubahan berhasil dikirim.');
    }
}
