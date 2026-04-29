@extends('layouts.portal')

@section('title', 'Cakrawala | Booking Tickets')
@section('active', 'bookings')

@section('content')
    <section class="ticket-sheet mx-auto max-w-6xl space-y-6">
        <article class="portal-card portal-print-hide">
            <div class="portal-section-head">
                <div class="min-w-0">
                    <p class="portal-kicker">Ticket stack</p>
                    <h1 class="portal-section-title">{{ $booking->booking_code }}</h1>
                    <p class="portal-section-copy">
                        {{ $flight->airline->name }} &middot; {{ $flight->departureAirport->code }} &rarr; {{ $flight->arrivalAirport->code }}
                    </p>
                </div>
                <span class="portal-inline-note">{{ $ticketDetails->count() }} passenger ticket{{ $ticketDetails->count() > 1 ? 's' : '' }}</span>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button type="button" onclick="window.print()" class="portal-btn-gold">Print All Tickets</button>
                <a href="{{ route('my-bookings.tickets.download-all', $booking) }}" class="portal-btn-blue">Download All PDFs</a>
                <a href="{{ route('my-bookings.show', $booking) }}" class="portal-btn-blue">Back to Booking</a>
            </div>
        </article>

        @foreach ($ticketDetails as $detail)
            @php($ticket = $detail->ticket)
            @include('user.tickets.partials.ticket-card', ['ticket' => $ticket, 'detail' => $detail, 'booking' => $booking, 'flight' => $flight, 'showSingleLink' => true, 'showPrintButton' => false])
        @endforeach
    </section>
@endsection
