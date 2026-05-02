@extends('layouts.admin')

@section('title', 'Admin Dashboard | Cakrawala')
@section('page-title', 'Dashboard')

@section('content')
    <section class="space-y-6">
        <article class="admin-hero-card admin-hero-card-command">
            <div class="grid gap-6 xl:grid-cols-[1.35fr_minmax(0,1fr)]">
                <div class="space-y-5">
                    <div class="admin-section-head">
                        <div class="max-w-2xl">
                            <p class="admin-section-kicker">Pusat kendali</p>
                            <h2 class="admin-section-title">Operasi harian disusun seperti ruang kendali, bukan halaman ringkasan biasa.</h2>
                            <p class="admin-section-copy">Fokus utama dashboard admin penuh adalah membaca kesehatan sistem, distribusi traffic, dan area yang perlu intervensi cepat dari banyak modul sekaligus.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.bookings.index') }}" class="admin-btn-primary">Buka Booking</a>
                            <a href="{{ route('admin.reports.index') }}" class="admin-btn-secondary">Buka Laporan</a>
                        </div>
                    </div>

                    <div class="admin-data-grid">
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Pelanggan</p>
                            <p class="admin-metric-value">{{ $stats['total_users'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Akun customer aktif yang sudah terdaftar.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Penumpang</p>
                            <p class="admin-metric-value">{{ $stats['total_passengers'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Total manifest penumpang yang pernah dibuat.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Penerbangan</p>
                            <p class="admin-metric-value">{{ $stats['total_flights'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Jadwal yang sedang tersimpan di jaringan.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Tiket</p>
                            <p class="admin-metric-value">{{ $stats['total_tickets'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Ticket yang sudah diterbitkan sistem.</p>
                        </article>
                    </div>
                </div>

                <div class="space-y-4">
                    <article class="admin-command-aside">
                        <p class="admin-section-kicker">Ringkasan pendapatan</p>
                        <div class="mt-4 grid gap-3">
                            <div class="admin-command-stat">
                                <span>Revenue total</span>
                                <strong>Rp{{ number_format($stats['revenue_total'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="admin-command-stat">
                                <span>Revenue bulan ini</span>
                                <strong>Rp{{ number_format($stats['revenue_this_month'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="admin-command-stat">
                                <span>Antrean pembayaran</span>
                                <strong>{{ $stats['payment_pending'] }}</strong>
                            </div>
                        </div>
                    </article>

                    <article class="admin-surface-muted space-y-3">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="admin-section-kicker">Operations watch</p>
                                <h3 class="admin-section-title text-lg">Prioritas hari ini</h3>
                            </div>
                            <span class="admin-chip">{{ count($quickAlerts) }} alert</span>
                        </div>
                        @forelse ($quickAlerts as $alert)
                            <div class="rounded-2xl border border-amber-200 bg-white/90 px-4 py-3 text-sm text-amber-700">{{ $alert }}</div>
                        @empty
                            <div class="rounded-2xl border border-emerald-200 bg-white/90 px-4 py-3 text-sm text-emerald-700">Tidak ada alert kritis saat ini.</div>
                        @endforelse
                    </article>
                </div>
            </div>
        </article>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="admin-kpi-card">
                        <p class="admin-info-label">Booking Tertunda</p>
                <p class="mt-3 text-3xl font-bold text-amber-600">{{ $stats['booking_pending'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Booking yang masih menunggu aksi lanjutan.</p>
            </article>
            <article class="admin-kpi-card">
                        <p class="admin-info-label">Booking Terkonfirmasi</p>
                <p class="mt-3 text-3xl font-bold text-emerald-700">{{ $stats['booking_confirmed'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Transaksi yang sudah lolos proses pembayaran.</p>
            </article>
            <article class="admin-kpi-card">
                        <p class="admin-info-label">Booking Dibatalkan</p>
                <p class="mt-3 text-3xl font-bold text-red-600">{{ $stats['booking_cancelled'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Booking yang dibatalkan sistem atau manual.</p>
            </article>
            <article class="admin-kpi-card">
                        <p class="admin-info-label">Inbox Bantuan</p>
                <p class="mt-3 text-3xl font-bold text-[#c2410c]">{{ $stats['open_contact_messages'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Pesan bantuan yang masih terbuka.</p>
            </article>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.35fr_.95fr]">
            <article class="admin-card space-y-4">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Tren permintaan</p>
                        <h2 class="admin-section-title">Booking per bulan</h2>
                        <p class="admin-section-copy">Pergerakan jumlah booking untuk membaca pola demand dan kapasitas operasional.</p>
                    </div>
                    <span class="admin-chip">{{ $stats['total_bookings'] }} booking</span>
                </div>
                <div class="space-y-3">
                    @php($maxBooking = max($bookingChart->max('total'), 1))
                    @foreach ($bookingChart as $item)
                        <div class="grid gap-2 rounded-[24px] border border-orange-100/90 bg-white/80 px-4 py-3 sm:grid-cols-[110px_1fr_56px] sm:items-center">
                            <p class="text-sm font-semibold text-slate-700">{{ $item['month'] }}</p>
                            @include('admin.partials.progress-bar', ['percentage' => ($item['total'] / $maxBooking) * 100])
                            <p class="text-right text-sm font-semibold text-slate-600">{{ $item['total'] }}</p>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="admin-card space-y-4">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Komposisi tim</p>
                        <h2 class="admin-section-title">Struktur Backoffice</h2>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="admin-list-card">
                        <p class="admin-info-label">Staf</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ $stats['total_staff'] }}</p>
                    </div>
                    <div class="admin-list-card">
                        <p class="admin-info-label">Manajer</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ $stats['total_managers'] }}</p>
                    </div>
                    <div class="admin-list-card">
                        <p class="admin-info-label">Administrator</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ $stats['total_admins'] }}</p>
                    </div>
                    <div class="admin-list-card">
                        <p class="admin-info-label">Check-In Tertunda</p>
                        <p class="mt-2 text-2xl font-bold text-amber-600">{{ $stats['pending_checkins'] }}</p>
                    </div>
                    <div class="admin-list-card">
                        <p class="admin-info-label">Add-On Aktif</p>
                        <p class="mt-2 text-2xl font-bold text-[#c2410c]">{{ $stats['active_addons'] }}</p>
                    </div>
                    <div class="admin-list-card">
                        <p class="admin-info-label">Permintaan Perubahan Aktif</p>
                        <p class="mt-2 text-2xl font-bold text-red-600">{{ $stats['open_change_requests'] }}</p>
                    </div>
                    <div class="admin-list-card">
                        <p class="admin-info-label">Lunas vs Tertunda</p>
                        <p class="mt-2 text-sm font-semibold text-slate-700">Paid {{ $paymentStatusChart['paid'] }} / Pending {{ $paymentStatusChart['pending'] }}</p>
                    </div>
                </div>
            </article>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <article class="admin-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Permintaan terbaru</p>
                        <h2 class="admin-section-title">Booking terbaru</h2>
                    </div>
                    <a href="{{ route('admin.bookings.index') }}" class="admin-btn-secondary">Lihat semua</a>
                </div>
                <div class="admin-table-wrap mt-4">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>User</th>
                                <th>Flight</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentBookings as $booking)
                                <tr>
                                    <td><a class="font-semibold text-[#c2410c]" href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->booking_code }}</a></td>
                                    <td>{{ $booking->user?->name }}</td>
                                    <td>{{ $booking->flight?->flight_number }}</td>
                                    <td>@include('admin.partials.status-badge', ['status' => $booking->status])</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate-500">Belum ada data booking.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="admin-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Antrean kas</p>
                        <h2 class="admin-section-title">Payment terbaru</h2>
                    </div>
                    <a href="{{ route('admin.payments.index') }}" class="admin-btn-secondary">Lihat semua</a>
                </div>
                <div class="admin-table-wrap mt-4">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Booking</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentPayments as $payment)
                                <tr>
                                    <td><a class="font-semibold text-[#c2410c]" href="{{ route('admin.payments.show', $payment) }}">{{ $payment->booking?->booking_code }}</a></td>
                                    <td>{{ $payment->booking?->user?->name }}</td>
                                    <td>Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                    <td>@include('admin.partials.status-badge', ['status' => $payment->payment_status])</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-slate-500">Belum ada data pembayaran.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <article class="admin-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Pantauan bantuan</p>
                    <h2 class="admin-section-title">Inbox bantuan aktif</h2>
                    <p class="admin-section-copy">Kasus yang masih perlu assignment atau tindak lanjut dari tim backoffice.</p>
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
