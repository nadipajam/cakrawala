<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    public function __construct(
        protected BookingService $bookingService
    ) {
    }

    #[OA\Get(
        path: '/api/v1/my-bookings',
        tags: ['Bookings'],
        summary: 'Riwayat booking user login',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function myBookings(Request $request)
    {
        $bookings = $request->user()->bookings()
            ->with([
                'flight.airline',
                'flight.departureAirport',
                'flight.arrivalAirport',
                'details.passenger',
                'details.seat',
                'details.ticket',
                'payments',
            ])
            ->latest()
            ->get();

        return $this->successResponse(BookingResource::collection($bookings), 'Data berhasil diambil');
    }

    #[OA\Post(
        path: '/api/v1/bookings',
        tags: ['Bookings'],
        summary: 'Buat booking baru',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Booking berhasil dibuat')]
    )]
    public function store(StoreBookingRequest $request)
    {
        $booking = $this->bookingService->createBooking($request->user(), $request->validated());

        return $this->successResponse(new BookingResource($booking), 'Booking berhasil dibuat', 201);
    }

    #[OA\Get(
        path: '/api/v1/bookings/{id}',
        tags: ['Bookings'],
        summary: 'Detail booking',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function show(Request $request, Booking $booking)
    {
        if ($booking->user_id !== $request->user()->id && ! $request->user()->isBackoffice()) {
            throw new AuthorizationException('Unauthorized');
        }

        $booking->load([
            'flight.airline',
            'flight.departureAirport',
            'flight.arrivalAirport',
            'details.passenger',
            'details.seat',
            'details.ticket',
            'payments.booking',
        ]);

        return $this->successResponse(new BookingResource($booking), 'Data berhasil diambil');
    }

    #[OA\Post(
        path: '/api/v1/bookings/{id}/cancel',
        tags: ['Bookings'],
        summary: 'Batalkan booking',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function cancel(Request $request, Booking $booking)
    {
        $booking = $this->bookingService->cancelBooking($request->user(), $booking);

        return $this->successResponse(new BookingResource($booking), 'Booking berhasil dibatalkan');
    }

    #[OA\Get(
        path: '/api/v1/admin/bookings',
        tags: ['Bookings'],
        summary: 'Daftar booking admin',
        security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Berhasil')]
    )]
    public function adminIndex()
    {
        $bookings = Booking::query()
            ->with([
                'user',
                'flight.airline',
                'details.passenger',
                'details.seat',
                'payments',
            ])
            ->latest()
            ->get();

        return $this->successResponse(BookingResource::collection($bookings), 'Data berhasil diambil');
    }
}
