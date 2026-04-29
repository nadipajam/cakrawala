<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seat;
use Illuminate\Http\Request;

class SeatController extends Controller
{
    public function index(Request $request)
    {
        $airplaneId = $request->integer('airplane_id');
        $class = trim((string) $request->string('class'));
        $search = trim((string) $request->string('search'));

        $seats = Seat::query()
            ->with('airplane.airline')
            ->when($airplaneId > 0, fn ($query) => $query->where('airplane_id', $airplaneId))
            ->when(in_array($class, ['economy', 'business', 'first'], true), fn ($query) => $query->where('class', $class))
            ->when($search !== '', fn ($query) => $query->where('seat_number', 'like', "%{$search}%"))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse($seats, 'Data seat berhasil diambil');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'airplane_id' => ['required', 'exists:airplanes,id'],
            'seat_number' => ['required', 'string', 'max:10'],
            'class' => ['required', 'in:economy,business,first'],
        ]);

        $data['seat_number'] = strtoupper((string) $data['seat_number']);

        $exists = Seat::query()
            ->where('airplane_id', $data['airplane_id'])
            ->where('seat_number', $data['seat_number'])
            ->exists();

        if ($exists) {
            return $this->errorResponse('Seat number sudah dipakai pada airplane ini.', 422);
        }

        $seat = Seat::create($data)->load('airplane.airline');

        return $this->successResponse($seat, 'Seat berhasil dibuat', 201);
    }

    public function show(Seat $seat)
    {
        $seat->load([
            'airplane.airline',
            'bookingDetails.booking.user',
            'bookingDetails.booking.flight',
            'bookingDetails.passenger',
            'bookingDetails.ticket',
        ]);

        return $this->successResponse($seat, 'Detail seat berhasil diambil');
    }

    public function update(Request $request, Seat $seat)
    {
        $data = $request->validate([
            'airplane_id' => ['required', 'exists:airplanes,id'],
            'seat_number' => ['required', 'string', 'max:10'],
            'class' => ['required', 'in:economy,business,first'],
        ]);

        $data['seat_number'] = strtoupper((string) $data['seat_number']);

        $exists = Seat::query()
            ->where('airplane_id', $data['airplane_id'])
            ->where('seat_number', $data['seat_number'])
            ->where('id', '!=', $seat->id)
            ->exists();

        if ($exists) {
            return $this->errorResponse('Seat number sudah dipakai pada airplane ini.', 422);
        }

        $seat->update($data);

        return $this->successResponse($seat->fresh('airplane.airline'), 'Seat berhasil diperbarui');
    }

    public function destroy(Seat $seat)
    {
        if ($seat->bookingDetails()->exists()) {
            return $this->errorResponse('Seat tidak dapat dihapus karena sudah dipakai booking.', 422);
        }

        $seat->delete();

        return $this->successResponse(null, 'Seat berhasil dihapus');
    }
}
