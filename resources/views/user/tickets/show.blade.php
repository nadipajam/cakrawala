@extends('layouts.portal')

@section('title', 'Cakrawala | E-Ticket')
@section('active', 'bookings')

@section('content')
    <section class="ticket-sheet mx-auto max-w-5xl space-y-6">
        @include('user.tickets.partials.ticket-card', ['ticket' => $ticket, 'detail' => $detail, 'booking' => $booking, 'flight' => $flight, 'showSingleLink' => false])

        <article class="portal-card portal-print-hide">
            <div class="flex flex-wrap items-center gap-3">
                @if (($relatedTicketDetails->count() ?? 0) > 1)
                    <a href="{{ route('my-bookings.tickets', $booking) }}" class="portal-btn-gold">Open All Tickets</a>
                    <a href="{{ route('my-bookings.tickets.download-all', $booking) }}" class="portal-btn-blue">Download All PDFs</a>
                @endif
                <a href="{{ route('my-bookings.show', $booking) }}" class="portal-btn-blue">Back to Booking</a>
            </div>
        </article>

        @if (($relatedTicketDetails->count() ?? 0) > 1)
            <article class="portal-card portal-print-hide">
                <p class="portal-kicker">Related tickets</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Other Passenger Tickets</h2>
                <p class="portal-section-copy">Booking ini memiliki lebih dari satu e-ticket. Anda bisa membuka semua ticket sekaligus atau memilih ticket per passenger.</p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($relatedTicketDetails as $relatedDetail)
                        <a
                            href="{{ route('tickets.show', $relatedDetail->ticket) }}"
                            @class([
                                'portal-card-soft block transition hover:bg-white',
                                'ring-2 ring-[#0f3f78] border-[#0f3f78]' => $relatedDetail->ticket->id === $ticket->id,
                            ])
                        >
                            <p class="font-semibold text-slate-800">{{ $relatedDetail->passenger?->full_name }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $relatedDetail->seat?->seat_number }} &middot; {{ $relatedDetail->ticket_number }}</p>
                        </a>
                    @endforeach
                </div>
            </article>
        @endif
    </section>
@endsection
