<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addon\StoreBookingAddonRequest;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Services\BookingAddonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingAddonWebController extends Controller
{
    public function __construct(
        protected BookingAddonService $addonService
    ) {
    }

    public function index(Request $request, Booking $booking): View
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $booking->load([
            'flight.airline',
            'flight.departureAirport',
            'flight.arrivalAirport',
            'details.passenger',
            'details.seat',
            'addons.bookingDetail.passenger',
            'payments',
        ]);

        return view('user.bookings.addons', [
            'booking' => $booking,
            'catalog' => $this->addonService->catalog(),
            'addons' => $this->addonService->addonsForBooking($booking),
            'latestPayment' => $booking->payments->sortByDesc('created_at')->first(),
        ]);
    }

    public function store(StoreBookingAddonRequest $request, Booking $booking): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);

        $this->addonService->add($request->user(), $booking, $request->validated());

        return back()->with('status', 'Add-on berhasil ditambahkan.');
    }

    public function destroy(Request $request, Booking $booking, BookingAddon $addon): RedirectResponse
    {
        abort_unless($booking->user_id === $request->user()->id, 403);
        abort_unless($addon->booking_id === $booking->id, 404);

        $this->addonService->cancel($request->user(), $addon);

        return back()->with('status', 'Add-on berhasil dibatalkan.');
    }
}
