<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingAddon;
use App\Services\BookingAddonService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminAddonController extends Controller
{
    public function __construct(
        protected BookingAddonService $bookingAddonService
    ) {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $type = trim((string) $request->string('type'));

        $addons = BookingAddon::query()
            ->with([
                'booking.user',
                'booking.flight.departureAirport',
                'booking.flight.arrivalAirport',
                'bookingDetail.passenger',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested
                        ->where('addon_name', 'like', "%{$search}%")
                        ->orWhereHas('booking', fn ($booking) => $booking->where('booking_code', 'like', "%{$search}%"))
                        ->orWhereHas('booking.user', fn ($user) => $user->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('bookingDetail.passenger', fn ($passenger) => $passenger->where('full_name', 'like', "%{$search}%"));
                });
            })
            ->when(in_array($status, ['selected', 'paid', 'cancelled'], true), fn ($query) => $query->where('status', $status))
            ->when(in_array($type, ['baggage', 'priority', 'service', 'insurance'], true), fn ($query) => $query->where('addon_type', $type))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.addons.index', compact('addons', 'search', 'status', 'type'));
    }

    public function updateStatus(Request $request, BookingAddon $addon): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['selected', 'paid', 'cancelled'])],
        ]);

        $addon->update([
            'status' => $data['status'],
        ]);

        if ($addon->booking) {
            $booking = $this->bookingAddonService->recalculateBookingTotal($addon->booking);
            $this->bookingAddonService->syncPendingPayment($booking);
        }

        return back()->with('status', 'Status add-on berhasil diperbarui.');
    }
}
