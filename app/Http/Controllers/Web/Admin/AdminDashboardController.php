<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ContactMessage;
use App\Models\Flight;
use App\Models\Payment;
use App\Services\AdminAnalyticsService;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected AdminAnalyticsService $analyticsService
    ) {
    }

    public function index(Request $request)
    {
        $summary = $this->analyticsService->summary();
        $user = $request->user();
        $reportSummary = $this->analyticsService->reportSummary();

        $quickAlerts = [];
        if ($summary['payment_pending'] > 0) {
            $quickAlerts[] = "{$summary['payment_pending']} pembayaran menunggu verifikasi";
        }
        if ($summary['overdue_non_qris_pending_payments'] > 0) {
            $quickAlerts[] = "{$summary['overdue_non_qris_pending_payments']} pembayaran non-QRIS melewati SLA verifikasi 30 menit";
        }
        if ($summary['delayed_flights'] > 0) {
            $quickAlerts[] = "{$summary['delayed_flights']} flight berstatus delayed";
        }
        if ($summary['booking_expiring_today'] > 0) {
            $quickAlerts[] = "{$summary['booking_expiring_today']} booking akan expired hari ini";
        }
        if ($summary['open_contact_messages'] > 0) {
            $quickAlerts[] = "{$summary['open_contact_messages']} pesan bantuan belum ditutup";
        }
        if ($summary['pending_checkins'] > 0) {
            $quickAlerts[] = "{$summary['pending_checkins']} booking memiliki passenger yang belum check-in";
        }
        if ($summary['open_change_requests'] > 0) {
            $quickAlerts[] = "{$summary['open_change_requests']} change request menunggu tindak lanjut";
        }

        $dashboardData = [
            'stats' => $summary,
            'recentBookings' => Booking::query()
                ->with(['user', 'flight.airline', 'flight.departureAirport', 'flight.arrivalAirport'])
                ->latest()
                ->limit(8)
                ->get(),
            'recentPayments' => Payment::query()
                ->with(['booking.user', 'booking.flight'])
                ->latest()
                ->limit(8)
                ->get(),
            'bookingChart' => $this->analyticsService->monthlyBookingChart(6),
            'paymentStatusChart' => $this->analyticsService->paymentStatusBreakdown(),
            'quickAlerts' => $quickAlerts,
            'openMessages' => ContactMessage::query()
                ->with(['assignedUser'])
                ->whereIn('status', ['open', 'in_progress'])
                ->latest()
                ->limit(6)
                ->get(),
        ];

        if ($user->isStaff()) {
            return view('admin.dashboard-staff', $dashboardData + [
                'upcomingFlights' => Flight::query()
                    ->with(['airline', 'departureAirport', 'arrivalAirport'])
                    ->whereIn('status', ['scheduled', 'delayed'])
                    ->whereBetween('departure_time', [now(), now()->addDay()])
                    ->orderBy('departure_time')
                    ->limit(8)
                    ->get(),
                'pendingPayments' => Payment::query()
                    ->with(['booking.user', 'booking.flight.airline'])
                    ->where('payment_status', 'pending')
                    ->latest()
                    ->limit(8)
                    ->get(),
            ]);
        }

        if ($user->isManager()) {
            return view('admin.dashboard-manager', $dashboardData + [
                'roleMix' => [
                    'admin' => $summary['total_admins'],
                    'staff' => $summary['total_staff'],
                    'manager' => $summary['total_managers'],
                    'customer' => $summary['total_users'],
                ],
                'monthlyRevenue' => $reportSummary['monthly_revenue'],
                'popularRoutes' => $reportSummary['popular_routes'],
            ]);
        }

        return view('admin.dashboard', $dashboardData);
    }
}
