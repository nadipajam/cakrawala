@php($showPrintButton = $showPrintButton ?? true)

<article class="portal-ticket-shell min-w-0 overflow-hidden print:break-inside-avoid">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <p class="portal-kicker">E-Ticket</p>
            <h2 class="ticket-number mt-3 max-w-full break-all font-heading font-bold text-slate-800">{{ $detail->ticket_number ?: 'Ticket' }}</h2>
            <p class="mt-2 text-slate-600">{{ $flight->airline->name }} &middot; {{ $booking->booking_code }}</p>
        </div>
        @if ($ticket->issued_at)
            <span class="portal-status-confirmed whitespace-nowrap">Issued {{ $ticket->issued_at->format('d M Y H:i') }}</span>
        @endif
    </div>

    <div class="portal-ticket-grid">
        <div class="portal-card-soft">
            <p class="text-sm text-slate-500">Passenger</p>
            <p class="text-lg font-semibold text-slate-800">{{ $detail->passenger?->full_name }}</p>
        </div>
        <div class="portal-card-soft">
            <p class="text-sm text-slate-500">Seat</p>
            <p class="text-lg font-semibold text-slate-800">{{ $detail->seat?->seat_number }} &middot; {{ ucfirst($detail->seat?->class ?? '-') }}</p>
        </div>
        <div class="portal-card-soft">
            <p class="text-sm text-slate-500">Route</p>
            <p class="text-lg font-semibold text-slate-800">{{ $flight->departureAirport->code }} &rarr; {{ $flight->arrivalAirport->code }}</p>
        </div>
        <div class="portal-card-soft">
            <p class="text-sm text-slate-500">Boarding Time</p>
            <p class="text-lg font-semibold text-slate-800">{{ $flight->departure_time->format('d M Y H:i') }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-[1fr_280px]">
        <div class="portal-surface-muted">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-slate-500">Ticket Number</p>
                    <p class="mt-1 text-xl font-semibold text-slate-800">{{ $detail->ticket_number }}</p>
                </div>
                <a href="{{ route('tickets.qr', $ticket) }}" target="_blank" class="portal-btn-blue portal-print-hide">Open QR</a>
            </div>
            <div class="mt-4 flex items-center justify-center rounded-2xl border border-dashed border-orange-200 bg-white/90 p-6">
                <img src="{{ route('tickets.qr', $ticket) }}" alt="QR Ticket {{ $detail->passenger?->full_name }}" class="h-44 w-44 object-contain sm:h-48 sm:w-48">
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                <span class="font-semibold text-slate-700">Boarding:</span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ ucfirst(str_replace('_', ' ', (string) $detail->boarding_status)) }}</span>
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <a href="{{ route('tickets.pdf', $ticket) }}" class="portal-btn-gold portal-print-hide w-full justify-center">Download PDF</a>
            @if (in_array($detail->boarding_status, ['checked_in', 'boarded'], true))
                <a href="{{ route('my-bookings.checkin.pdf', [$booking, $detail]) }}" class="portal-btn-blue portal-print-hide w-full justify-center">Boarding Pass PDF</a>
            @elseif (in_array($booking->status, ['confirmed', 'completed'], true))
                <a href="{{ route('my-bookings.checkin.index', $booking) }}" class="portal-btn-blue portal-print-hide w-full justify-center">Open Check-In</a>
            @endif
            @if ($showPrintButton)
                <button type="button" onclick="window.print()" class="portal-btn-blue portal-print-hide w-full justify-center">Print</button>
            @endif
            @if (($showSingleLink ?? false) === true)
                <a href="{{ route('tickets.show', $ticket) }}" class="portal-btn-blue portal-print-hide w-full justify-center">Open Single Ticket</a>
            @endif
        </div>
    </div>
</article>
