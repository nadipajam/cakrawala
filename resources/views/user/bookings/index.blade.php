@extends('layouts.portal')

@section('title', 'Cakrawala | My Bookings')
@section('active', 'bookings')

@section('content')
    <section class="space-y-6">
        <article class="portal-card">
            <div class="portal-section-head">
                <div>
                    <p class="portal-kicker">Booking ledger</p>
                    <h1 class="portal-section-title">My Bookings</h1>
                    <p class="portal-section-copy">Semua booking aktif, pending, dan selesai disusun seperti board kerja agar mudah dipantau dan ditindaklanjuti.</p>
                </div>
                <a href="{{ route('flights.index') }}" class="portal-btn-gold">Search New Flight</a>
            </div>
        </article>

        <div class="space-y-4">
            @forelse ($bookings as $booking)
                <article class="portal-route-card">
                    <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_240px]">
                        <div class="space-y-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="portal-kicker">{{ $booking->flight->airline->name }}</p>
                                    <h2 class="mt-2 text-2xl font-bold text-slate-800">{{ $booking->booking_code }}</h2>
                                </div>
                                <div>
                                    @if ($booking->status === 'pending')
                                        <span class="portal-status-pending">Pending</span>
                                    @elseif ($booking->status === 'confirmed')
                                        <span class="portal-status-confirmed">Confirmed</span>
                                    @elseif ($booking->status === 'cancelled')
                                        <span class="portal-status-cancelled">Cancelled</span>
                                    @else
                                        <span class="portal-status-default">{{ ucfirst($booking->status) }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid gap-3 md:grid-cols-3">
                                <div class="portal-card-soft">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Route</p>
                                    <p class="mt-2 font-semibold text-slate-800">{{ $booking->flight->departureAirport->code }} &rarr; {{ $booking->flight->arrivalAirport->code }}</p>
                                </div>
                                <div class="portal-card-soft">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Departure</p>
                                    <p class="mt-2 font-semibold text-slate-800">{{ $booking->flight->departure_time->format('d M Y H:i') }}</p>
                                </div>
                                <div class="portal-card-soft">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Passengers</p>
                                    <p class="mt-2 font-semibold text-slate-800">{{ $booking->details->count() }} traveler{{ $booking->details->count() > 1 ? 's' : '' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="portal-surface-muted flex flex-col justify-between gap-4">
                            <div>
                                <p class="text-sm text-slate-500">Total</p>
                                <p class="mt-2 text-3xl font-bold text-[#0f3f78]">Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</p>
                                @if ($booking->status === 'pending')
                                    <p class="mt-2 text-xs font-medium text-amber-700">Bayar sebelum {{ $booking->expired_at?->format('d M Y H:i:s') ?: '-' }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('my-bookings.show', $booking) }}" class="portal-btn-blue w-full justify-center">Open Detail</a>
                                @if ($booking->status === 'pending')
                                    <a href="{{ route('payments.create', ['booking' => $booking->id]) }}" class="portal-btn-gold w-full justify-center">
                                        {{ $booking->payments->sortByDesc('created_at')->first()?->submitted_at ? 'Update Payment' : 'Pay Now' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="portal-card text-center text-slate-600">No booking yet.</div>
            @endforelse
        </div>

        @if ($bookings->hasPages())
            <div class="portal-card">
                {{ $bookings->links() }}
            </div>
        @endif
    </section>
@endsection
