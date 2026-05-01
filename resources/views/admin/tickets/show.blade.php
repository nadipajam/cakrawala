@extends('layouts.admin')

@section('title', 'Ticket Detail | Cakrawala')
@section('page-title', 'Ticket Detail')

@section('content')
    @php($detail = $ticket->bookingDetail)
    @php($booking = $detail?->booking)
    @php($flight = $booking?->flight)

    <section class="space-y-6">
        <article class="admin-ops-detail-hero">
            <div class="admin-section-head">
                <div class="max-w-3xl">
                    <p class="admin-section-kicker">Ticket Review</p>
                    <h2 class="admin-section-title">{{ $detail?->ticket_number ?: 'Ticket detail' }}</h2>
                    <p class="admin-section-copy">Tampilan detail ini menggabungkan data passenger, booking, seat, dan file output ticket agar proses reissue atau audit lebih cepat.</p>
                </div>
                @if ($booking)
                    <div>@include('admin.partials.status-badge', ['status' => $booking?->status])</div>
                @endif
            </div>

            <div class="mt-5 grid gap-4 xl:grid-cols-[1.35fr_.95fr]">
                <div class="admin-ops-inline-grid">
                    <div class="admin-ops-info-card"><p class="admin-info-label">Ticket Number</p><p class="admin-info-value">{{ $detail?->ticket_number ?: '-' }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Booking Code</p><p class="admin-info-value">{{ $booking?->booking_code ?: '-' }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Passenger</p><p class="admin-info-value">{{ $detail?->passenger?->full_name ?: '-' }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Seat</p><p class="admin-info-value">{{ $detail?->seat?->seat_number ?: '-' }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Flight</p><p class="admin-info-value">{{ $flight?->flight_number ?: '-' }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Route</p><p class="admin-info-value">{{ $flight?->departureAirport?->code ?: '-' }} - {{ $flight?->arrivalAirport?->code ?: '-' }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Issued At</p><p class="admin-info-value">{{ $ticket->issued_at?->format('d M Y H:i') ?: '-' }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Booking Status</p><div class="mt-2">@include('admin.partials.status-badge', ['status' => $booking?->status])</div></div>
                </div>

                <aside class="space-y-4">
                    <div class="admin-ops-sidecard space-y-3">
                        <p class="admin-section-kicker">Ticket Actions</p>
                        <h3 class="font-heading text-lg font-bold text-slate-800">Output dan reissue</h3>
                        @if (auth()->user()->isAdmin() || auth()->user()->isStaff())
                            <form method="POST" action="{{ route('admin.tickets.regenerate', $ticket) }}">
                                @csrf
                                <button class="admin-btn-primary w-full" type="submit">Regenerate Ticket</button>
                            </form>
                        @endif

                        @if ($ticket->pdf_path)
                            <a href="{{ route('admin.tickets.pdf', $ticket) }}" class="admin-btn-secondary w-full">Download PDF</a>
                        @endif

                        @if ($ticket->qr_code_path)
                            <a href="{{ route('admin.tickets.qr', $ticket) }}" target="_blank" class="admin-btn-secondary w-full">Open QR</a>
                        @endif
                    </div>

                    <div class="admin-ops-sidecard">
                        <p class="admin-section-kicker">Related Booking</p>
                        @if ($booking)
                            <p class="mt-2 font-semibold text-slate-800">{{ $booking->user?->name }} ({{ $booking->user?->email }})</p>
                            <p class="mt-2 text-sm text-slate-600">Total harga: Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</p>
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="admin-btn-secondary mt-4">Lihat Booking</a>
                        @else
                            <p class="mt-2 text-sm text-slate-600">Booking terkait tidak ditemukan.</p>
                        @endif
                    </div>
                </aside>
            </div>
        </article>
    </section>
@endsection
