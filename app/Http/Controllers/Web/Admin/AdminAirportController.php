<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminAirportController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $city = trim((string) $request->string('city'));
        $country = trim((string) $request->string('country'));

        $airports = Airport::query()
            ->withCount(['departureFlights', 'arrivalFlights'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($qq) use ($search) {
                    $qq->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($city !== '', fn ($query) => $query->where('city', 'like', "%{$city}%"))
            ->when($country !== '', fn ($query) => $query->where('country', 'like', "%{$country}%"))
            ->orderBy('code')
            ->paginate(12)
            ->withQueryString();

        return view('admin.airports.index', compact('airports', 'search', 'city', 'country'));
    }

    public function create()
    {
        return view('admin.airports.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:airports,code'],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
        ]);

        Airport::create($data);

        return redirect()->route('admin.airports.index')->with('status', 'Airport berhasil dibuat.');
    }

    public function show(Airport $airport)
    {
        $airport->load(['departureFlights.airline', 'arrivalFlights.airline']);
        $airport->loadCount(['departureFlights', 'arrivalFlights']);

        return view('admin.airports.show', compact('airport'));
    }

    public function edit(Airport $airport)
    {
        return view('admin.airports.edit', compact('airport'));
    }

    public function update(Request $request, Airport $airport)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('airports', 'code')->ignore($airport->id)],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
        ]);

        $airport->update($data);

        return redirect()->route('admin.airports.index')->with('status', 'Airport berhasil diperbarui.');
    }

    public function destroy(Airport $airport)
    {
        $activeFlightsExist = $airport->departureFlights()->whereIn('status', ['scheduled', 'delayed'])->exists()
            || $airport->arrivalFlights()->whereIn('status', ['scheduled', 'delayed'])->exists();

        if ($activeFlightsExist) {
            return back()->withErrors(['airport' => 'Airport tidak bisa dihapus karena masih dipakai flight aktif.']);
        }

        $airport->delete();

        return redirect()->route('admin.airports.index')->with('status', 'Airport berhasil dihapus.');
    }
}
