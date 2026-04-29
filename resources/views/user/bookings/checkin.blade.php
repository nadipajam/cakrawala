@extends('layouts.portal')

@section('title', 'Cakrawala | Online Check-In')
@section('active', 'bookings')

@section('content')
    <section class="space-y-6">
        <article class="portal-card">
            <div class="portal-section-head">
                <div class="min-w-0">
                    <p class="portal-kicker">Check-in desk</p>
                    <h1 class="portal-section-title">{{ $booking->booking_code }}</h1>
                    <p class="portal-section-copy">
                        {{ $booking->flight->airline->name }} •
                        {{ $booking->flight->departureAirport->code }} &rarr; {{ $booking->flight->arrivalAirport->code }} •
                        {{ $booking->flight->departure_time->format('d M Y H:i') }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('my-bookings.show', $booking) }}" class="portal-btn-blue">Back to Booking</a>
                    <a href="{{ route('my-bookings.tickets', $booking) }}" class="portal-btn-blue">Open Tickets</a>
                </div>
            </div>
        </article>

        @if (! in_array($booking->status, ['confirmed', 'completed'], true))
            <article class="portal-card">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-700">
                    Check-in belum tersedia. Booking harus berstatus confirmed terlebih dahulu.
                </div>
            </article>
        @endif

        <article class="portal-card">
            <p class="portal-kicker">Manifest</p>
            <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Passenger Check-In Manifest</h2>
            <p class="portal-section-copy">Setiap passenger memiliki boarding pass dan QR sendiri. Jika passenger lebih dari satu, lakukan check-in per passenger.</p>

            <div class="mt-5 space-y-4">
                @forelse ($booking->details as $detail)
                    @php($availability = $checkInAvailability[$detail->id] ?? ['can_check_in' => false, 'reason' => null])
                    <article class="portal-route-card">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-lg font-semibold text-slate-800">{{ $detail->passenger?->full_name }}</p>
                                <p class="text-sm text-slate-600">Seat {{ $detail->seat?->seat_number }} • {{ ucfirst($detail->seat?->class ?? '-') }}</p>
                                <p class="text-sm text-slate-600">Ticket {{ $detail->ticket_number ?: '-' }}</p>
                                <p class="mt-1 text-sm text-slate-500">
                                    Status:
                                    @include('admin.partials.status-badge', ['status' => $detail->boarding_status])
                                </p>
                                @if ($detail->checked_in_at)
                                    <p class="mt-1 text-xs text-slate-500">Checked in at {{ $detail->checked_in_at->format('d M Y H:i') }}</p>
                                @endif
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                @if ($availability['can_check_in'])
                                    <form method="POST" action="{{ route('my-bookings.checkin.store', [$booking, $detail]) }}">
                                        @csrf
                                        <button type="submit" class="portal-btn-gold">Check-In Now</button>
                                    </form>
                                @elseif ($detail->boarding_status === 'not_checked_in')
                                    <button type="button" class="cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-4 py-2.5 text-sm font-semibold text-slate-400" disabled>
                                        Check-In Unavailable
                                    </button>
                                @endif

                                @if (in_array($detail->boarding_status, ['checked_in', 'boarded'], true))
                                    <a href="{{ route('my-bookings.checkin.pdf', [$booking, $detail]) }}" class="portal-btn-blue">Boarding Pass PDF</a>
                                    <a href="{{ route('my-bookings.checkin.qr', [$booking, $detail]) }}" target="_blank" class="portal-btn-blue">Open QR</a>
                                @endif
                            </div>
                        </div>

                        @if (! $availability['can_check_in'] && $detail->boarding_status === 'not_checked_in' && $availability['reason'])
                            <p class="mt-3 text-sm text-amber-700">{{ $availability['reason'] }}</p>
                        @endif

                        @if (in_array($detail->boarding_status, ['checked_in', 'boarded'], true))
                            <div class="mt-4 grid gap-4 lg:grid-cols-[220px_1fr]">
                                <div class="rounded-xl border border-dashed border-orange-200 bg-white p-3 text-center">
                                    <img
                                        src="{{ route('my-bookings.checkin.qr', [$booking, $detail]) }}"
                                        alt="Boarding QR {{ $detail->passenger?->full_name }}"
                                        class="mx-auto h-44 w-44 object-contain"
                                    >
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div class="portal-card-soft">
                                        <p class="text-xs text-slate-500">Check-In Reference</p>
                                        <p class="font-semibold text-slate-800">{{ $detail->checkin_reference ?: '-' }}</p>
                                    </div>
                                    <div class="portal-card-soft">
                                        <p class="text-xs text-slate-500">Boarding Group</p>
                                        <p class="font-semibold text-slate-800">{{ $detail->boarding_group ?: '-' }}</p>
                                    </div>
                                    <div class="portal-card-soft">
                                        <p class="text-xs text-slate-500">Gate</p>
                                        <p class="font-semibold text-slate-800">{{ $detail->gate_number ?: '-' }}</p>
                                    </div>
                                    <div class="portal-card-soft">
                                        <p class="text-xs text-slate-500">Boarding Time</p>
                                        <p class="font-semibold text-slate-800">{{ $booking->flight->departure_time->format('d M Y H:i') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </article>
                @empty
                    <p class="portal-card-soft text-slate-500">Tidak ada detail passenger pada booking ini.</p>
                @endforelse
            </div>
        </article>
    </section>
@endsection
