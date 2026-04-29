<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Airline;
use App\Models\Airplane;
use App\Services\SeatMapService;
use App\Services\SeatGeneratorService;
use App\Support\CabinClass;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminAirplaneController extends Controller
{
    public function __construct(
        protected SeatGeneratorService $seatGeneratorService,
        protected SeatMapService $seatMapService,
    ) {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $airlineId = $request->integer('airline_id');

        $airplanes = Airplane::query()
            ->with('airline')
            ->withCount(['seats', 'flights'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($qq) use ($search) {
                    $qq->where('model', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%");
                });
            })
            ->when($airlineId > 0, fn ($query) => $query->where('airline_id', $airlineId))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $airlines = Airline::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.airplanes.index', compact('airplanes', 'airlines', 'search', 'airlineId'));
    }

    public function create()
    {
        $airlines = Airline::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.airplanes.create', compact('airlines'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'airline_id' => ['required', 'exists:airlines,id'],
            'model' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:50', 'unique:airplanes,registration_number'],
            'capacity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:3072'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('airplanes', 'public');
        }

        Airplane::create($data);

        return redirect()->route('admin.airplanes.index')->with('status', 'Airplane berhasil dibuat.');
    }

    public function show(Airplane $airplane)
    {
        $airplane->load([
            'airline',
            'seats' => fn ($query) => $query->orderByRaw('LENGTH(seat_number)')->orderBy('seat_number'),
            'flights' => fn ($query) => $query->with(['departureAirport', 'arrivalAirport'])->latest()->limit(12),
        ]);
        $airplane->loadCount(['seats', 'flights']);

        $seatMap = $this->seatMapService->build($airplane->seats, $airplane->seats->pluck('id'));
        $cabinSummary = collect(CabinClass::ORDER)
            ->mapWithKeys(function (string $class) use ($airplane) {
                $classSeats = $airplane->seats->where('class', $class);

                return [$class => [
                    'label' => CabinClass::label($class),
                    'seat_count' => $classSeats->count(),
                    'row_count' => $classSeats
                        ->pluck('seat_number')
                        ->map(fn (string $seatNumber) => (int) preg_replace('/\D+/', '', $seatNumber))
                        ->filter()
                        ->unique()
                        ->count(),
                ]];
            })
            ->all();

        return view('admin.airplanes.show', compact('airplane', 'seatMap', 'cabinSummary'));
    }

    public function edit(Airplane $airplane)
    {
        $airlines = Airline::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.airplanes.edit', compact('airplane', 'airlines'));
    }

    public function update(Request $request, Airplane $airplane)
    {
        $data = $request->validate([
            'airline_id' => ['required', 'exists:airlines,id'],
            'model' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:50', Rule::unique('airplanes', 'registration_number')->ignore($airplane->id)],
            'capacity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:3072'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('airplanes', 'public');
        }

        $airplane->update($data);

        return redirect()->route('admin.airplanes.index')->with('status', 'Airplane berhasil diperbarui.');
    }

    public function destroy(Airplane $airplane)
    {
        $airplane->delete();

        return redirect()->route('admin.airplanes.index')->with('status', 'Airplane berhasil dihapus.');
    }

    public function generateSeats(Request $request, Airplane $airplane)
    {
        $data = $request->validate([
            'first_rows' => ['nullable', 'integer', 'min:0'],
            'business_rows' => ['nullable', 'integer', 'min:0'],
            'reset' => ['nullable', 'boolean'],
        ]);

        $this->seatGeneratorService->generateForAirplane(
            $airplane,
            (int) ($data['first_rows'] ?? 0),
            (int) ($data['business_rows'] ?? 0),
            (bool) ($data['reset'] ?? false),
        );

        return back()->with('status', 'Layout kursi pesawat berhasil digenerate.');
    }
}
