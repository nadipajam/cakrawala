<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $role = trim((string) $request->string('role'));

        $users = User::query()
            ->withCount(['passengers', 'bookings'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, UserRole::values(), true), fn ($query) => $query->where('role', $role))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse($users, 'Data user berhasil diambil');
    }

    public function show(User $user)
    {
        $user->loadCount(['passengers', 'bookings']);
        $user->load([
            'passengers' => fn ($query) => $query->latest()->limit(10),
            'bookings' => fn ($query) => $query->latest()->limit(10),
        ]);

        return $this->successResponse($user, 'Detail user berhasil diambil');
    }

    public function bookings(User $user, Request $request)
    {
        $bookings = Booking::query()
            ->with(['flight.airline', 'flight.departureAirport', 'flight.arrivalAirport', 'payments'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse($bookings, 'Data booking user berhasil diambil');
    }

    public function passengers(User $user, Request $request)
    {
        $passengers = Passenger::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse($passengers, 'Data passenger user berhasil diambil');
    }
}
