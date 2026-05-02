@php($showPrintButton = $showPrintButton ?? true)
@php($hasBoardingPass = in_array($detail->boarding_status, ['checked_in', 'boarded'], true))
@php($qrRoute = $hasBoardingPass ? route('my-bookings.checkin.qr', [$booking, $detail]) : route('tickets.qr', $ticket))

<article class="portal-ticket-shell min-w-0 overflow-hidden print:break-inside-avoid">
    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_300px]">
        <div class="min-w-0">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="portal-kicker">Arsip e-ticket</p>
                    <h2 class="ticket-number mt-3 max-w-full break-all font-heading font-bold text-slate-800">{{ $detail->ticket_number ?: 'Tiket' }}</h2>
                    <p class="mt-2 text-slate-600">{{ $flight->airline->name }} | {{ $booking->booking_code }}</p>
                </div>
                @if ($ticket->issued_at)
                    <span class="portal-status-confirmed whitespace-nowrap">Diterbitkan {{ $ticket->issued_at->format('d M Y H:i') }}</span>
                @endif
            </div>

            <div class="portal-ticket-grid">
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Passenger</p>
                    <p class="text-lg font-semibold text-slate-800">{{ $detail->passenger?->full_name }}</p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Seat</p>
                    <p class="text-lg font-semibold text-slate-800">{{ $detail->seat?->seat_number }} | {{ ucfirst($detail->seat?->class ?? '-') }}</p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Route</p>
                    <p class="text-lg font-semibold text-slate-800">{{ $flight->departureAirport->code }} -> {{ $flight->arrivalAirport->code }}</p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Waktu boarding</p>
                    <p class="text-lg font-semibold text-slate-800">{{ $flight->departure_time->format('d M Y H:i') }}</p>
                </div>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="journey-manifest-card">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Nomor tiket</p>
                    <p class="mt-2 text-xl font-semibold text-slate-800">{{ $detail->ticket_number }}</p>
                </div>
                <div class="journey-manifest-card">
                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Status boarding</p>
                    <div class="mt-2">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">{{ ucfirst(str_replace('_', ' ', (string) $detail->boarding_status)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="portal-surface-muted">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm text-slate-500">Akses pemindaian</p>
                        <p class="mt-1 font-semibold text-slate-800">{{ $hasBoardingPass ? 'QR Boarding Pass' : 'QR E-Ticket' }}</p>
                    </div>
                    <a href="{{ $qrRoute }}" target="_blank" class="portal-btn-blue portal-print-hide">Buka QR</a>
                </div>
                <div class="mt-4 flex items-center justify-center rounded-2xl border border-dashed border-orange-200 bg-white/90 p-6">
                    <img src="{{ $qrRoute }}" alt="QR {{ $detail->passenger?->full_name }}" class="h-44 w-44 object-contain sm:h-48 sm:w-48">
                </div>
            </div>

            <div class="grid gap-3">
                <a href="{{ route('tickets.pdf', $ticket) }}" class="portal-btn-gold portal-print-hide w-full justify-center">Unduh PDF</a>
                @if ($hasBoardingPass)
                    <a href="{{ route('my-bookings.checkin.pdf', [$booking, $detail]) }}" class="portal-btn-blue portal-print-hide w-full justify-center">Boarding Pass PDF</a>
                    <a href="{{ route('my-bookings.checkin.qr', [$booking, $detail]) }}" target="_blank" class="portal-btn-blue portal-print-hide w-full justify-center">Boarding Pass QR</a>
                @elseif (in_array($booking->status, ['confirmed', 'completed'], true))
                    <a href="{{ route('my-bookings.checkin.index', $booking) }}" class="portal-btn-blue portal-print-hide w-full justify-center">Buka Check-In</a>
                @endif
                @if ($showPrintButton)
                    <button type="button" onclick="window.print()" class="portal-btn-blue portal-print-hide w-full justify-center">Cetak</button>
                @endif
                @if (($showSingleLink ?? false) === true)
                    <a href="{{ route('tickets.show', $ticket) }}" class="portal-btn-blue portal-print-hide w-full justify-center">Buka Tiket Ini</a>
                @endif
            </div>
        </div>
    </div>
</article>
