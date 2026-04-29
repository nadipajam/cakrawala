<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Cakrawala Technical Reference',
    version: '1.0.0',
    description: 'Referensi teknis internal sistem Cakrawala'
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Internal Service'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token'
)]
class OpenApiSpec
{
}
