@extends('layouts.admin')

@section('title', 'Staff Dashboard | Cakrawala')
@section('page-title', 'Dashboard Operasional')

@section('content')
    <section class="space-y-6">
        <article class="admin-hero-card admin-hero-card-ops">
            <div class="grid gap-6 xl:grid-cols-[1.35fr_minmax(0,1fr)]">
                <div class="space-y-5">
                    <div class="admin-section-head">
                        <div class="max-w-2xl">
                            <p class="admin-section-kicker">Meja operasional</p>
                            <h2 class="admin-section-title">Ruang kerja staff dibuat seperti antrean tindakan, bukan ringkasan eksekutif.</h2>
                            <p class="admin-section-copy">Arah visual dashboard staff lebih padat dan langsung ke tugas: pembayaran pending, departure dekat, dan pesan support yang perlu diproses tanpa banyak distraksi.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.payments.index') }}" class="admin-btn-primary">Buka Pembayaran</a>
                            <a href="{{ route('admin.contact-messages.index') }}" class="admin-btn-secondary">Buka Inbox</a>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Pembayaran tertunda</p>
                            <p class="admin-metric-value text-amber-600">{{ $stats['payment_pending'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Transaksi yang menunggu verifikasi manual.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Booking aktif</p>
                            <p class="admin-metric-value">{{ $stats['booking_pending'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Booking pending yang masih aktif di sistem.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Inbox bantuan</p>
                            <p class="admin-metric-value text-[#c2410c]">{{ $stats['open_contact_messages'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Kasus bantuan yang perlu ditangani.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Penerbangan aktif</p>
                            <p class="admin-metric-value">{{ $stats['total_flights'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Jadwal yang sedang dikelola jaringan.</p>
                        </article>
                    </div>
                </div>

                <div class="space-y-4">
                    <article class="admin-shift-board">
                        <p class="admin-section-kicker">Shift board</p>
                        <div class="mt-4 space-y-3">
                            <div class="admin-shift-item">
                                <span>Pembayaran pending</span>
                                <strong>{{ $stats['payment_pending'] }}</strong>
                            </div>
                            <div class="admin-shift-item">
                                <span>Inbox terbuka</span>
                                <strong>{{ $stats['open_contact_messages'] }}</strong>
                            </div>
                            <div class="admin-shift-item">
                                <span>Booking aktif</span>
                                <strong>{{ $stats['booking_pending'] }}</strong>
                            </div>
                        </div>
                    </article>

                    <article class="admin-surface-muted">
                        <p class="admin-section-kicker">Meja penerbangan</p>
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
                        <p class="admin-section-kicker">Departure watch</p>
                        <h2 class="admin-section-title">Keberangkatan terdekat</h2>
                    </div>
                    <a href="{{ route('admin.flights.index') }}" class="admin-btn-secondary">Lihat Penerbangan</a>
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
                        <p class="admin-section-kicker">Payment queue</p>
                        <h2 class="admin-section-title">Pembayaran tertunda</h2>
                    </div>
                    <a href="{{ route('admin.payments.index') }}" class="admin-btn-secondary">Buka Pembayaran</a>
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
                                <tr><td colspan="4" class="text-center text-slate-500">Tidak ada pembayaran tertunda.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <article class="admin-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Antrean bantuan</p>
                    <h2 class="admin-section-title">Pesan bantuan aktif</h2>
                </div>
                <a href="{{ route('admin.contact-messages.index') }}" class="admin-btn-secondary">Buka Inbox</a>
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
                                <td>{{ $message->assignedUser?->name ?: 'Belum ditugaskan' }}</td>
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
