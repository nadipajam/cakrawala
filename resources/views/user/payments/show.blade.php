@extends('layouts.portal')

@section('title', 'Cakrawala | Detail Pembayaran')
@section('active', 'bookings')

@section('content')
    @php($isMidtransSnap = $payment->payment_method === 'midtrans_snap')

    <section class="space-y-6">
        <article class="payment-detail-hero">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
                <div>
                    <p class="booking-shell-kicker">Detail pembayaran</p>
                    <h1 class="booking-shell-title">{{ $paymentLabel }}</h1>
                    <p class="booking-shell-copy">Booking {{ $payment->booking->booking_code }} dengan rekam jejak pengajuan dan status verifikasi yang mudah dibaca.</p>
                </div>
                <div class="payment-detail-status">
                    @if ($payment->payment_status === 'paid')
                        <span class="portal-status-confirmed">Lunas</span>
                    @elseif ($payment->payment_status === 'pending')
                        <span class="portal-status-pending">Menunggu Verifikasi</span>
                    @elseif ($payment->payment_status === 'failed')
                        <span class="portal-status-cancelled">Gagal</span>
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

        <div class="{{ $isMidtransSnap ? 'grid gap-6' : 'grid gap-6 xl:grid-cols-[1.08fr_.92fr]' }}">
            @if (! $isMidtransSnap)
                <article class="portal-card">
                    <p class="portal-kicker">Data pengajuan</p>
                    <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Rincian Pengajuan Pembayaran</h2>

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
                                <a href="{{ route('payments.proof', $payment) }}" target="_blank" class="portal-anchor-link">Buka bukti yang diunggah</a>
                            @else
                                <p class="font-semibold text-slate-800">-</p>
                            @endif
                        </div>
                    </div>
                </article>
            @endif

            <article class="portal-card">
                <p class="portal-kicker">Riwayat verifikasi</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Ringkasan Status</h2>

                <div class="mt-5 space-y-3">
                    <div class="payment-trail-item">
                        <span>Kode transaksi</span>
                        <strong>{{ $payment->transaction_code ?: '-' }}</strong>
                    </div>
                    @if ($payment->midtrans_order_id)
                        <div class="payment-trail-item">
                            <span>Midtrans Order ID</span>
                            <strong>{{ $payment->midtrans_order_id }}</strong>
                        </div>
                    @endif
                    <div class="payment-trail-item">
                        <span>Status booking</span>
                        <strong>{{ ucfirst($payment->booking->status) }}</strong>
                    </div>
                    <div class="payment-trail-item">
                        <span>Diajukan pada</span>
                        <strong>{{ $payment->submitted_at?->format('d M Y H:i:s') ?: '-' }}</strong>
                    </div>
                    <div class="payment-trail-item">
                        <span>Dibayar pada</span>
                        <strong>{{ $payment->paid_at?->format('d M Y H:i:s') ?: '-' }}</strong>
                    </div>
                    <div class="payment-trail-item">
                        <span>Route</span>
                        <strong>{{ $payment->booking->flight->departureAirport->code }} ke {{ $payment->booking->flight->arrivalAirport->code }}</strong>
                    </div>
                </div>
            </article>
        </div>

        <article class="portal-card">
            <div class="flex flex-wrap items-center gap-3">
                @if ($qrisUrl)
                    <a href="{{ $qrisUrl }}" class="portal-btn-gold">Buka QRIS</a>
                @endif
                @if ($midtransUrl)
                    <a href="{{ $midtransUrl }}" class="portal-btn-gold" target="_blank" rel="noopener">Bayar Sekarang (Midtrans Snap)</a>
                @endif
                @if ($payment->payment_status !== 'paid' && $payment->booking->status === 'pending')
                    <a href="{{ route('payments.create', ['booking' => $payment->booking->id]) }}" class="portal-btn-blue">Ubah Pengajuan Pembayaran</a>
                @endif
                <a href="{{ route('my-bookings.show', $payment->booking) }}" class="portal-btn-blue">Kembali ke Booking</a>
            </div>
        </article>
    </section>
@endsection
