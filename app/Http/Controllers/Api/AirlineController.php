<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AirlineResource;
use App\Models\Airline;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AirlineController extends Controller
{
    #[OA\Get(
        path: '/api/v1/airlines',
        tags: ['Airlines'],
        summary: 'Daftar airline',
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function index()
    {
        $airlines = Airline::query()->orderBy('name')->get();

        return $this->successResponse(AirlineResource::collection($airlines), 'Data berhasil diambil');
    }

    #[OA\Post(
        path: '/api/v1/admin/airlines',
        tags: ['Airlines'],
        summary: 'Tambah airline (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Berhasil')]
    )]
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:airlines,code'],
            'logo' => ['nullable'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('airlines', 'public');
        } elseif (! empty($data['logo']) && ! is_string($data['logo'])) {
            return $this->errorResponse('Format logo tidak valid.', 422);
        }

        $airline = Airline::create($data);

        return $this->successResponse(new AirlineResource($airline), 'Airline berhasil dibuat', 201);
    }

    #[OA\Get(
        path: '/api/v1/admin/airlines/{id}',
        tags: ['Airlines'],
        summary: 'Detail airline',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function show(Airline $airline)
    {
        return $this->successResponse(new AirlineResource($airline), 'Data berhasil diambil');
    }

    #[OA\Put(
        path: '/api/v1/admin/airlines/{id}',
        tags: ['Airlines'],
        summary: 'Update airline (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function update(Request $request, Airline $airline)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', 'unique:airlines,code,'.$airline->id],
            'logo' => ['nullable'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('airlines', 'public');
        } elseif (! empty($data['logo']) && ! is_string($data['logo'])) {
            return $this->errorResponse('Format logo tidak valid.', 422);
        }

        $airline->update($data);

        return $this->successResponse(new AirlineResource($airline), 'Airline berhasil diperbarui');
    }

    #[OA\Delete(
        path: '/api/v1/admin/airlines/{id}',
        tags: ['Airlines'],
        summary: 'Hapus airline (admin)',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function destroy(Airline $airline)
    {
        $airline->delete();

        return $this->successResponse(null, 'Airline berhasil dihapus');
    }
}
