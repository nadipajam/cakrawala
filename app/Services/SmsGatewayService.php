<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsGatewayService
{
    public function send(string $phone, string $message): bool
    {
        $endpoint = (string) config('services.sms.endpoint');
        $token = (string) config('services.sms.token');

        if ($endpoint === '') {
            Log::info('SMS OTP fallback (no gateway configured)', [
                'phone' => $phone,
                'message' => $message,
            ]);

            return true;
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->post($endpoint, [
                'phone' => $phone,
                'message' => $message,
                'sender' => config('services.sms.sender'),
            ]);

        if (! $response->successful()) {
            Log::warning('SMS gateway request failed', [
                'phone' => $phone,
                'status' => $response->status(),
                'body' => $response->json() ?? $response->body(),
            ]);
        }

        return $response->successful();
    }
}

