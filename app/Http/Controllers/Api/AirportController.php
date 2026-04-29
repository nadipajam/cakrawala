<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AirportResource;
use App\Models\Airport;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AirportController extends Controller
{
    #[OA\Get(
        path: '/api/v1/airports',
        tags: ['Airports'],
        summary: 'Daftar airport',
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function index()
    {
        $airports = Airport::query()->orderBy('code')->get();

        return $this->successResponse(AirportResource::collection($airports), 'Data berhasil diambil');
    }

    #[OA\Post(
        path: '/api/v1/admin/airports',
        tags: ['Airports'],
        summary: 'Tambah airport (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Berhasil')]
    )]
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:airports,code'],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
        ]);

        $airport = Airport::create($data);

        return $this->successResponse(new AirportResource($airport), 'Airport berhasil dibuat', 201);
    }

    #[OA\Get(
        path: '/api/v1/admin/airports/{id}',
        tags: ['Airports'],
        summary: 'Detail airport',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function show(Airport $airport)
    {
        return $this->successResponse(new AirportResource($airport), 'Data berhasil diambil');
    }

    #[OA\Put(
        path: '/api/v1/admin/airports/{id}',
        tags: ['Airports'],
        summary: 'Update airport (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function update(Request $request, Airport $airport)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:airports,code,'.$airport->id],
            'name' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
        ]);

        $airport->update($data);

        return $this->successResponse(new AirportResource($airport), 'Airport berhasil diperbarui');
    }

    #[OA\Delete(
        path: '/api/v1/admin/airports/{id}',
        tags: ['Airports'],
        summary: 'Hapus airport (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function destroy(Airport $airport)
    {
        $activeFlightsExist = $airport->departureFlights()->whereIn('status', ['scheduled', 'delayed'])->exists()
            || $airport->arrivalFlights()->whereIn('status', ['scheduled', 'delayed'])->exists();

        if ($activeFlightsExist) {
            return $this->errorResponse('Airport tidak bisa dihapus karena masih dipakai flight aktif.', 422);
        }

        $airport->delete();

        return $this->successResponse(null, 'Airport berhasil dihapus');
    }
}
