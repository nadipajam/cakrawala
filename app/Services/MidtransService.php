<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class MidtransService
{
    public function createSnapTransaction(Booking $booking, int $amount, string $orderId): array
    {
        $baseUrl = $this->isProduction()
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $serverKey = $this->serverKey();

        if ($serverKey === '') {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diisi.');
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => Str::limit($booking->user?->name ?? 'Customer', 50, ''),
                'email' => $booking->user?->email,
                'phone' => $booking->user?->phone,
            ],
        ];

        $finishUrl = $this->finishCallbackUrl();
        if ($finishUrl !== null) {
            $payload['callbacks'] = [
                'finish' => $finishUrl,
                'unfinish' => $finishUrl,
                'error' => $finishUrl,
            ];
            $payload['gopay'] = [
                'enable_callback' => true,
                'callback_url' => $finishUrl,
            ];
        }

        $request = Http::acceptJson()
            ->asJson()
            ->withBasicAuth($serverKey, '')
            ->withOptions([
                'verify' => (bool) config('services.midtrans.verify_ssl', true),
            ]);

        $overrideNotificationUrl = trim((string) config('services.midtrans.notification_url', ''));
        if ($overrideNotificationUrl !== '') {
            $request = $request->withHeaders([
                'X-Override-Notification' => $overrideNotificationUrl,
            ]);
        }

        $response = $request->post($baseUrl.'/snap/v1/transactions', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Gagal membuat transaksi Midtrans: '.$response->body());
        }

        $json = $response->json();

        return [
            'token' => (string) ($json['token'] ?? ''),
            'redirect_url' => (string) ($json['redirect_url'] ?? ''),
            'payload' => $json,
        ];
    }

    public function verifyNotificationSignature(array $payload): bool
    {
        $signature = (string) ($payload['signature_key'] ?? '');
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');

        if ($signature === '' || $orderId === '' || $statusCode === '' || $grossAmount === '') {
            return false;
        }

        $expected = hash('sha512', $orderId.$statusCode.$grossAmount.$this->serverKey());

        return hash_equals($expected, $signature);
    }

    public function getTransactionStatus(string $orderId): array
    {
        $orderId = trim($orderId);
        if ($orderId === '') {
            throw new RuntimeException('Order ID Midtrans kosong.');
        }

        $baseUrl = $this->isProduction()
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        $response = Http::acceptJson()
            ->withBasicAuth($this->serverKey(), '')
            ->withOptions([
                'verify' => (bool) config('services.midtrans.verify_ssl', true),
            ])
            ->get($baseUrl.'/v2/'.$orderId.'/status');

        $json = (array) $response->json();

        if (! $response->successful() && ! isset($json['status_code'])) {
            throw new RuntimeException('Gagal cek status Midtrans: '.$response->body());
        }

        return $json;
    }

    public function mapMidtransStatusToPaymentStatus(array $payload): string
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        if ($transactionStatus === 'settlement') {
            return 'paid';
        }

        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'challenge' ? 'pending' : 'paid';
        }

        if ($transactionStatus === 'authorize') {
            return 'paid';
        }

        if (in_array($transactionStatus, ['pending'], true)) {
            return 'pending';
        }

        if (in_array($transactionStatus, ['cancel', 'deny', 'expire', 'failure'], true)) {
            return 'failed';
        }

        if (in_array($transactionStatus, ['refund', 'partial_refund', 'chargeback', 'partial_chargeback'], true)) {
            return 'refunded';
        }

        return 'pending';
    }

    public function generateOrderId(int $bookingId, int $paymentId): string
    {
        return 'MID-'.$bookingId.'-'.$paymentId.'-'.now()->format('YmdHis');
    }

    protected function serverKey(): string
    {
        $key = (string) config('services.midtrans.server_key');

        if ($key === '' || str_contains($key, 'REPLACE_WITH_YOUR_SANDBOX_SERVER_KEY')) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY masih placeholder. Isi dengan Server Key asli dari Midtrans MAP.');
        }

        return $key;
    }

    protected function isProduction(): bool
    {
        return (bool) config('services.midtrans.is_production', false);
    }

    protected function finishCallbackUrl(): ?string
    {
        $finishPath = route('payments.midtrans.finish', absolute: false);

        if (app()->bound('request')) {
            $request = request();
            if ($request) {
                return rtrim($request->getSchemeAndHttpHost(), '/').$finishPath;
            }
        }

        return route('payments.midtrans.finish');
    }
}
