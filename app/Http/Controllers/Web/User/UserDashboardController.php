<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use App\Models\BookingAddon;
use App\Models\BookingChangeRequest;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use App\Services\BookingExpiryService;

class UserDashboardController extends Controller
{
    public function __construct(
        protected BookingExpiryService $bookingExpiryService
    ) {
    }

    public function index(Request $request)
    {
        $this->bookingExpiryService->expirePendingBookings();

        $bookings = $request->user()->bookings()
            ->with(['flight.airline'])
            ->latest()
            ->limit(5)
            ->get();

        return view('user.dashboard', [
            'stats' => [
                'active_bookings' => $request->user()->bookings()->whereIn('status', ['pending', 'confirmed'])->count(),
                'pending_payments' => $request->user()->bookings()->where('status', 'pending')->count(),
                'completed_trips' => $request->user()->bookings()->where('status', 'completed')->count(),
                'saved_passengers' => $request->user()->passengers()->count(),
                'unread_notifications' => $request->user()->unreadNotifications()->count(),
                'open_support_cases' => ContactMessage::query()
                    ->where('user_id', $request->user()->id)
                    ->whereIn('status', ['open', 'in_progress'])
                    ->count(),
                'pending_checkins' => $request->user()->bookings()
                    ->whereIn('status', ['confirmed', 'completed'])
                    ->whereHas('details', fn ($query) => $query->where('boarding_status', 'not_checked_in'))
                    ->count(),
                'active_addons' => BookingAddon::query()
                    ->whereHas('booking', fn ($query) => $query->where('user_id', $request->user()->id))
                    ->where('status', 'selected')
                    ->count(),
                'open_change_requests' => BookingChangeRequest::query()
                    ->where('user_id', $request->user()->id)
                    ->whereIn('status', ['submitted', 'in_review', 'approved'])
                    ->count(),
            ],
            'bookings' => $bookings,
            'notifications' => $request->user()->notifications()->latest()->limit(4)->get(),
        ]);
    }
}
