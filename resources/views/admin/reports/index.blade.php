@extends('layouts.admin')

@section('title', 'Admin Reports | Cakrawala')
@section('page-title', 'Reports')

@section('content')
    @php($activeFilterCount = collect($filters)->filter(fn ($value) => $value !== null && $value !== '' && $value !== 0)->count())
    <section class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[340px_1fr]">
            <form method="GET" class="admin-ops-filter space-y-5">
                <div>
                    <p class="admin-section-kicker">Report Builder</p>
                    <h2 class="admin-section-title">Bangun laporan operasional</h2>
                    <p class="admin-section-copy">Panel filter dibuat seperti control rail agar ringkasan dan tabel insight di sisi kanan tetap dominan saat laporan dibaca.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="date_from">Date From</label>
                        <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}" class="admin-field">
                    </div>
                    <div>
                        <label class="admin-label" for="date_to">Date To</label>
                        <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}" class="admin-field">
                    </div>
                    <div>
                        <label class="admin-label" for="airline_id">Airline</label>
                        <select id="airline_id" name="airline_id" class="admin-field">
                            <option value="">Semua airline</option>
                            @foreach ($airlines as $airline)
                                <option value="{{ $airline->id }}" @selected((int) $filters['airline_id'] === (int) $airline->id)>{{ $airline->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-label" for="route">Route</label>
                        <input id="route" name="route" value="{{ $filters['route'] }}" class="admin-field" placeholder="CGK / DPS">
                    </div>
                    <div>
                        <label class="admin-label" for="payment_status">Payment Status</label>
                        <select id="payment_status" name="payment_status" class="admin-field">
                            <option value="">Semua</option>
                            <option value="pending" @selected($filters['payment_status'] === 'pending')>Pending</option>
                            <option value="paid" @selected($filters['payment_status'] === 'paid')>Paid</option>
                            <option value="failed" @selected($filters['payment_status'] === 'failed')>Failed</option>
                            <option value="refunded" @selected($filters['payment_status'] === 'refunded')>Refunded</option>
                        </select>
                    </div>
                    <div>
                        <label class="admin-label" for="booking_status">Booking Status</label>
                        <select id="booking_status" name="booking_status" class="admin-field">
                            <option value="">Semua</option>
                            <option value="pending" @selected($filters['booking_status'] === 'pending')>Pending</option>
                            <option value="confirmed" @selected($filters['booking_status'] === 'confirmed')>Confirmed</option>
                            <option value="cancelled" @selected($filters['booking_status'] === 'cancelled')>Cancelled</option>
                            <option value="completed" @selected($filters['booking_status'] === 'completed')>Completed</option>
                        </select>
                    </div>
                </div>

                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Export CSV memuat KPI, breakdown status, metode pembayaran, revenue, route populer, dan detail booking.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Generate</button>
                        <a href="{{ route('admin.reports.index', array_merge(request()->query(), ['export' => 'csv'])) }}" class="admin-btn-secondary">Export CSV</a>
                    </div>
                </div>
            </form>

            <div class="space-y-6">
                <article class="admin-ops-hero">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Report Canvas</p>
                            <h2 class="admin-section-title">Ringkasan performa berdasarkan filter aktif</h2>
                            <p class="admin-section-copy">Insight utama dipindahkan ke kanvas laporan supaya pembacaan KPI, route, revenue, dan metode pembayaran terasa lebih utuh.</p>
                        </div>
                        <span class="admin-chip">{{ $activeFilterCount > 0 ? $activeFilterCount.' filter aktif' : 'Semua data' }}</span>
                    </div>

                    <div class="admin-ops-summary-grid xl:grid-cols-5">
                        <article class="admin-ops-summary-card">
                            <p class="label">Total Booking</p>
                            <p class="value">{{ $summary['total_bookings'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Transaksi booking sesuai filter laporan.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Payment Paid</p>
                            <p class="value text-emerald-700">{{ $summary['total_paid_payments'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Pembayaran berhasil tervalidasi.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Cancelled Booking</p>
                            <p class="value text-red-600">{{ $summary['total_cancelled_bookings'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Booking yang dibatalkan di periode ini.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Flight Aktif</p>
                            <p class="value">{{ $summary['total_active_flights'] }}</p>
                            <p class="mt-2 text-sm text-slate-500">Status flight scheduled atau delayed.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Pendapatan</p>
                            <p class="value text-emerald-700">Rp{{ number_format((float) $summary['revenue_total'], 0, ',', '.') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Akumulasi nominal payment berstatus paid.</p>
                        </article>
                    </div>
                </article>

                <div class="grid gap-6 xl:grid-cols-3">
                    <article class="admin-ops-table-card xl:col-span-2">
                        <div class="admin-section-head">
                            <div>
                                <p class="admin-section-kicker">Traffic Insight</p>
                                <h2 class="admin-section-title">Popular Routes</h2>
                                <p class="admin-section-copy">Rute yang paling sering menghasilkan booking dalam filter laporan saat ini.</p>
                            </div>
                        </div>
                        <div class="admin-table-wrap mt-4">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Route</th>
                                        <th>Flight Number</th>
                                        <th>Total Bookings</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($summary['popular_routes'] as $route)
                                        <tr>
                                            <td>{{ $route['route'] }}</td>
                                            <td>{{ $route['flight_number'] }}</td>
                                            <td>{{ $route['total_bookings'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-slate-500">Belum ada data route.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <article class="admin-ops-table-card">
                        <div class="admin-section-head">
                            <div>
                                <p class="admin-section-kicker">Booking Mix</p>
                                <h2 class="admin-section-title">Booking Status</h2>
                                <p class="admin-section-copy">Distribusi status booking dari data yang sudah difilter.</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
                            @php($maxBookingStatus = max(collect($reportData['booking_status_breakdown'])->max() ?? 0, 1))
                            @forelse ($reportData['booking_status_breakdown'] as $status => $total)
                                <div class="space-y-2 rounded-2xl border border-slate-200 bg-white/75 p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <p class="text-sm font-semibold text-slate-700">{{ ucfirst((string) $status) }}</p>
                                        <p class="text-sm text-slate-500">{{ $total }} booking</p>
                                    </div>
                                    @include('admin.partials.progress-bar', ['percentage' => ($total / $maxBookingStatus) * 100])
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Belum ada distribusi status booking.</p>
                            @endforelse
                        </div>
                    </article>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <article class="admin-ops-table-card">
                        <div class="admin-section-head">
                            <div>
                                <p class="admin-section-kicker">Revenue Trend</p>
                                <h2 class="admin-section-title">Revenue Per Month</h2>
                                <p class="admin-section-copy">Pergerakan pendapatan berdasarkan transaksi paid per bulan.</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
                            @php($maxRevenue = max(collect($summary['monthly_revenue'])->max('total') ?? 0, 1))
                            @forelse ($summary['monthly_revenue'] as $item)
                                <div class="grid gap-2 sm:grid-cols-[100px_1fr_120px] sm:items-center">
                                    <p class="text-sm text-slate-600">{{ $item['month'] }}</p>
                                    @include('admin.partials.progress-bar', ['percentage' => ($item['total'] / $maxRevenue) * 100])
                                    <p class="text-right text-sm font-semibold text-slate-700">Rp{{ number_format((float) $item['total'], 0, ',', '.') }}</p>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Belum ada data revenue bulanan.</p>
                            @endforelse
                        </div>
                    </article>

                    <article class="admin-ops-table-card">
                        <div class="admin-section-head">
                            <div>
                                <p class="admin-section-kicker">Payment Preference</p>
                                <h2 class="admin-section-title">Payment Methods</h2>
                                <p class="admin-section-copy">Metode pembayaran yang paling sering digunakan dan nominal transaksinya.</p>
                            </div>
                        </div>
                        <div class="mt-4 space-y-3">
                            @php($maxPaymentMethod = max(collect($reportData['payment_method_breakdown'])->max('total_transactions') ?? 0, 1))
                            @forelse ($reportData['payment_method_breakdown'] as $item)
                                <div class="space-y-2 rounded-2xl border border-slate-200 bg-white/75 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-slate-800">{{ \App\Support\PaymentMethodCatalog::label($item['payment_method']) }}</p>
                                            <p class="text-sm text-slate-500">{{ $item['total_transactions'] }} transaksi</p>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-700">Rp{{ number_format((float) $item['total_amount'], 0, ',', '.') }}</p>
                                    </div>
                                    @include('admin.partials.progress-bar', ['percentage' => ($item['total_transactions'] / $maxPaymentMethod) * 100])
                                </div>
                            @empty
                                <p class="text-sm text-slate-500">Belum ada data metode pembayaran.</p>
                            @endforelse
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
@endsection
