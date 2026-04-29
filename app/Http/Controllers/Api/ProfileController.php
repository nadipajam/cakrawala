<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    #[OA\Get(
        path: '/api/v1/profile',
        tags: ['Profile'],
        summary: 'Detail profile login',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function show(Request $request)
    {
        return $this->successResponse([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'phone' => $request->user()->phone,
            'role' => $request->user()->role,
        ], 'Data berhasil diambil');
    }

    #[OA\Put(
        path: '/api/v1/profile',
        tags: ['Profile'],
        summary: 'Update profile login',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function update(UpdateProfileRequest $request)
    {
        $request->user()->update($request->validated());

        return $this->successResponse([
            'id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'phone' => $request->user()->phone,
            'role' => $request->user()->role,
        ], 'Profile berhasil diperbarui');
    }
}
