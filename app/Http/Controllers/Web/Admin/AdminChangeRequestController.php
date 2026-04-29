<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeRequest\ProcessBookingChangeRequest;
use App\Models\BookingChangeRequest;
use App\Services\BookingChangeRequestService;
use App\Support\BookingChangeRequestCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminChangeRequestController extends Controller
{
    public function __construct(
        protected BookingChangeRequestService $changeRequestService
    ) {
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => trim((string) $request->string('search')),
            'status' => trim((string) $request->string('status')),
            'type' => trim((string) $request->string('type')),
        ];

        $requests = $this->changeRequestService
            ->queryForAdmin($filters)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.change-requests.index', [
            'requests' => $requests,
            'filters' => $filters,
            'requestTypes' => BookingChangeRequestCatalog::types(),
            'statuses' => BookingChangeRequestCatalog::statuses(),
        ]);
    }

    public function show(BookingChangeRequest $changeRequest)
    {
        $changeRequest->load([
            'booking.user',
            'booking.details.passenger',
            'booking.details.seat',
            'booking.flight.airline',
            'booking.flight.departureAirport',
            'booking.flight.arrivalAirport',
            'preferredFlight.airline',
            'preferredFlight.departureAirport',
            'preferredFlight.arrivalAirport',
            'processedByUser',
        ]);

        return view('admin.change-requests.show', [
            'changeRequest' => $changeRequest,
            'requestTypes' => BookingChangeRequestCatalog::types(),
            'statuses' => BookingChangeRequestCatalog::statuses(),
        ]);
    }

    public function update(ProcessBookingChangeRequest $request, BookingChangeRequest $changeRequest): RedirectResponse
    {
        $this->changeRequestService->process($request->user(), $changeRequest, $request->validated());

        return back()->with('status', 'Request booking berhasil diproses.');
    }
}
