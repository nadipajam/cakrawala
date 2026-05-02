@extends('layouts.portal')

@section('title', 'Cakrawala | E-Ticket')
@section('active', 'bookings')

@section('content')
    <section class="ticket-lounge mx-auto max-w-7xl">
        <div class="space-y-6">
            <article class="journey-hero portal-print-hide">
                <div class="journey-hero-grid">
                    <div>
                        <span class="journey-chip">
                            <span class="journey-dot"></span>
                            Detail E-Ticket
                        </span>
                        <h1 class="journey-title">{{ $detail->ticket_number ?: 'Tiket' }}</h1>
                        <p class="journey-copy">
                            Buka e-ticket per penumpang tanpa mengubah akses ke tiket lain dalam booking yang sama.
                        </p>
                    </div>
                    <div class="journey-meta-grid">
                        <div class="journey-code">
                            <span class="journey-code-label">Passenger</span>
                            <span class="journey-code-value">{{ $detail->passenger?->full_name }}</span>
                        </div>
                        <div class="journey-code">
                            <span class="journey-code-label">Booking Code</span>
                            <span class="journey-code-value">{{ $booking->booking_code }}</span>
                        </div>
                    </div>
                </div>
            </article>

            @include('user.tickets.partials.ticket-card', ['ticket' => $ticket, 'detail' => $detail, 'booking' => $booking, 'flight' => $flight, 'showSingleLink' => false])
        </div>

        <aside class="ticket-lounge-stack portal-print-hide">
            <article class="journey-rail-card journey-rail-card-dark">
                <p class="portal-kicker">Aksi tiket</p>
                <div class="mt-4 space-y-3">
                    @if (($relatedTicketDetails->count() ?? 0) > 1)
                        <a href="{{ route('my-bookings.tickets', $booking) }}" class="ticket-lounge-link">
                            <span class="ticket-lounge-link-title">Buka Semua Tiket</span>
                            <span class="ticket-lounge-link-copy">Kembali ke daftar semua tiket penumpang.</span>
                        </a>
                        <a href="{{ route('my-bookings.tickets.download-all', $booking) }}" class="ticket-lounge-link">
                            <span class="ticket-lounge-link-title">Unduh Semua PDF</span>
                            <span class="ticket-lounge-link-copy">Unduh satu paket e-ticket booking ini.</span>
                        </a>
                    @endif
                    <a href="{{ route('my-bookings.show', $booking) }}" class="ticket-lounge-link">
                        <span class="ticket-lounge-link-title">Kembali ke Booking</span>
                        <span class="ticket-lounge-link-copy">Kembali ke halaman detail booking.</span>
                    </a>
                </div>
            </article>

            @if (($relatedTicketDetails->count() ?? 0) > 1)
                <article class="ticket-lounge-card">
                    <p class="portal-kicker">Tiket terkait</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Tiket Penumpang Lain</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($relatedTicketDetails as $relatedDetail)
                            <a
                                href="{{ route('tickets.show', $relatedDetail->ticket) }}"
                                @class([
                                    'ticket-lounge-link',
                                    'ticket-lounge-link-active' => $relatedDetail->ticket->id === $ticket->id,
                                ])
                            >
                                <span class="ticket-lounge-link-title">{{ $relatedDetail->passenger?->full_name }}</span>
                                <span class="ticket-lounge-link-copy">{{ $relatedDetail->seat?->seat_number }} | {{ $relatedDetail->ticket_number }}</span>
                            </a>
                        @endforeach
                    </div>
                </article>
            @endif
        </aside>
    </section>
@endsection
