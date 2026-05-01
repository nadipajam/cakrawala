@extends('layouts.admin')

@section('title', 'Admin Tickets | Cakrawala')
@section('page-title', 'Tickets')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_1fr]">
            <form method="GET" class="admin-ops-filter space-y-5">
                <div>
                    <p class="admin-section-kicker">Ticketing Desk</p>
                    <h2 class="admin-section-title">Filter ticket terbit</h2>
                    <p class="admin-section-copy">Cari ticket berdasarkan booking, passenger, flight, atau nomor ticket untuk kebutuhan audit dan reissue.</p>
                </div>
                <div>
                    <label class="admin-label" for="search">Search Booking/Passenger/Flight/Ticket</label>
                    <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="Ticket number, booking, passenger, flight">
                </div>
                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Panel ini memisahkan pencarian dari tabel agar daftar ticket lebih mudah dipindai di layar sempit.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a href="{{ route('admin.tickets.index') }}" class="admin-btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-ops-hero">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Ticket Ledger</p>
                            <h2 class="admin-section-title">Daftar ticket aktif</h2>
                            <p class="admin-section-copy">Nomor ticket, pemilik kursi, dan flight terkait ditampilkan dalam kanvas yang lebih ringkas dan terstruktur.</p>
                        </div>
                        <span class="admin-chip">{{ $tickets->total() }} ticket</span>
                    </div>
                    <div class="admin-ops-summary-grid">
                        <article class="admin-ops-summary-card">
                            <p class="label">Visible tickets</p>
                            <p class="value">{{ $tickets->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Row yang tampil pada halaman aktif.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Issued today</p>
                            <p class="value text-[#0f3f78]">{{ $tickets->filter(fn ($ticket) => optional($ticket->issued_at)?->isToday())->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Ticket yang diterbitkan hari ini.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Linked bookings</p>
                            <p class="value text-emerald-700">{{ $tickets->filter(fn ($ticket) => filled($ticket->bookingDetail?->booking_id))->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Ticket yang terhubung ke booking detail.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-ops-table-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Ticket Number</th>
                                    <th>Passenger</th>
                                    <th>Booking</th>
                                    <th>Flight</th>
                                    <th>Seat</th>
                                    <th>Issued At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($tickets as $ticket)
                                    @php($detail = $ticket->bookingDetail)
                                    <tr>
                                        <td class="font-semibold">{{ $detail?->ticket_number ?: '-' }}</td>
                                        <td>{{ $detail?->passenger?->full_name }}</td>
                                        <td>{{ $detail?->booking?->booking_code }}</td>
                                        <td>{{ $detail?->booking?->flight?->flight_number }}</td>
                                        <td>{{ $detail?->seat?->seat_number }}</td>
                                        <td>{{ $ticket->issued_at?->format('d M Y H:i') ?: '-' }}</td>
                                        <td><a href="{{ route('admin.tickets.show', $ticket) }}" class="admin-btn-secondary">Detail</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-slate-500">Data ticket belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $tickets->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
