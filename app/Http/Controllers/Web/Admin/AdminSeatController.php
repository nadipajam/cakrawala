<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airplane;
use App\Models\Seat;
use Illuminate\Http\Request;

class AdminSeatController extends Controller
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
            ->paginate(15)
            ->withQueryString();

        $airplanes = Airplane::query()
            ->with('airline')
            ->orderBy('model')
            ->get();

        return view('admin.seats.index', compact('seats', 'airplanes', 'airplaneId', 'class', 'search'));
    }

    public function create()
    {
        $airplanes = Airplane::query()->with('airline')->orderBy('model')->get();

        return view('admin.seats.create', compact('airplanes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'airplane_id' => ['required', 'exists:airplanes,id'],
            'seat_number' => ['required', 'string', 'max:10'],
            'class' => ['required', 'in:economy,business,first'],
        ]);

        $exists = Seat::query()
            ->where('airplane_id', $data['airplane_id'])
            ->where('seat_number', strtoupper($data['seat_number']))
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['seat_number' => 'Seat number sudah dipakai pada airplane ini.']);
        }

        $data['seat_number'] = strtoupper((string) $data['seat_number']);

        Seat::create($data);

        return redirect()->route('admin.seats.index')->with('status', 'Seat berhasil dibuat.');
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

        return view('admin.seats.show', compact('seat'));
    }

    public function edit(Seat $seat)
    {
        $airplanes = Airplane::query()->with('airline')->orderBy('model')->get();

        return view('admin.seats.edit', compact('seat', 'airplanes'));
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
            return back()->withInput()->withErrors(['seat_number' => 'Seat number sudah dipakai pada airplane ini.']);
        }

        $seat->update($data);

        return redirect()->route('admin.seats.index')->with('status', 'Seat berhasil diperbarui.');
    }

    public function destroy(Seat $seat)
    {
        $usedInBooking = $seat->bookingDetails()->exists();

        if ($usedInBooking) {
            return back()->withErrors(['seat' => 'Seat tidak dapat dihapus karena sudah dipakai booking.']);
        }

        $seat->delete();

        return redirect()->route('admin.seats.index')->with('status', 'Seat berhasil dihapus.');
    }
}
