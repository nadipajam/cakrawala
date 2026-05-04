@extends('layouts.portal')

@section('title', 'Cakrawala | Booking Detail')
@section('active', 'bookings')

@section('content')
    @php($ticketCount = $booking->details->filter(fn ($detail) => $detail->ticket !== null)->count())
    @php($activeAddonCount = $booking->addons->whereIn('status', ['selected', 'paid'])->count())
    @php($openChangeRequestCount = $booking->changeRequests->whereIn('status', ['submitted', 'in_review', 'approved'])->count())
    @php($checkinCompletedCount = $booking->details->whereIn('boarding_status', ['checked_in', 'boarded'])->count())

    <section class="space-y-6">
        <article class="booking-control-hero">
            <div class="grid gap-6 xl:grid-cols-[1.12fr_.88fr]">
                <div class="min-w-0">
                    <p class="booking-shell-kicker">{{ $booking->flight->airline->name }}</p>
                    <h1 class="booking-shell-title">{{ $booking->booking_code }}</h1>
                    <p class="booking-shell-copy">
                        {{ $booking->flight->departureAirport->code }} ke {{ $booking->flight->arrivalAirport->code }} |
                        {{ $booking->flight->departure_time->format('d M Y H:i') }}
                    </p>
                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        @if ($booking->status === 'pending')
                            <span class="portal-status-pending">Pending</span>
                        @elseif ($booking->status === 'confirmed')
                            <span class="portal-status-confirmed">Confirmed</span>
                        @elseif ($booking->status === 'cancelled')
                            <span class="portal-status-cancelled">Cancelled</span>
                        @else
                            <span class="portal-status-default">{{ ucfirst($booking->status) }}</span>
                        @endif

                        @if ($booking->status === 'pending')
                            <span class="booking-control-deadline">Deadline {{ $booking->expired_at?->format('d M Y H:i:s') ?: '-' }}</span>
                        @endif
                    </div>
                </div>

                <div class="booking-control-highlights">
                    <div class="booking-control-highlight">
                        <span>Penumpang</span>
                        <strong>{{ $booking->details->count() }}</strong>
                    </div>
                    <div class="booking-control-highlight">
                        <span>Check-in selesai</span>
                        <strong>{{ $checkinCompletedCount }}</strong>
                    </div>
                    <div class="booking-control-highlight">
                        <span>Add-on aktif</span>
                        <strong>{{ $activeAddonCount }}</strong>
                    </div>
                    <div class="booking-control-highlight">
                        <span>Permintaan aktif</span>
                        <strong>{{ $openChangeRequestCount }}</strong>
                    </div>
                </div>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="space-y-6">
                <article class="portal-card">
                    <div class="portal-section-head">
                        <div>
                            <p class="portal-kicker">Data penumpang</p>
                            <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Penumpang dan Kursi</h2>
                        </div>
                        @if ($ticketCount > 0)
                            <span class="portal-inline-note">{{ $ticketCount }} ticket{{ $ticketCount > 1 ? 's' : '' }}</span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($booking->details as $detail)
                            <div class="booking-detail-passenger-card">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $detail->passenger?->full_name }}</p>
                                    <p class="text-sm text-slate-600">Seat {{ $detail->seat?->seat_number }} | {{ ucfirst($detail->seat?->class ?? '-') }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Status boarding:
                                        <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', (string) $detail->boarding_status)) }}</span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-[#c2410c]">Rp{{ number_format((float) $detail->price, 0, ',', '.') }}</p>
                                    @if ($detail->ticket)
                                        <a href="{{ route('tickets.show', $detail->ticket) }}" class="mt-2 inline-flex text-sm font-semibold text-[#c2410c] underline underline-offset-2">Buka tiket</a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="portal-card-soft text-slate-500">No passenger details.</p>
                        @endforelse
                    </div>
                </article>

                @if ($booking->addons->isNotEmpty())
                    <article class="portal-card">
                            <p class="portal-kicker">Layanan tambahan</p>
                        <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Selected add-ons</h2>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach ($booking->addons->sortByDesc('created_at') as $addon)
                                <div class="portal-card-soft">
                                    <p class="font-semibold text-slate-800">{{ $addon->addon_name }}</p>
                                    <p class="text-sm text-slate-600">{{ ucfirst($addon->addon_type) }} | Qty {{ $addon->quantity }}</p>
                                    @if ($addon->bookingDetail?->passenger)
                                        <p class="mt-1 text-xs text-slate-500">Passenger: {{ $addon->bookingDetail->passenger->full_name }}</p>
                                    @endif
                                    <div class="mt-2 flex items-center justify-between gap-2">
                                        <p class="font-semibold text-[#c2410c]">Rp{{ number_format((float) $addon->total_price, 0, ',', '.') }}</p>
                                        @include('admin.partials.status-badge', ['status' => $addon->status])
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endif
            </div>

            <aside class="booking-control-rail">
                <div class="booking-control-rail-card">
                    <p class="portal-kicker">Booking actions</p>
                    <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Next move</h2>

                    <div class="mt-5 space-y-3">
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Latest payment</p>
                            @if ($latestPayment)
                                <p class="font-semibold text-slate-800">{{ \App\Support\PaymentMethodCatalog::label($latestPayment->payment_method) }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ ucfirst($latestPayment->payment_status) }} | Rp{{ number_format((float) $latestPayment->amount, 0, ',', '.') }}</p>
                                @if ($latestPayment->payment_method === 'midtrans_snap')
                                    <p class="mt-1 text-xs text-slate-500">Midtrans order: {{ $latestPayment->midtrans_order_id ?: '-' }}</p>
                                @endif
                            @else
                                <p class="font-semibold text-slate-800">Belum ada data pembayaran.</p>
                            @endif
                        </div>
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Booking total</p>
                            <p class="text-3xl font-bold text-[#c2410c]">Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mt-5 flex flex-col gap-2">
                        @if ($booking->status === 'pending')
                            @if ($latestPayment?->payment_method === 'midtrans_snap' && $latestPayment->payment_status === 'pending' && filled($latestPayment->midtrans_redirect_url))
                                <a href="{{ $latestPayment->midtrans_redirect_url }}" target="_blank" rel="noopener" class="portal-btn-gold w-full justify-center">
                                    Lanjutkan Midtrans
                                </a>
                                <a href="{{ route('payments.show', $latestPayment) }}" class="portal-btn-blue w-full justify-center">Detail Transaksi</a>
                                <form method="POST" action="{{ route('payments.midtrans.refresh', $latestPayment) }}">
                                    @csrf
                                    <button type="submit" class="portal-btn-blue w-full justify-center">Cek Status Midtrans</button>
                                </form>
                                <a href="{{ route('payments.create', ['booking' => $booking->id]) }}" class="portal-btn-blue w-full justify-center">
                                    Buat Sesi Baru
                                </a>
                            @else
                                <a href="{{ route('payments.create', ['booking' => $booking->id]) }}" class="portal-btn-gold w-full justify-center">
                                    {{ $latestPayment?->submitted_at ? 'Perbarui Pengajuan Pembayaran' : 'Bayar Sekarang' }}
                                </a>
                                @if ($latestPayment?->submitted_at)
                                    <a href="{{ route('payments.show', $latestPayment) }}" class="portal-btn-blue w-full justify-center">Detail Transaksi</a>
                                @endif
                                @if ($latestPayment?->payment_method === 'qris')
                                    <a href="{{ route('payments.qris.show', $latestPayment) }}" class="portal-btn-blue w-full justify-center">Buka QRIS</a>
                                @endif
                            @endif
                        @endif

                        <a href="{{ route('my-bookings.addons.index', $booking) }}" class="portal-btn-blue w-full justify-center">Kelola Add-On</a>
                        <a href="{{ route('my-bookings.change-requests.index', ['booking' => $booking->id]) }}" class="portal-btn-blue w-full justify-center">Refund / Permintaan Perubahan</a>

                        @if (in_array($booking->status, ['confirmed', 'completed'], true))
                            <a href="{{ route('my-bookings.checkin.index', $booking) }}" class="portal-btn-blue w-full justify-center">Online Check-In</a>
                        @endif

                        @if ($ticketCount > 0)
                            <a href="{{ route('my-bookings.tickets', $booking) }}" class="portal-btn-blue w-full justify-center">
                                {{ $ticketCount > 1 ? 'Buka Semua Tiket' : 'Buka Tiket' }}
                            </a>
                            @if ($ticketCount > 1)
                                <a href="{{ route('my-bookings.tickets.download-all', $booking) }}" class="portal-btn-blue w-full justify-center">Unduh Semua PDF</a>
                            @endif
                        @endif

                        @if ($booking->status === 'pending')
                            <form method="POST" action="{{ route('my-bookings.cancel', $booking) }}">
                                @csrf
                                <button type="submit" class="portal-btn-blue w-full justify-center">Batalkan Booking</button>
                            </form>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </section>
@endsection
