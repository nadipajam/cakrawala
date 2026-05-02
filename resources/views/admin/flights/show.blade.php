@extends('layouts.admin')

@section('title', 'Detail Penerbangan | Cakrawala')
@section('page-title', 'Detail Penerbangan')

@section('content')
    <section class="space-y-6">
        <article class="admin-ops-detail-hero">
            <div class="admin-section-head">
                <div class="max-w-3xl">
                    <p class="admin-section-kicker">Ringkasan penerbangan</p>
                    <h2 class="admin-section-title">{{ $flight->flight_number }}</h2>
                    <p class="admin-section-copy">Ringkasan airline, rute, kapasitas, pembayaran, dan booking pada jadwal ini dikelompokkan agar mudah dipakai untuk kontrol operasional.</p>
                </div>
                <div>@include('admin.partials.status-badge', ['status' => $flight->status])</div>
            </div>

            <div class="mt-5 grid gap-4 xl:grid-cols-[1.35fr_.95fr]">
                <div class="admin-ops-inline-grid">
                    <div class="admin-ops-info-card"><p class="admin-info-label">Nomor Penerbangan</p><p class="admin-info-value">{{ $flight->flight_number }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Maskapai</p><p class="admin-info-value">{{ $flight->airline?->name }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Pesawat</p><p class="admin-info-value">{{ $flight->airplane?->model }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Harga</p><p class="admin-info-value">Rp{{ number_format((float) $flight->price, 0, ',', '.') }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Keberangkatan</p><p class="admin-info-value">{{ $flight->departureAirport?->code }} - {{ $flight->departure_time?->format('d M Y H:i') }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Kedatangan</p><p class="admin-info-value">{{ $flight->arrivalAirport?->code }} - {{ $flight->arrival_time?->format('d M Y H:i') }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Kursi terisi</p><p class="admin-info-value">{{ $bookedSeatIds->count() }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Kursi tersedia</p><p class="admin-info-value">{{ $availableSeats }}</p></div>
                </div>

                <aside class="space-y-4">
                    <div class="admin-ops-sidecard">
                        <p class="admin-section-kicker">Status operasional</p>
                        <h3 class="mt-2 font-heading text-lg font-bold text-slate-800">Perbarui operasional</h3>
                        <form method="POST" action="{{ route('admin.flights.status', $flight) }}" class="mt-4 space-y-3">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="admin-field" required>
                                <option value="scheduled" @selected($flight->status === 'scheduled')>Terjadwal</option>
                                <option value="delayed" @selected($flight->status === 'delayed')>Tertunda</option>
                                <option value="cancelled" @selected($flight->status === 'cancelled')>Dibatalkan</option>
                                <option value="completed" @selected($flight->status === 'completed')>Selesai</option>
                            </select>
                            <button class="admin-btn-primary w-full" type="submit">Perbarui Status</button>
                        </form>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                        <article class="admin-ops-sidecard">
                            <p class="admin-info-label">Pembayaran lunas</p>
                            <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $paymentSummary['paid'] }}</p>
                        </article>
                        <article class="admin-ops-sidecard">
                            <p class="admin-info-label">Pembayaran tertunda</p>
                            <p class="mt-2 text-2xl font-bold text-amber-600">{{ $paymentSummary['pending'] }}</p>
                        </article>
                        <article class="admin-ops-sidecard">
                            <p class="admin-info-label">Pembayaran gagal</p>
                            <p class="mt-2 text-2xl font-bold text-red-600">{{ $paymentSummary['failed'] }}</p>
                        </article>
                    </div>
                </aside>
            </div>
        </article>

        <article class="admin-ops-table-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Manifest booking</p>
                    <h2 class="admin-section-title">Daftar booking</h2>
                </div>
                <span class="admin-chip">{{ $flight->bookings->count() }} booking</span>
            </div>
            <div class="admin-table-wrap mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Pengguna</th>
                            <th>Penumpang</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Tiket Terbit</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($flight->bookings as $booking)
                            <tr>
                                <td>{{ $booking->booking_code }}</td>
                                <td>{{ $booking->user?->name }}</td>
                                <td>{{ $booking->total_passengers }}</td>
                                <td>Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</td>
                                <td>@include('admin.partials.status-badge', ['status' => $booking->status])</td>
                                <td>{{ $booking->details->whereNotNull('ticket')->count() }}</td>
                                <td><a href="{{ route('admin.bookings.show', $booking) }}" class="admin-btn-secondary">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-slate-500">Belum ada booking untuk penerbangan ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
