<?php

namespace App\Services;

use App\Models\Airline;
use App\Models\Airplane;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\BookingAddon;
use App\Models\BookingChangeRequest;
use App\Models\ContactMessage;
use App\Models\Flight;
use App\Models\Passenger;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsService
{
    public function summary(): array
    {
        $now = now();

        return [
            'total_users' => User::query()->whereIn('role', UserRole::customerValues())->count(),
            'total_staff' => User::query()->where('role', UserRole::STAFF)->count(),
            'total_managers' => User::query()->where('role', UserRole::MANAGER)->count(),
            'total_admins' => User::query()->where('role', UserRole::ADMIN)->count(),
            'total_passengers' => Passenger::query()->count(),
            'total_airports' => Airport::query()->count(),
            'total_airlines' => Airline::query()->count(),
            'total_airplanes' => Airplane::query()->count(),
            'total_flights' => Flight::query()->count(),
            'total_bookings' => Booking::query()->count(),
            'total_payments' => Payment::query()->count(),
            'total_tickets' => Ticket::query()->count(),
            'booking_pending' => Booking::query()->where('status', 'pending')->count(),
            'booking_confirmed' => Booking::query()->where('status', 'confirmed')->count(),
            'booking_cancelled' => Booking::query()->where('status', 'cancelled')->count(),
            'payment_pending' => Payment::query()->where('payment_status', 'pending')->count(),
            'revenue_total' => (float) Payment::query()->where('payment_status', 'paid')->sum('amount'),
            'revenue_this_month' => (float) Payment::query()
                ->where('payment_status', 'paid')
                ->whereYear('paid_at', $now->year)
                ->whereMonth('paid_at', $now->month)
                ->sum('amount'),
            'open_contact_messages' => ContactMessage::query()->whereIn('status', ['open', 'in_progress'])->count(),
            'delayed_flights' => Flight::query()->where('status', 'delayed')->count(),
            'booking_expiring_today' => Booking::query()
                ->where('status', 'pending')
                ->whereDate('expired_at', $now->toDateString())
                ->count(),
            'pending_checkins' => Booking::query()
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereHas('details', fn (Builder $detail) => $detail->where('boarding_status', 'not_checked_in'))
                ->count(),
            'active_addons' => BookingAddon::query()->where('status', 'selected')->count(),
            'open_change_requests' => BookingChangeRequest::query()->whereIn('status', ['submitted', 'in_review', 'approved'])->count(),
        ];
    }

    /**
     * @return Collection<int, array{month:string,total:int}>
     */
    public function monthlyBookingChart(int $months = 6): Collection
    {
        $start = now()->startOfMonth()->subMonths($months - 1);

        $data = Booking::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn (Booking $booking) => optional($booking->created_at)->format('Y-m'))
            ->map(fn (Collection $items) => $items->count());

        return collect(range(0, $months - 1))->map(function (int $offset) use ($start, $data) {
            $month = $start->copy()->addMonths($offset);
            $key = $month->format('Y-m');

            return [
                'month' => $month->format('M Y'),
                'total' => (int) ($data[$key] ?? 0),
            ];
        });
    }

    public function paymentStatusBreakdown(): array
    {
        return [
            'paid' => Payment::query()->where('payment_status', 'paid')->count(),
            'pending' => Payment::query()->where('payment_status', 'pending')->count(),
        ];
    }

    public function reportSummary(array $filters = []): array
    {
        $bookingQuery = $this->filteredReportBookingQuery($filters)
            ->with(['flight.departureAirport', 'flight.arrivalAirport', 'flight.airline']);
        $paymentQuery = $this->filteredReportPaymentQuery($filters)
            ->with(['booking.flight.departureAirport', 'booking.flight.arrivalAirport']);
        $flightQuery = $this->filteredReportFlightQuery($filters);

        $paidPaymentQuery = (clone $paymentQuery)->where('payment_status', 'paid');

        $popularRoutes = (clone $bookingQuery)
            ->select('flight_id', DB::raw('COUNT(*) as total'))
            ->groupBy('flight_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(function (Booking $booking) {
                $flight = $booking->flight;

                return [
                    'flight_id' => $flight?->id,
                    'route' => $flight
                        ? sprintf('%s -> %s', $flight->departureAirport?->code, $flight->arrivalAirport?->code)
                        : '-',
                    'flight_number' => $flight?->flight_number,
                    'total_bookings' => (int) $booking->total,
                ];
            })
            ->values();

        $monthlyRevenue = (clone $paidPaymentQuery)
            ->whereNotNull('paid_at')
            ->get(['paid_at', 'amount'])
            ->groupBy(fn (Payment $payment) => optional($payment->paid_at)->format('Y-m'))
            ->map(fn (Collection $items, string $month) => [
                'month' => $month,
                'total' => (float) $items->sum('amount'),
            ])
            ->sortBy('month')
            ->values();

        return [
            'total_bookings' => $bookingQuery->count(),
            'total_paid_payments' => $paidPaymentQuery->count(),
            'total_cancelled_bookings' => (clone $bookingQuery)->where('status', 'cancelled')->count(),
            'total_active_flights' => $flightQuery->whereIn('status', ['scheduled', 'delayed'])->count(),
            'revenue_total' => (float) $paidPaymentQuery->sum('amount'),
            'popular_routes' => $popularRoutes,
            'monthly_revenue' => $monthlyRevenue,
        ];
    }

    public function reportExportData(array $filters = []): array
    {
        $bookingQuery = $this->filteredReportBookingQuery($filters)
            ->with([
                'user',
                'flight.airline',
                'flight.departureAirport',
                'flight.arrivalAirport',
                'payments' => fn ($query) => $query->latest(),
            ]);
        $paymentQuery = $this->filteredReportPaymentQuery($filters);

        $bookingStatusBreakdown = (clone $bookingQuery)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($total) => (int) $total)
            ->all();

        $paymentStatusBreakdown = (clone $paymentQuery)
            ->select('payment_status', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_status')
            ->pluck('total', 'payment_status')
            ->map(fn ($total) => (int) $total)
            ->all();

        $paymentMethodBreakdown = (clone $paymentQuery)
            ->select('payment_method', DB::raw('COUNT(*) as total_transactions'), DB::raw('SUM(amount) as total_amount'))
            ->groupBy('payment_method')
            ->orderByDesc('total_transactions')
            ->get()
            ->map(fn (Payment $payment) => [
                'payment_method' => (string) $payment->payment_method,
                'total_transactions' => (int) $payment->total_transactions,
                'total_amount' => (float) $payment->total_amount,
            ])
            ->values()
            ->all();

        $bookingRows = (clone $bookingQuery)
            ->latest()
            ->get()
            ->map(function (Booking $booking) {
                $latestPayment = $booking->payments->sortByDesc('created_at')->first();
                $flight = $booking->flight;

                return [
                    'booking_code' => (string) $booking->booking_code,
                    'booked_at' => optional($booking->created_at)->format('Y-m-d H:i:s'),
                    'user_name' => (string) ($booking->user?->name ?? '-'),
                    'user_email' => (string) ($booking->user?->email ?? '-'),
                    'airline' => (string) ($flight?->airline?->name ?? '-'),
                    'flight_number' => (string) ($flight?->flight_number ?? '-'),
                    'route' => $flight
                        ? sprintf('%s -> %s', $flight->departureAirport?->code, $flight->arrivalAirport?->code)
                        : '-',
                    'passengers' => (int) $booking->total_passengers,
                    'booking_status' => (string) $booking->status,
                    'payment_status' => (string) ($latestPayment?->payment_status ?? '-'),
                    'payment_method' => (string) ($latestPayment?->payment_method ?? '-'),
                    'paid_at' => optional($latestPayment?->paid_at)->format('Y-m-d H:i:s'),
                    'total_price' => (float) $booking->total_price,
                    'expired_at' => optional($booking->expired_at)->format('Y-m-d H:i:s'),
                ];
            })
            ->values()
            ->all();

        return [
            'booking_status_breakdown' => $bookingStatusBreakdown,
            'payment_status_breakdown' => $paymentStatusBreakdown,
            'payment_method_breakdown' => $paymentMethodBreakdown,
            'booking_rows' => $bookingRows,
        ];
    }

    protected function filteredReportBookingQuery(array $filters = []): Builder
    {
        $query = Booking::query();

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['airline_id'])) {
            $query->whereHas('flight', fn (Builder $flight) => $flight->where('airline_id', $filters['airline_id']));
        }

        if (! empty($filters['booking_status'])) {
            $query->where('status', $filters['booking_status']);
        }

        if (! empty($filters['route'])) {
            $routeSegments = $this->routeSegments((string) $filters['route']);

            $query->whereHas('flight', function (Builder $flight) use ($routeSegments) {
                $this->applyAirportRouteConstraint($flight, $routeSegments);
            });
        }

        return $query;
    }

    protected function filteredReportPaymentQuery(array $filters = []): Builder
    {
        $query = Payment::query();

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['airline_id'])) {
            $query->whereHas('booking.flight', fn (Builder $flight) => $flight->where('airline_id', $filters['airline_id']));
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (! empty($filters['booking_status'])) {
            $query->whereHas('booking', fn (Builder $booking) => $booking->where('status', $filters['booking_status']));
        }

        if (! empty($filters['route'])) {
            $routeSegments = $this->routeSegments((string) $filters['route']);

            $query->whereHas('booking.flight', function (Builder $flight) use ($routeSegments) {
                $this->applyAirportRouteConstraint($flight, $routeSegments);
            });
        }

        return $query;
    }

    protected function filteredReportFlightQuery(array $filters = []): Builder
    {
        $query = Flight::query();

        if (! empty($filters['airline_id'])) {
            $query->where('airline_id', $filters['airline_id']);
        }

        if (! empty($filters['route'])) {
            $routeSegments = $this->routeSegments((string) $filters['route']);

            $this->applyAirportRouteConstraint($query, $routeSegments);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('departure_time', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('departure_time', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * @return array<int, string>
     */
    protected function routeSegments(string $route): array
    {
        $normalized = strtoupper(trim($route));
        if ($normalized === '') {
            return [];
        }

        $parts = preg_split('/\s*(?:\/|->|>|-)\s*/', $normalized) ?: [];
        $segments = array_values(array_filter(array_map(static fn (string $part) => trim($part), $parts)));

        return $segments === [] ? [$normalized] : array_slice($segments, 0, 2);
    }

    /**
     * @param  array<int, string>  $segments
     */
    protected function applyAirportRouteConstraint(Builder $query, array $segments): void
    {
        if (count($segments) >= 2) {
            $query
                ->whereHas('departureAirport', fn (Builder $airport) => $airport->where('code', 'like', '%'.$segments[0].'%'))
                ->whereHas('arrivalAirport', fn (Builder $airport) => $airport->where('code', 'like', '%'.$segments[1].'%'));

            return;
        }

        $segment = $segments[0] ?? '';
        if ($segment === '') {
            return;
        }

        $query->where(function (Builder $routeQuery) use ($segment) {
            $routeQuery
                ->whereHas('departureAirport', fn (Builder $airport) => $airport->where('code', 'like', '%'.$segment.'%'))
                ->orWhereHas('arrivalAirport', fn (Builder $airport) => $airport->where('code', 'like', '%'.$segment.'%'));
        });
    }
}
