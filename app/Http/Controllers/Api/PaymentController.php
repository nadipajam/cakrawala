<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\VerifyPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {
    }

    #[OA\Post(
        path: '/api/v1/payments',
        tags: ['Payments'],
        summary: 'Buat payment',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Berhasil')]
    )]
    public function store(StorePaymentRequest $request)
    {
        $booking = Booking::query()->with('payments')->findOrFail($request->integer('booking_id'));

        if ($booking->user_id !== $request->user()->id && ! $request->user()->isBackoffice()) {
            throw new AuthorizationException('Unauthorized');
        }

        $payment = $this->paymentService->createPayment($booking, $request->validated());

        return $this->successResponse(new PaymentResource($payment), 'Payment berhasil dibuat', 201);
    }

    #[OA\Get(
        path: '/api/v1/payments/{id}',
        tags: ['Payments'],
        summary: 'Detail payment',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function show(Request $request, Payment $payment)
    {
        $payment->load('booking');

        if ($payment->booking->user_id !== $request->user()->id && ! $request->user()->isBackoffice()) {
            throw new AuthorizationException('Unauthorized');
        }

        return $this->successResponse(new PaymentResource($payment), 'Data berhasil diambil');
    }

    public function adminIndex(Request $request)
    {
        $status = trim((string) $request->string('status'));
        $search = trim((string) $request->string('search'));

        $payments = Payment::query()
            ->with(['booking.user', 'booking.flight.airline'])
            ->when(in_array($status, ['pending', 'paid', 'failed', 'refunded'], true), fn ($query) => $query->where('payment_status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($qq) use ($search) {
                    $qq->where('transaction_code', 'like', "%{$search}%")
                        ->orWhere('payment_method', 'like', "%{$search}%")
                        ->orWhereHas('booking', fn ($q) => $q->where('booking_code', 'like', "%{$search}%"))
                        ->orWhereHas('booking.user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate($request->integer('per_page', 15));

        return $this->successResponse($payments, 'Data payment berhasil diambil');
    }

    #[OA\Post(
        path: '/api/v1/admin/payments/{id}/verify',
        tags: ['Payments'],
        summary: 'Verifikasi payment admin',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function verify(VerifyPaymentRequest $request, Payment $payment)
    {
        $payload = $request->validated();
        if (! isset($payload['payment_status'])) {
            $payload['payment_status'] = 'paid';
        }

        $payment = $this->paymentService->verifyPayment($payment, $payload);

        return $this->successResponse(new PaymentResource($payment), 'Payment berhasil diverifikasi');
    }

    public function reject(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'transaction_code' => ['nullable', 'string', 'max:100'],
        ]);

        $payment = $this->paymentService->verifyPayment($payment, [
            'payment_status' => 'failed',
            'transaction_code' => $data['transaction_code'] ?? null,
        ]);

        return $this->successResponse(new PaymentResource($payment), 'Payment berhasil ditolak');
    }
}
