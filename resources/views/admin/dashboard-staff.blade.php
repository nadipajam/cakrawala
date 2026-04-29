@extends('layouts.admin')

@section('title', 'Staff Dashboard | Cakrawala')
@section('page-title', 'Operations Dashboard')

@section('content')
    <section class="space-y-6">
        <article class="admin-hero-card">
            <div class="grid gap-6 xl:grid-cols-[1.35fr_1fr]">
                <div class="space-y-5">
                    <div class="admin-section-head">
                        <div class="max-w-2xl">
                            <p class="admin-section-kicker">Operations Desk</p>
                            <h2 class="admin-section-title">Panel kerja staff untuk antrean harian</h2>
                            <p class="admin-section-copy">Fokus halaman ini ada pada keberangkatan terdekat, verifikasi pembayaran, dan pesan support yang perlu ditindaklanjuti cepat.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.payments.index') }}" class="admin-btn-primary">Open Payments</a>
                            <a href="{{ route('admin.contact-messages.index') }}" class="admin-btn-secondary">Open Inbox</a>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Pending payments</p>
                            <p class="admin-metric-value text-amber-600">{{ $stats['payment_pending'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Transaksi yang menunggu verifikasi manual.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Open bookings</p>
                            <p class="admin-metric-value">{{ $stats['booking_pending'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Booking pending yang masih aktif di sistem.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Support inbox</p>
                            <p class="admin-metric-value text-[#0f3f78]">{{ $stats['open_contact_messages'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Kasus bantuan yang perlu ditangani.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Active flights</p>
                            <p class="admin-metric-value">{{ $stats['total_flights'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Jadwal yang sedang dikelola jaringan.</p>
                        </article>
                    </div>
                </div>

                <div class="space-y-4">
                    <article class="admin-list-card">
                        <div class="admin-section-head">
                            <div>
                                <p class="admin-section-kicker">Action Focus</p>
                                <h3 class="admin-section-title text-lg">Prioritas shift ini</h3>
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
                            <div class="admin-list-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Pembayaran pending</p>
                                    <p class="text-sm text-slate-500">Selesaikan verifikasi untuk mempercepat ticketing.</p>
                                </div>
                                <p class="text-lg font-bold text-amber-600">{{ $stats['payment_pending'] }}</p>
                            </div>
                            <div class="admin-list-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Inbox terbuka</p>
                                    <p class="text-sm text-slate-500">Pastikan assignment PIC tetap seimbang.</p>
                                </div>
                                <p class="text-lg font-bold text-[#0f3f78]">{{ $stats['open_contact_messages'] }}</p>
                            </div>
                            <div class="admin-list-row">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Open bookings</p>
                                    <p class="text-sm text-slate-500">Booking pending perlu dipantau sebelum expired.</p>
                                </div>
                                <p class="text-lg font-bold text-slate-800">{{ $stats['booking_pending'] }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="admin-surface-muted">
                        <p class="admin-section-kicker">Flight Desk</p>
                        <h3 class="mt-2 font-heading text-lg font-bold text-slate-800">Kesiapan operasional</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan daftar departure di bawah untuk melihat jalur yang harus diprioritaskan saat ada perubahan jadwal, check-in, atau permintaan pelanggan.</p>
                    </article>
                </div>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-2">
            <article class="admin-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Departure Watch</p>
                        <h2 class="admin-section-title">Upcoming departures</h2>
                    </div>
                    <a href="{{ route('admin.flights.index') }}" class="admin-btn-secondary">View Flights</a>
                </div>
                <div class="admin-table-wrap mt-4">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Flight</th>
                                <th>Route</th>
                                <th>Departure</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($upcomingFlights as $flight)
                                <tr>
                                    <td>{{ $flight->flight_number }} | {{ $flight->airline?->name }}</td>
                                    <td>{{ $flight->departureAirport?->code }} - {{ $flight->arrivalAirport?->code }}</td>
                                    <td>{{ $flight->departure_time?->format('d M Y H:i') }}</td>
                                    <td>@include('admin.partials.status-badge', ['status' => $flight->status])</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-slate-500">Tidak ada departure dalam 24 jam ke depan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="admin-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Payment Queue</p>
                        <h2 class="admin-section-title">Pending payments</h2>
                    </div>
                    <a href="{{ route('admin.payments.index') }}" class="admin-btn-secondary">Open Payments</a>
                </div>
                <div class="admin-table-wrap mt-4">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>Customer</th>
                                <th>Airline</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($pendingPayments as $payment)
                                <tr>
                                    <td>{{ $payment->booking?->booking_code }}</td>
                                    <td>{{ $payment->booking?->user?->name }}</td>
                                    <td>{{ $payment->booking?->flight?->airline?->name }}</td>
                                    <td>Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-slate-500">Tidak ada payment pending.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <article class="admin-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Support Queue</p>
                    <h2 class="admin-section-title">Open support messages</h2>
                </div>
                <a href="{{ route('admin.contact-messages.index') }}" class="admin-btn-secondary">Open Inbox</a>
            </div>
            <div class="admin-table-wrap mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>PIC</th>
                            <th>Received</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($openMessages as $message)
                            <tr>
                                <td>{{ $message->name }}</td>
                                <td>{{ $message->subject }}</td>
                                <td>@include('admin.partials.status-badge', ['status' => $message->status])</td>
                                <td>{{ $message->assignedUser?->name ?: 'Unassigned' }}</td>
                                <td>{{ $message->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-slate-500">Tidak ada pesan bantuan terbuka.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
