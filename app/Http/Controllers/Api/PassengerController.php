<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Passenger\StorePassengerRequest;
use App\Http\Requests\Passenger\UpdatePassengerRequest;
use App\Http\Resources\PassengerResource;
use App\Models\Passenger;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PassengerController extends Controller
{
    #[OA\Get(
        path: '/api/v1/passengers',
        tags: ['Passengers'],
        summary: 'Daftar passenger user login',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function index(Request $request)
    {
        $passengers = $request->user()->passengers()->latest()->get();

        return $this->successResponse(PassengerResource::collection($passengers), 'Data berhasil diambil');
    }

    #[OA\Post(
        path: '/api/v1/passengers',
        tags: ['Passengers'],
        summary: 'Tambah passenger',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Berhasil')]
    )]
    public function store(StorePassengerRequest $request)
    {
        $passenger = $request->user()->passengers()->create($request->validated());

        return $this->successResponse(new PassengerResource($passenger), 'Passenger berhasil dibuat', 201);
    }

    #[OA\Get(
        path: '/api/v1/passengers/{id}',
        tags: ['Passengers'],
        summary: 'Detail passenger',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function show(Request $request, Passenger $passenger)
    {
        if ($passenger->user_id !== $request->user()->id) {
            throw new AuthorizationException('Unauthorized');
        }

        return $this->successResponse(new PassengerResource($passenger), 'Data berhasil diambil');
    }

    #[OA\Put(
        path: '/api/v1/passengers/{id}',
        tags: ['Passengers'],
        summary: 'Update passenger',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function update(UpdatePassengerRequest $request, Passenger $passenger)
    {
        if ($passenger->user_id !== $request->user()->id) {
            throw new AuthorizationException('Unauthorized');
        }

        $passenger->update($request->validated());

        return $this->successResponse(new PassengerResource($passenger), 'Passenger berhasil diperbarui');
    }

    #[OA\Delete(
        path: '/api/v1/passengers/{id}',
        tags: ['Passengers'],
        summary: 'Hapus passenger',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function destroy(Request $request, Passenger $passenger)
    {
        if ($passenger->user_id !== $request->user()->id) {
            throw new AuthorizationException('Unauthorized');
        }

        $passenger->delete();

        return $this->successResponse(null, 'Passenger berhasil dihapus');
    }
}
