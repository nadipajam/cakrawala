<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Passenger;
use Illuminate\Http\Request;

class PassengerController extends Controller
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
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse($passengers, 'Data passenger berhasil diambil');
    }

    public function show(Passenger $passenger)
    {
        $passenger->load([
            'user',
            'bookingDetails.booking.flight',
            'bookingDetails.booking.user',
            'bookingDetails.seat',
            'bookingDetails.ticket',
        ]);

        return $this->successResponse($passenger, 'Detail passenger berhasil diambil');
    }
}
