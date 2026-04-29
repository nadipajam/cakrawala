@extends('layouts.portal')

@section('title', 'Cakrawala | Booking Detail')
@section('active', 'bookings')

@section('content')
    @php($ticketCount = $booking->details->filter(fn ($detail) => $detail->ticket !== null)->count())
    @php($activeAddonCount = $booking->addons->whereIn('status', ['selected', 'paid'])->count())
    @php($openChangeRequestCount = $booking->changeRequests->whereIn('status', ['submitted', 'in_review', 'approved'])->count())
    @php($checkinCompletedCount = $booking->details->whereIn('boarding_status', ['checked_in', 'boarded'])->count())

    <section class="space-y-6">
        <article class="portal-card">
            <div class="portal-section-head">
                <div class="min-w-0">
                    <p class="portal-kicker">{{ $booking->flight->airline->name }}</p>
                    <h1 class="portal-section-title">{{ $booking->booking_code }}</h1>
                    <p class="portal-section-copy">
                        {{ $booking->flight->departureAirport->code }} &rarr; {{ $booking->flight->arrivalAirport->code }}
                        • {{ $booking->flight->departure_time->format('d M Y H:i') }}
                    </p>
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

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="portal-metric-card">
                    <p class="portal-kicker">Passengers</p>
                    <p class="portal-metric-value">{{ $booking->details->count() }}</p>
                </div>
                <div class="portal-metric-card">
                    <p class="portal-kicker">Check-In Done</p>
                    <p class="portal-metric-value">{{ $checkinCompletedCount }}</p>
                </div>
                <div class="portal-metric-card">
                    <p class="portal-kicker">Active Add-Ons</p>
                    <p class="portal-metric-value">{{ $activeAddonCount }}</p>
                </div>
                <div class="portal-metric-card">
                    <p class="portal-kicker">Open Requests</p>
                    <p class="portal-metric-value">{{ $openChangeRequestCount }}</p>
                </div>
            </div>

            @if ($booking->status === 'pending')
                <div class="mt-5 portal-surface-muted">
                    <p class="portal-kicker">Payment deadline</p>
                    <p class="mt-3 text-lg font-semibold text-slate-800">{{ $booking->expired_at?->format('d M Y H:i:s') ?: '-' }}</p>
                    <p class="mt-2 text-sm text-amber-700">Jika pembayaran tidak dikirim dan diselesaikan sebelum waktu ini, booking akan expired dan kursi kembali tersedia.</p>
                </div>
            @endif
        </article>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-6">
                <article class="portal-card">
                    <div class="portal-section-head">
                        <div>
                            <p class="portal-kicker">Passenger manifest</p>
                            <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Passenger & Seat</h2>
                        </div>
                        @if ($ticketCount > 0)
                            <span class="portal-inline-note">{{ $ticketCount }} ticket{{ $ticketCount > 1 ? 's' : '' }}</span>
                        @endif
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse ($booking->details as $detail)
                            <div class="portal-card-soft flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $detail->passenger?->full_name }}</p>
                                    <p class="text-sm text-slate-600">Seat {{ $detail->seat?->seat_number }} • {{ ucfirst($detail->seat?->class ?? '-') }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Boarding:
                                        <span class="font-semibold">{{ ucfirst(str_replace('_', ' ', (string) $detail->boarding_status)) }}</span>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold text-[#0f3f78]">Rp{{ number_format((float) $detail->price, 0, ',', '.') }}</p>
                                    @if ($detail->ticket)
                                        <a href="{{ route('tickets.show', $detail->ticket) }}" class="mt-2 inline-flex text-sm font-semibold text-[#0f3f78] underline underline-offset-2">Open ticket</a>
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
                        <p class="portal-kicker">Ancillary services</p>
                        <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Selected Add-Ons</h2>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach ($booking->addons->sortByDesc('created_at') as $addon)
                                <div class="portal-card-soft">
                                    <p class="font-semibold text-slate-800">{{ $addon->addon_name }}</p>
                                    <p class="text-sm text-slate-600">{{ ucfirst($addon->addon_type) }} • Qty {{ $addon->quantity }}</p>
                                    @if ($addon->bookingDetail?->passenger)
                                        <p class="mt-1 text-xs text-slate-500">Passenger: {{ $addon->bookingDetail->passenger->full_name }}</p>
                                    @endif
                                    <div class="mt-2 flex items-center justify-between gap-2">
                                        <p class="font-semibold text-[#0f3f78]">Rp{{ number_format((float) $addon->total_price, 0, ',', '.') }}</p>
                                        @include('admin.partials.status-badge', ['status' => $addon->status])
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </article>
                @endif
            </div>

            <aside class="portal-side-panel">
                <p class="portal-kicker">Booking actions</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Manage This Booking</h2>

                <div class="mt-5 space-y-3">
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Latest Payment</p>
                        @if ($latestPayment)
                            <p class="font-semibold text-slate-800">{{ \App\Support\PaymentMethodCatalog::label($latestPayment->payment_method) }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ ucfirst($latestPayment->payment_status) }} • Rp{{ number_format((float) $latestPayment->amount, 0, ',', '.') }}</p>
                        @else
                            <p class="font-semibold text-slate-800">No payment data yet.</p>
                        @endif
                    </div>
                </div>

                <div class="mt-5 flex flex-col gap-2">
                    @if ($booking->status === 'pending')
                        <a href="{{ route('payments.create', ['booking' => $booking->id]) }}" class="portal-btn-gold w-full justify-center">
                            {{ $latestPayment?->submitted_at ? 'Update Payment Submission' : 'Pay Now' }}
                        </a>
                        @if ($latestPayment?->submitted_at)
                            <a href="{{ route('payments.show', $latestPayment) }}" class="portal-btn-blue w-full justify-center">Transaction Detail</a>
                        @endif
                        @if ($latestPayment?->payment_method === 'qris')
                            <a href="{{ route('payments.qris.show', $latestPayment) }}" class="portal-btn-blue w-full justify-center">Open QRIS</a>
                        @endif
                    @endif

                    <a href="{{ route('my-bookings.addons.index', $booking) }}" class="portal-btn-blue w-full justify-center">Manage Add-Ons</a>
                    <a href="{{ route('my-bookings.change-requests.index', ['booking' => $booking->id]) }}" class="portal-btn-blue w-full justify-center">Refund / Change Request</a>

                    @if (in_array($booking->status, ['confirmed', 'completed'], true))
                        <a href="{{ route('my-bookings.checkin.index', $booking) }}" class="portal-btn-blue w-full justify-center">Online Check-In</a>
                    @endif

                    @if ($ticketCount > 0)
                        <a href="{{ route('my-bookings.tickets', $booking) }}" class="portal-btn-blue w-full justify-center">
                            {{ $ticketCount > 1 ? 'Open All Tickets' : 'Open Ticket' }}
                        </a>
                        @if ($ticketCount > 1)
                            <a href="{{ route('my-bookings.tickets.download-all', $booking) }}" class="portal-btn-blue w-full justify-center">Download All PDFs</a>
                        @endif
                    @endif

                    @if ($booking->status === 'pending')
                        <form method="POST" action="{{ route('my-bookings.cancel', $booking) }}">
                            @csrf
                            <button type="submit" class="portal-btn-blue w-full justify-center">Cancel Booking</button>
                        </form>
                    @endif
                </div>
            </aside>
        </div>
    </section>
@endsection
