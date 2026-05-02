@extends('layouts.portal')

@section('title', 'Cakrawala | Booking Saya')
@section('active', 'bookings')

@section('content')
    @php
        $statusSummary = [
            'pending' => $bookings->getCollection()->where('status', 'pending')->count(),
            'confirmed' => $bookings->getCollection()->where('status', 'confirmed')->count(),
            'completed' => $bookings->getCollection()->where('status', 'completed')->count(),
            'cancelled' => $bookings->getCollection()->where('status', 'cancelled')->count(),
        ];
    @endphp

    <section class="space-y-6">
        <article class="booking-board-hero">
            <div class="grid gap-6 xl:grid-cols-[1.18fr_.82fr]">
                <div>
                    <p class="booking-shell-kicker">Daftar booking</p>
                    <h1 class="booking-shell-title">Seluruh booking Anda disusun agar mudah dipantau dan ditindaklanjuti.</h1>
                    <p class="booking-shell-copy">Booking menunggu pembayaran, perjalanan terkonfirmasi, hingga riwayat selesai ditampilkan dalam satu tampilan ringkas.</p>
                </div>

                <div class="booking-board-hero-side">
                    <div class="booking-board-metric">
                        <span>Pending</span>
                        <strong>{{ $statusSummary['pending'] }}</strong>
                    </div>
                    <div class="booking-board-metric">
                        <span>Confirmed</span>
                        <strong>{{ $statusSummary['confirmed'] }}</strong>
                    </div>
                    <div class="booking-board-metric">
                        <span>Completed</span>
                        <strong>{{ $statusSummary['completed'] }}</strong>
                    </div>
                    <div class="booking-board-metric">
                        <span>Cancelled</span>
                        <strong>{{ $statusSummary['cancelled'] }}</strong>
                    </div>
                </div>
            </div>
            <div class="mt-5">
                <a href="{{ route('flights.index') }}" class="portal-btn-gold">Cari Penerbangan Baru</a>
            </div>
        </article>

        <div class="space-y-4">
            @forelse ($bookings as $booking)
                <article class="booking-board-card">
                    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.1fr)_minmax(0,.9fr)_250px]">
                        <div class="space-y-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="portal-kicker">{{ $booking->flight->airline->name }}</p>
                                    <h2 class="mt-2 text-2xl font-bold text-slate-800">{{ $booking->booking_code }}</h2>
                                    <p class="mt-2 text-sm text-slate-500">{{ $booking->flight->flight_number }} | {{ $booking->flight->departure_time->format('d M Y H:i') }}</p>
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

                            <div class="grid gap-3 md:grid-cols-2">
                                <div class="portal-card-soft">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Route</p>
                                    <p class="mt-2 font-semibold text-slate-800">{{ $booking->flight->departureAirport->city }} ({{ $booking->flight->departureAirport->code }})</p>
                                    <p class="text-sm text-slate-500">{{ $booking->flight->arrivalAirport->city }} ({{ $booking->flight->arrivalAirport->code }})</p>
                                </div>
                                <div class="portal-card-soft">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Penumpang</p>
                                    <p class="mt-2 font-semibold text-slate-800">{{ $booking->details->count() }} penumpang</p>
                                    <p class="text-sm text-slate-500">Pilihan kabin tersimpan di detail booking.</p>
                                </div>
                            </div>
                        </div>

                        <div class="booking-board-timeline">
                            <div class="booking-board-timeline-item">
                                <span>Booking dibuat</span>
                                <strong>{{ $booking->created_at?->format('d M Y H:i') ?: '-' }}</strong>
                            </div>
                            <div class="booking-board-timeline-item">
                                <span>Keberangkatan</span>
                                <strong>{{ $booking->flight->departure_time->format('d M Y H:i') }}</strong>
                            </div>
                            <div class="booking-board-timeline-item">
                                <span>Batas pembayaran</span>
                                <strong>{{ $booking->expired_at?->format('d M Y H:i:s') ?: '-' }}</strong>
                            </div>
                        </div>

                        <div class="booking-board-actions">
                            <div>
                                <p class="text-sm text-slate-500">Total</p>
                                <p class="mt-2 text-3xl font-bold text-[#c2410c]">Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</p>
                                @if ($booking->status === 'pending')
                                    <p class="mt-2 text-xs font-medium text-amber-700">Bayar sebelum {{ $booking->expired_at?->format('d M Y H:i:s') ?: '-' }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col gap-2">
                                <a href="{{ route('my-bookings.show', $booking) }}" class="portal-btn-blue w-full justify-center">Lihat Detail</a>
                                @if ($booking->status === 'pending')
                                    <a href="{{ route('payments.create', ['booking' => $booking->id]) }}" class="portal-btn-gold w-full justify-center">
                                        {{ $booking->payments->sortByDesc('created_at')->first()?->submitted_at ? 'Perbarui Pembayaran' : 'Bayar Sekarang' }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="portal-card text-center text-slate-600">Belum ada booking.</div>
            @endforelse
        </div>

        @if ($bookings->hasPages())
            <div class="portal-card">
                {{ $bookings->links() }}
            </div>
        @endif
    </section>
@endsection
