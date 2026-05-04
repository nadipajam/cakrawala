@extends('layouts.portal')

@section('title', 'Cakrawala | Online Check-In')
@section('active', 'bookings')

@section('content')
    <section class="journey-shell">
        <article class="journey-hero">
            <div class="journey-hero-grid">
                <div>
                    <span class="journey-chip">
                        <span class="journey-dot"></span>
                        Layanan Check-In
                    </span>
                    <h1 class="journey-title">{{ $booking->booking_code }}</h1>
                    <p class="journey-copy">
                        Kelola check-in setiap penumpang untuk {{ $booking->flight->airline->name }} dari
                        {{ $booking->flight->departureAirport->code }} ke {{ $booking->flight->arrivalAirport->code }}.
                        Boarding pass dan QR tetap diterbitkan per penumpang.
                    </p>
                    <div class="journey-action-row mt-5">
                        <a href="{{ route('my-bookings.show', $booking) }}" class="portal-btn-blue">Kembali ke Booking</a>
                        <a href="{{ route('my-bookings.tickets', $booking) }}" class="portal-btn-gold">Buka Tiket</a>
                    </div>
                </div>
                <div class="journey-meta-grid">
                    <div class="journey-code">
                        <span class="journey-code-label">Departure Time</span>
                        <span class="journey-code-value">{{ $booking->flight->departure_time->format('d M Y H:i') }}</span>
                    </div>
                    <div class="journey-code">
                        <span class="journey-code-label">Passenger Count</span>
                        <span class="journey-code-value">{{ $booking->details->count() }}</span>
                    </div>
                </div>
            </div>
        </article>

        @if (! in_array($booking->status, ['confirmed', 'completed'], true))
            <article class="journey-rail-card border-amber-200 bg-amber-50 text-amber-800 shadow-none">
                Check-in belum tersedia. Booking harus berstatus terkonfirmasi terlebih dahulu.
            </article>
        @endif

        <div class="journey-grid">
            <div class="journey-manifest">
                @forelse ($booking->details as $detail)
                    @php($availability = $checkInAvailability[$detail->id] ?? ['can_check_in' => false, 'reason' => null])
                    <article class="journey-manifest-card">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-orange-700">Data Penumpang</p>
                                <h2 class="mt-2 text-2xl font-bold text-slate-900">{{ $detail->passenger?->full_name }}</h2>
                                <p class="mt-2 text-sm text-slate-600">
                                    Seat {{ $detail->seat?->seat_number }} | {{ ucfirst($detail->seat?->class ?? '-') }} | Ticket {{ $detail->ticket_number ?: '-' }}
                                </p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if ($availability['can_check_in'])
                                    <form method="POST" action="{{ route('my-bookings.checkin.store', [$booking, $detail]) }}">
                                        @csrf
                                        <button type="submit" class="portal-btn-gold">Check-In Now</button>
                                    </form>
                                @elseif ($detail->boarding_status === 'not_checked_in')
                                    <button type="button" class="cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-400" disabled>
                                        Check-In Belum Tersedia
                                    </button>
                                @endif

                                @if (in_array($detail->boarding_status, ['checked_in', 'boarded'], true))
                                    <a href="{{ route('my-bookings.checkin.pdf', [$booking, $detail]) }}" class="portal-btn-blue">Boarding Pass PDF</a>
                                    <a href="{{ route('my-bookings.checkin.qr', [$booking, $detail]) }}" target="_blank" class="portal-btn-blue">Buka QR</a>
                                @endif
                            </div>
                        </div>

                        <div class="journey-stat-grid mt-5 sm:grid-cols-2 xl:grid-cols-4">
                            <div class="journey-stat">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Boarding Status</p>
                                <div class="mt-2">@include('admin.partials.status-badge', ['status' => $detail->boarding_status])</div>
                            </div>
                            <div class="journey-stat">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Check-In Reference</p>
                                <p class="mt-2 font-semibold text-slate-900">{{ $detail->checkin_reference ?: '-' }}</p>
                            </div>
                            <div class="journey-stat">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Boarding Group</p>
                                <p class="mt-2 font-semibold text-slate-900">{{ $detail->boarding_group ?: '-' }}</p>
                            </div>
                            <div class="journey-stat">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Gate</p>
                                <p class="mt-2 font-semibold text-slate-900">{{ $detail->gate_number ?: '-' }}</p>
                            </div>
                        </div>

                        @if ($detail->checked_in_at)
                            <p class="mt-4 text-xs text-slate-500">Check-in pada {{ $detail->checked_in_at->format('d M Y H:i') }}</p>
                        @endif

                        @if (! $availability['can_check_in'] && $detail->boarding_status === 'not_checked_in' && $availability['reason'])
                            <p class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                                {{ $availability['reason'] }}
                            </p>
                        @endif

                        @if (in_array($detail->boarding_status, ['checked_in', 'boarded'], true))
                            <div class="mt-5 grid gap-4 lg:grid-cols-[220px_minmax(0,1fr)]">
                                <div class="portal-surface-muted text-center">
                                    <img
                                        src="{{ route('my-bookings.checkin.qr', [$booking, $detail]) }}"
                                        alt="Boarding QR {{ $detail->passenger?->full_name }}"
                                        class="mx-auto h-44 w-44 object-contain"
                                    >
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="portal-card-soft">
                                        <p class="text-xs text-slate-500">Flight</p>
                                        <p class="font-semibold text-slate-800">{{ $booking->flight->flight_number }}</p>
                                    </div>
                                    <div class="portal-card-soft">
                                        <p class="text-xs text-slate-500">Boarding Time</p>
                                        <p class="font-semibold text-slate-800">{{ $booking->flight->departure_time->format('d M Y H:i') }}</p>
                                    </div>
                                    <div class="portal-card-soft">
                                        <p class="text-xs text-slate-500">Route</p>
                                        <p class="font-semibold text-slate-800">{{ $booking->flight->departureAirport->code }} -> {{ $booking->flight->arrivalAirport->code }}</p>
                                    </div>
                                    <div class="portal-card-soft">
                                        <p class="text-xs text-slate-500">Airline</p>
                                        <p class="font-semibold text-slate-800">{{ $booking->flight->airline->name }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </article>
                @empty
                    <article class="journey-rail-card text-slate-500">
                        Tidak ada detail penumpang pada booking ini.
                    </article>
                @endforelse
            </div>

            <aside class="journey-rail">
                <article class="journey-rail-card journey-rail-card-dark">
                    <p class="portal-kicker">Panduan layanan</p>
                    <h2 class="mt-2 text-2xl font-bold">Alur Check-In</h2>
                    <div class="mt-4 space-y-3">
                        <div class="journey-guide-step">
                            <p class="journey-guide-step-label">Step 1</p>
                            <p class="journey-guide-step-title">Pastikan booking sudah confirmed.</p>
                            <p class="journey-guide-step-copy">Check-in hanya tersedia untuk booking yang sudah terkonfirmasi atau completed.</p>
                        </div>
                        <div class="journey-guide-step">
                            <p class="journey-guide-step-label">Step 2</p>
                            <p class="journey-guide-step-title">Lakukan check-in satu penumpang per satu penumpang.</p>
                            <p class="journey-guide-step-copy">Setiap passenger punya status boarding, referensi check-in, dan boarding pass masing-masing.</p>
                        </div>
                        <div class="journey-guide-step">
                            <p class="journey-guide-step-label">Step 3</p>
                            <p class="journey-guide-step-title">Unduh boarding pass atau buka QR setelah status berubah.</p>
                            <p class="journey-guide-step-copy">Begitu status checked in, file PDF dan QR boarding akan langsung bisa dipakai.</p>
                        </div>
                    </div>
                </article>
            </aside>
        </div>
    </section>
@endsection
