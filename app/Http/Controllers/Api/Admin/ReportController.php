<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected AdminAnalyticsService $analyticsService
    ) {
    }

    public function bookings(Request $request)
    {
        $summary = $this->analyticsService->reportSummary($this->extractFilters($request));

        return $this->successResponse([
            'total_bookings' => $summary['total_bookings'],
            'total_cancelled_bookings' => $summary['total_cancelled_bookings'],
        ], 'Laporan bookings berhasil diambil');
    }

    public function payments(Request $request)
    {
        $summary = $this->analyticsService->reportSummary($this->extractFilters($request));

        return $this->successResponse([
            'total_paid_payments' => $summary['total_paid_payments'],
            'revenue_total' => $summary['revenue_total'],
        ], 'Laporan payments berhasil diambil');
    }

    public function revenue(Request $request)
    {
        $summary = $this->analyticsService->reportSummary($this->extractFilters($request));

        return $this->successResponse([
            'revenue_total' => $summary['revenue_total'],
            'monthly_revenue' => $summary['monthly_revenue'],
        ], 'Laporan revenue berhasil diambil');
    }

    public function popularRoutes(Request $request)
    {
        $summary = $this->analyticsService->reportSummary($this->extractFilters($request));

        return $this->successResponse($summary['popular_routes'], 'Laporan popular routes berhasil diambil');
    }

    protected function extractFilters(Request $request): array
    {
        return [
            'date_from' => trim((string) $request->string('date_from')),
            'date_to' => trim((string) $request->string('date_to')),
            'airline_id' => $request->integer('airline_id'),
            'route' => trim((string) $request->string('route')),
            'payment_status' => trim((string) $request->string('payment_status')),
            'booking_status' => trim((string) $request->string('booking_status')),
        ];
    }
}
