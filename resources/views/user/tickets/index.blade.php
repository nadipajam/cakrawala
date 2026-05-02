@extends('layouts.portal')

@section('title', 'Cakrawala | Tiket Booking')
@section('active', 'bookings')

@section('content')
    <section class="ticket-lounge mx-auto max-w-7xl">
        <div class="space-y-6">
            <article class="journey-hero portal-print-hide">
                <div class="journey-hero-grid">
                    <div>
                        <span class="journey-chip">
                            <span class="journey-dot"></span>
                            Pusat Tiket
                        </span>
                        <h1 class="journey-title">{{ $booking->booking_code }}</h1>
                        <p class="journey-copy">
                            Semua e-ticket untuk {{ $flight->airline->name }} dikumpulkan di satu stack.
                            Anda tetap bisa mencetak semua tiket sekaligus atau membuka per penumpang.
                        </p>
                        <div class="journey-action-row mt-5">
                            <button type="button" onclick="window.print()" class="portal-btn-gold">Cetak Semua Tiket</button>
                            <a href="{{ route('my-bookings.tickets.download-all', $booking) }}" class="portal-btn-blue">Unduh Semua PDF</a>
                            <a href="{{ route('my-bookings.show', $booking) }}" class="portal-btn-blue">Kembali ke Booking</a>
                        </div>
                    </div>
                    <div class="journey-meta-grid">
                        <div class="journey-code">
                            <span class="journey-code-label">Route</span>
                            <span class="journey-code-value">{{ $flight->departureAirport->code }} -> {{ $flight->arrivalAirport->code }}</span>
                        </div>
                        <div class="journey-code">
                            <span class="journey-code-label">Jumlah tiket penumpang</span>
                            <span class="journey-code-value">{{ $ticketDetails->count() }}</span>
                        </div>
                    </div>
                </div>
            </article>

            <div class="ticket-sheet space-y-6">
                @foreach ($ticketDetails as $detail)
                    @php($ticket = $detail->ticket)
                    @include('user.tickets.partials.ticket-card', ['ticket' => $ticket, 'detail' => $detail, 'booking' => $booking, 'flight' => $flight, 'showSingleLink' => true, 'showPrintButton' => false])
                @endforeach
            </div>
        </div>

        <aside class="ticket-lounge-stack portal-print-hide">
            <article class="journey-rail-card journey-rail-card-dark">
                <p class="portal-kicker">Ringkasan penerbangan</p>
                <h2 class="mt-2 text-2xl font-bold">{{ $flight->airline->name }}</h2>
                <div class="mt-5 space-y-3">
                    <div class="portal-card-soft">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Departure</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $flight->departureAirport->code }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Arrival</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $flight->arrivalAirport->code }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Waktu keberangkatan</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $flight->departure_time->format('d M Y H:i') }}</p>
                    </div>
                </div>
            </article>
        </aside>
    </section>
@endsection
