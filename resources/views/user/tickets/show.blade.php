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
                            E-Ticket Detail
                        </span>
                        <h1 class="journey-title">{{ $detail->ticket_number ?: 'Ticket' }}</h1>
                        <p class="journey-copy">
                            Buka e-ticket per passenger tanpa mengubah akses ke ticket lain dalam booking yang sama.
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
                <p class="portal-kicker">Ticket Actions</p>
                <div class="mt-4 space-y-3">
                    @if (($relatedTicketDetails->count() ?? 0) > 1)
                        <a href="{{ route('my-bookings.tickets', $booking) }}" class="ticket-lounge-link">
                            <span class="ticket-lounge-link-title">Open All Tickets</span>
                            <span class="ticket-lounge-link-copy">Kembali ke stack semua passenger ticket.</span>
                        </a>
                        <a href="{{ route('my-bookings.tickets.download-all', $booking) }}" class="ticket-lounge-link">
                            <span class="ticket-lounge-link-title">Download All PDFs</span>
                            <span class="ticket-lounge-link-copy">Unduh satu paket e-ticket booking ini.</span>
                        </a>
                    @endif
                    <a href="{{ route('my-bookings.show', $booking) }}" class="ticket-lounge-link">
                        <span class="ticket-lounge-link-title">Back to Booking</span>
                        <span class="ticket-lounge-link-copy">Kembali ke control panel booking.</span>
                    </a>
                </div>
            </article>

            @if (($relatedTicketDetails->count() ?? 0) > 1)
                <article class="ticket-lounge-card">
                    <p class="portal-kicker">Related Tickets</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-900">Other Passenger Tickets</h2>
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
