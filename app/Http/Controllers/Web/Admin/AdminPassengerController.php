<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Passenger;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Http\Request;

class AdminPassengerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $userId = $request->integer('user_id');

        $passengers = Passenger::query()
            ->with('user')
            ->when($search !== '', fn ($query) => $query->where('full_name', 'like', "%{$search}%"))
            ->when($userId > 0, fn ($query) => $query->where('user_id', $userId))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $users = User::query()
            ->whereIn('role', UserRole::customerValues())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.passengers.index', compact('passengers', 'users', 'search', 'userId'));
    }

    public function show(Passenger $passenger)
    {
        $passenger->load(['user', 'bookingDetails.booking.flight', 'bookingDetails.seat', 'bookingDetails.ticket']);

        return view('admin.passengers.show', compact('passenger'));
    }
}
