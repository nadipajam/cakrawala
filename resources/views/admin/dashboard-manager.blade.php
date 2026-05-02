@extends('layouts.admin')

@section('title', 'Manager Dashboard | Cakrawala')
@section('page-title', 'Dashboard Manajerial')

@section('content')
    <section class="space-y-6">
        <article class="admin-hero-card admin-hero-card-management">
            <div class="grid gap-6 xl:grid-cols-[1.3fr_minmax(0,1fr)]">
                <div class="space-y-5">
                    <div class="admin-section-head">
                        <div class="max-w-2xl">
                            <p class="admin-section-kicker">Tampilan manajemen</p>
                            <h2 class="admin-section-title">Dashboard manager lebih tenang, lebih strategis, dan lebih fokus ke pembacaan tren.</h2>
                            <p class="admin-section-copy">Komposisinya dibuat berbeda dari dashboard staff: sedikit aksi langsung, lebih banyak ruang untuk membaca revenue, route populer, support load, dan komposisi tim.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('admin.reports.index') }}" class="admin-btn-primary">Buka Laporan</a>
                            <a href="{{ route('admin.contact-messages.index') }}" class="admin-btn-secondary">Buka Inbox</a>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Revenue total</p>
                            <p class="admin-metric-value text-emerald-700">Rp{{ number_format((float) $stats['revenue_total'], 0, ',', '.') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Akumulasi pembayaran paid seluruh periode.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Pendapatan bulan ini</p>
                            <p class="admin-metric-value text-emerald-700">Rp{{ number_format((float) $stats['revenue_this_month'], 0, ',', '.') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Performa pendapatan berjalan bulan ini.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Total booking</p>
                            <p class="admin-metric-value">{{ $stats['total_bookings'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Booking yang sudah tercatat di sistem.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Bantuan aktif</p>
                            <p class="admin-metric-value text-[#c2410c]">{{ $stats['open_contact_messages'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Case support yang masih menunggu penanganan.</p>
                        </article>
                    </div>
                </div>

                <div class="space-y-4">
                    <article class="admin-decision-panel">
                        <p class="admin-section-kicker">Decision snapshot</p>
                        <div class="mt-4 grid gap-3">
                            <div class="admin-decision-item">
                                <span>Revenue bulan ini</span>
                                <strong>Rp{{ number_format((float) $stats['revenue_this_month'], 0, ',', '.') }}</strong>
                            </div>
                            <div class="admin-decision-item">
                                <span>Antrean bantuan</span>
                                <strong>{{ $stats['open_contact_messages'] }}</strong>
                            </div>
                            <div class="admin-decision-item">
                                <span>Booking base</span>
                                <strong>{{ $stats['total_bookings'] }}</strong>
                            </div>
                        </div>
                    </article>

                    <article class="admin-surface-muted">
                        <p class="admin-section-kicker">Panduan</p>
                        <h3 class="mt-2 font-heading text-lg font-bold text-slate-800">Pembacaan cepat</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Bagian bawah halaman memisahkan insight route populer, tren revenue, distribusi role, dan support watch agar keputusan operasional tidak bercampur dengan detail transaksi.</p>
                    </article>
                </div>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-3">
            <article class="admin-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Komposisi tim</p>
                        <h2 class="admin-section-title">Distribusi Peran</h2>
                    </div>
                </div>
                <div class="mt-4 space-y-3">
                    @php($maxRoleMix = max(collect($roleMix)->max() ?? 0, 1))
                    @foreach ($roleMix as $role => $count)
                        <div class="space-y-2 rounded-2xl border border-slate-200 bg-white/75 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-semibold text-slate-700">{{ \App\Support\UserRole::label($role) }}</p>
                                <p class="text-sm text-slate-500">{{ $count }}</p>
                            </div>
                            @include('admin.partials.progress-bar', ['percentage' => ($count / $maxRoleMix) * 100])
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="admin-card xl:col-span-2">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Performa utama</p>
                        <h2 class="admin-section-title">Rute populer</h2>
                    </div>
                    <a href="{{ route('admin.reports.index') }}" class="admin-btn-secondary">Buka Laporan</a>
                </div>
                <div class="admin-table-wrap mt-4">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Route</th>
                                <th>Flight</th>
                                <th>Bookings</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($popularRoutes as $route)
                                <tr>
                                    <td>{{ $route['route'] }}</td>
                                    <td>{{ $route['flight_number'] }}</td>
                                    <td>{{ $route['total_bookings'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-slate-500">Belum ada data route populer.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <article class="admin-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Tren pendapatan</p>
                        <h2 class="admin-section-title">Pendapatan bulanan</h2>
                    </div>
                </div>
                <div class="mt-4 space-y-3">
                    @php($maxRevenue = max(collect($monthlyRevenue)->max('total') ?? 0, 1))
                    @forelse ($monthlyRevenue as $item)
                        <div class="grid gap-2 sm:grid-cols-[110px_1fr_130px] sm:items-center">
                            <p class="text-sm text-slate-600">{{ $item['month'] }}</p>
                            @include('admin.partials.progress-bar', ['percentage' => ($item['total'] / $maxRevenue) * 100])
                            <p class="text-right text-sm font-semibold text-slate-700">Rp{{ number_format((float) $item['total'], 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada data revenue bulanan.</p>
                    @endforelse
                </div>
            </article>

            <article class="admin-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Pantauan bantuan</p>
                        <h2 class="admin-section-title">Permintaan bantuan aktif</h2>
                    </div>
                    <a href="{{ route('admin.contact-messages.index') }}" class="admin-btn-secondary">Buka Inbox</a>
                </div>
                <div class="admin-table-wrap mt-4">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>PIC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($openMessages as $message)
                                <tr>
                                    <td>{{ $message->subject }}</td>
                                    <td>{{ $message->name }}</td>
                                    <td>@include('admin.partials.status-badge', ['status' => $message->status])</td>
                                    <td>{{ $message->assignedUser?->name ?: 'Belum ditugaskan' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-slate-500">Tidak ada case support terbuka.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>
    </section>
@endsection
