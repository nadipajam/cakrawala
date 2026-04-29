<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Airplane;
use App\Services\SeatGeneratorService;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AirplaneController extends Controller
{
    public function __construct(
        protected SeatGeneratorService $seatGeneratorService
    ) {
    }

    #[OA\Get(
        path: '/api/v1/airplanes',
        tags: ['Airplanes'],
        summary: 'Daftar airplane',
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function index()
    {
        $airplanes = Airplane::query()
            ->with('airline')
            ->orderBy('model')
            ->get()
            ->map(fn (Airplane $airplane) => [
                'id' => $airplane->id,
                'airline_id' => $airplane->airline_id,
                'airline_name' => $airplane->airline?->name,
                'model' => $airplane->model,
                'registration_number' => $airplane->registration_number,
                'capacity' => $airplane->capacity,
                'description' => $airplane->description,
                'photo' => $airplane->photo,
            ]);

        return $this->successResponse($airplanes, 'Data berhasil diambil');
    }

    #[OA\Post(
        path: '/api/v1/admin/airplanes',
        tags: ['Airplanes'],
        summary: 'Tambah airplane (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Berhasil')]
    )]
    public function store(Request $request)
    {
        $data = $request->validate([
            'airline_id' => ['required', 'exists:airlines,id'],
            'model' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:50', 'unique:airplanes,registration_number'],
            'capacity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('airplanes', 'public');
        } elseif (! empty($data['photo']) && ! is_string($data['photo'])) {
            return $this->errorResponse('Format photo tidak valid.', 422);
        }

        $airplane = Airplane::create($data)->load('airline');

        return $this->successResponse([
            'id' => $airplane->id,
            'airline_id' => $airplane->airline_id,
            'airline_name' => $airplane->airline?->name,
            'model' => $airplane->model,
            'registration_number' => $airplane->registration_number,
            'capacity' => $airplane->capacity,
            'description' => $airplane->description,
            'photo' => $airplane->photo,
        ], 'Airplane berhasil dibuat', 201);
    }

    #[OA\Get(
        path: '/api/v1/admin/airplanes/{id}',
        tags: ['Airplanes'],
        summary: 'Detail airplane',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function show(Airplane $airplane)
    {
        $airplane->load('airline');

        return $this->successResponse([
            'id' => $airplane->id,
            'airline_id' => $airplane->airline_id,
            'airline_name' => $airplane->airline?->name,
            'model' => $airplane->model,
            'registration_number' => $airplane->registration_number,
            'capacity' => $airplane->capacity,
            'description' => $airplane->description,
            'photo' => $airplane->photo,
        ], 'Data berhasil diambil');
    }

    #[OA\Put(
        path: '/api/v1/admin/airplanes/{id}',
        tags: ['Airplanes'],
        summary: 'Update airplane (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function update(Request $request, Airplane $airplane)
    {
        $data = $request->validate([
            'airline_id' => ['required', 'exists:airlines,id'],
            'model' => ['required', 'string', 'max:255'],
            'registration_number' => ['required', 'string', 'max:50', 'unique:airplanes,registration_number,'.$airplane->id],
            'capacity' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable'],
        ]);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('airplanes', 'public');
        } elseif (! empty($data['photo']) && ! is_string($data['photo'])) {
            return $this->errorResponse('Format photo tidak valid.', 422);
        }

        $airplane->update($data);
        $airplane->load('airline');

        return $this->successResponse([
            'id' => $airplane->id,
            'airline_id' => $airplane->airline_id,
            'airline_name' => $airplane->airline?->name,
            'model' => $airplane->model,
            'registration_number' => $airplane->registration_number,
            'capacity' => $airplane->capacity,
            'description' => $airplane->description,
            'photo' => $airplane->photo,
        ], 'Airplane berhasil diperbarui');
    }

    #[OA\Delete(
        path: '/api/v1/admin/airplanes/{id}',
        tags: ['Airplanes'],
        summary: 'Hapus airplane (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function destroy(Airplane $airplane)
    {
        $airplane->delete();

        return $this->successResponse(null, 'Airplane berhasil dihapus');
    }

    public function generateSeats(Request $request, Airplane $airplane)
    {
        $data = $request->validate([
            'class' => ['nullable', 'in:economy,business,first'],
            'reset' => ['nullable', 'boolean'],
        ]);

        $seats = $this->seatGeneratorService->generateForAirplane(
            $airplane,
            $data['class'] ?? 'economy',
            (bool) ($data['reset'] ?? false),
        );

        return $this->successResponse($seats, 'Seat berhasil digenerate');
    }
}
