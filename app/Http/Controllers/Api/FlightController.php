<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FlightResource;
use App\Models\Airplane;
use App\Models\Flight;
use App\Services\FlightService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class FlightController extends Controller
{
    public function __construct(
        protected FlightService $flightService
    ) {
    }

    #[OA\Get(
        path: '/api/v1/flights',
        tags: ['Flights'],
        summary: 'Daftar atau pencarian flight',
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function index(Request $request)
    {
        $flights = $this->flightService
            ->search($request->only([
                'departure_airport_id',
                'arrival_airport_id',
                'date',
                'airline_id',
                'status',
            ]))
            ->limit(20)
            ->get();

        return $this->successResponse(FlightResource::collection($flights), 'Data berhasil diambil');
    }

    #[OA\Post(
        path: '/api/v1/admin/flights',
        tags: ['Flights'],
        summary: 'Tambah flight (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Berhasil')]
    )]
    public function store(Request $request)
    {
        $data = $request->validate([
            'airline_id' => ['required', 'exists:airlines,id'],
            'airplane_id' => ['required', 'exists:airplanes,id'],
            'departure_airport_id' => ['required', 'exists:airports,id', 'different:arrival_airport_id'],
            'arrival_airport_id' => ['required', 'exists:airports,id'],
            'flight_number' => ['required', 'string', 'max:50', 'unique:flights,flight_number'],
            'departure_time' => ['required', 'date'],
            'arrival_time' => ['required', 'date', 'after:departure_time'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:scheduled,delayed,cancelled,completed'],
        ]);

        $this->ensureAirplaneMatchesAirline($data['airline_id'], $data['airplane_id']);

        $flight = Flight::create([
            ...$data,
            'status' => $data['status'] ?? 'scheduled',
        ])->load(['airline', 'airplane', 'departureAirport', 'arrivalAirport']);

        return $this->successResponse(new FlightResource($flight), 'Flight berhasil dibuat', 201);
    }

    #[OA\Get(
        path: '/api/v1/flights/{id}',
        tags: ['Flights'],
        summary: 'Detail flight',
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function show(Flight $flight)
    {
        $flight->load(['airline', 'airplane', 'departureAirport', 'arrivalAirport']);

        return $this->successResponse(new FlightResource($flight), 'Data berhasil diambil');
    }

    #[OA\Put(
        path: '/api/v1/admin/flights/{id}',
        tags: ['Flights'],
        summary: 'Update flight (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function update(Request $request, Flight $flight)
    {
        $data = $request->validate([
            'airline_id' => ['required', 'exists:airlines,id'],
            'airplane_id' => ['required', 'exists:airplanes,id'],
            'departure_airport_id' => ['required', 'exists:airports,id', 'different:arrival_airport_id'],
            'arrival_airport_id' => ['required', 'exists:airports,id'],
            'flight_number' => ['required', 'string', 'max:50', 'unique:flights,flight_number,'.$flight->id],
            'departure_time' => ['required', 'date'],
            'arrival_time' => ['required', 'date', 'after:departure_time'],
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:scheduled,delayed,cancelled,completed'],
        ]);

        $this->ensureAirplaneMatchesAirline($data['airline_id'], $data['airplane_id']);

        $flight->update($data);
        $flight->load(['airline', 'airplane', 'departureAirport', 'arrivalAirport']);

        return $this->successResponse(new FlightResource($flight), 'Flight berhasil diperbarui');
    }

    #[OA\Delete(
        path: '/api/v1/admin/flights/{id}',
        tags: ['Flights'],
        summary: 'Hapus flight (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function destroy(Flight $flight)
    {
        $flight->delete();

        return $this->successResponse(null, 'Flight berhasil dihapus');
    }

    #[OA\Get(
        path: '/api/v1/flights/{id}/available-seats',
        tags: ['Flights'],
        summary: 'Daftar seat tersedia pada flight',
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function availableSeats(Flight $flight)
    {
        $seats = $this->flightService
            ->availableSeats($flight)
            ->map(fn ($seat) => [
                'id' => $seat->id,
                'seat_number' => $seat->seat_number,
                'class' => $seat->class,
            ])
            ->values();

        return $this->successResponse($seats, 'Data berhasil diambil');
    }

    public function updateStatus(Request $request, Flight $flight)
    {
        $data = $request->validate([
            'status' => ['required', 'in:scheduled,delayed,cancelled,completed'],
        ]);

        $flight->update(['status' => $data['status']]);

        return $this->successResponse(new FlightResource($flight->fresh(['airline', 'airplane', 'departureAirport', 'arrivalAirport'])), 'Status flight berhasil diperbarui');
    }

    protected function ensureAirplaneMatchesAirline(int $airlineId, int $airplaneId): void
    {
        $airplane = Airplane::query()->find($airplaneId);

        if ($airplane && (int) $airplane->airline_id !== $airlineId) {
            throw ValidationException::withMessages([
                'airplane_id' => ['Airplane harus sesuai dengan airline terpilih.'],
            ]);
        }
    }
}
