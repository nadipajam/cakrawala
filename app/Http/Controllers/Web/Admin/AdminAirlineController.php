<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminAirlineController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));

        $airlines = Airline::query()
            ->withCount(['airplanes', 'flights'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.airlines.index', compact('airlines', 'search'));
    }

    public function create()
    {
        return view('admin.airlines.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:airlines,code'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('airlines', 'public');
        }

        Airline::create($data);

        return redirect()->route('admin.airlines.index')->with('status', 'Airline berhasil dibuat.');
    }

    public function show(Airline $airline)
    {
        $airline->load([
            'airplanes' => fn ($query) => $query->latest()->limit(10),
            'flights' => fn ($query) => $query->with(['departureAirport', 'arrivalAirport'])->latest()->limit(10),
        ]);
        $airline->loadCount(['airplanes', 'flights']);

        $bookingCount = $airline->flights()
            ->withCount('bookings')
            ->get()
            ->sum('bookings_count');

        return view('admin.airlines.show', compact('airline', 'bookingCount'));
    }

    public function edit(Airline $airline)
    {
        return view('admin.airlines.edit', compact('airline'));
    }

    public function update(Request $request, Airline $airline)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', Rule::unique('airlines', 'code')->ignore($airline->id)],
            'logo' => ['nullable', 'image', 'max:2048'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('airlines', 'public');
        }

        $airline->update($data);

        return redirect()->route('admin.airlines.index')->with('status', 'Airline berhasil diperbarui.');
    }

    public function destroy(Airline $airline)
    {
        $airline->delete();

        return redirect()->route('admin.airlines.index')->with('status', 'Airline berhasil dihapus.');
    }
}
