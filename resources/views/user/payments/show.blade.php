@extends('layouts.portal')

@section('title', 'Cakrawala | Payment Detail')
@section('active', 'bookings')

@section('content')
    <section class="space-y-6">
        <article class="payment-detail-hero">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
                <div>
                    <p class="booking-shell-kicker">Payment detail</p>
                    <h1 class="booking-shell-title">{{ $paymentLabel }}</h1>
                    <p class="booking-shell-copy">Booking {{ $payment->booking->booking_code }} dengan rekam jejak submission dan status verifikasi yang lebih mudah dibaca.</p>
                </div>
                <div class="payment-detail-status">
                    @if ($payment->payment_status === 'paid')
                        <span class="portal-status-confirmed">Paid</span>
                    @elseif ($payment->payment_status === 'pending')
                        <span class="portal-status-pending">Pending Verification</span>
                    @elseif ($payment->payment_status === 'failed')
                        <span class="portal-status-cancelled">Failed</span>
                    @else
                        <span class="portal-status-default">{{ ucfirst($payment->payment_status) }}</span>
                    @endif
                    <div class="payment-detail-status-card">
                        <span>Amount</span>
                        <strong>Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-[1.08fr_.92fr]">
            <article class="portal-card">
                <p class="portal-kicker">Submission data</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Payment submission detail</h2>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Nama Pembayar</p>
                        <p class="font-semibold text-slate-800">{{ $payment->payer_name ?: '-' }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Nomor Telepon</p>
                        <p class="font-semibold text-slate-800">{{ $payment->payer_phone ?: '-' }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Bank Pengirim</p>
                        <p class="font-semibold text-slate-800">{{ $payment->payer_bank_name ?: '-' }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Nomor Rekening Pengirim</p>
                        <p class="font-semibold text-slate-800">{{ $payment->payer_bank_account_number ?: '-' }}</p>
                    </div>
                    <div class="portal-card-soft md:col-span-2">
                        <p class="text-sm text-slate-500">Catatan</p>
                        <p class="font-semibold text-slate-800">{{ $payment->payment_notes ?: '-' }}</p>
                    </div>
                    <div class="portal-card-soft md:col-span-2">
                        <p class="text-sm text-slate-500">Bukti Pembayaran</p>
                        @if ($payment->proof_file)
                            <a href="{{ asset('storage/'.$payment->proof_file) }}" target="_blank" class="portal-anchor-link">Open uploaded proof</a>
                        @else
                            <p class="font-semibold text-slate-800">-</p>
                        @endif
                    </div>
                </div>
            </article>

            <article class="portal-card">
                <p class="portal-kicker">Verification trail</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Status snapshot</h2>

                <div class="mt-5 space-y-3">
                    <div class="payment-trail-item">
                        <span>Transaction code</span>
                        <strong>{{ $payment->transaction_code ?: '-' }}</strong>
                    </div>
                    <div class="payment-trail-item">
                        <span>Booking status</span>
                        <strong>{{ ucfirst($payment->booking->status) }}</strong>
                    </div>
                    <div class="payment-trail-item">
                        <span>Submitted at</span>
                        <strong>{{ $payment->submitted_at?->format('d M Y H:i:s') ?: '-' }}</strong>
                    </div>
                    <div class="payment-trail-item">
                        <span>Paid at</span>
                        <strong>{{ $payment->paid_at?->format('d M Y H:i:s') ?: '-' }}</strong>
                    </div>
                    <div class="payment-trail-item">
                        <span>Route</span>
                        <strong>{{ $payment->booking->flight->departureAirport->code }} to {{ $payment->booking->flight->arrivalAirport->code }}</strong>
                    </div>
                </div>
            </article>
        </div>

        <article class="portal-card">
            <div class="flex flex-wrap items-center gap-3">
                @if ($qrisUrl)
                    <a href="{{ $qrisUrl }}" class="portal-btn-gold">Open QRIS</a>
                @endif
                @if ($payment->payment_status !== 'paid' && $payment->booking->status === 'pending')
                    <a href="{{ route('payments.create', ['booking' => $payment->booking->id]) }}" class="portal-btn-blue">Edit Payment Submission</a>
                @endif
                <a href="{{ route('my-bookings.show', $payment->booking) }}" class="portal-btn-blue">Back to Booking</a>
            </div>
        </article>
    </section>
@endsection
