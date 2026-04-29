@extends('layouts.portal')

@section('title', 'Cakrawala | QRIS Payment')
@section('active', 'bookings')

@section('content')
    <section x-data="qrisCountdown(@js(optional($payment->booking->expired_at)?->toIso8601String()))" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <article class="portal-card">
            <div class="portal-section-head">
                <div>
                    <p class="portal-kicker">Instant payment</p>
                    <h1 class="portal-section-title">{{ $paymentLabel }}</h1>
                    <p class="portal-section-copy">Scan QR berikut dalam 5 menit. Jika lewat, booking akan expired dan kursi dilepas ke user lain.</p>
                </div>
                <span class="portal-inline-note" x-text="countdown"></span>
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[320px_1fr]">
                <div class="portal-surface-muted text-center">
                    <div class="portal-brand-mark mx-auto h-12 w-12 text-sm">QR</div>
                    <div class="mt-4 flex justify-center">
                        <img src="{{ $qrCode }}" alt="QRIS Demo" class="h-64 w-64 object-contain">
                    </div>
                    <p class="mt-4 text-sm leading-7 text-slate-500">Saat QR berhasil discan, pembayaran akan otomatis masuk status paid dan tiket diterbitkan.</p>
                </div>

                <div class="space-y-4">
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Amount</p>
                        <p class="text-3xl font-bold text-[#0f3f78]">Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Booking</p>
                        <p class="font-semibold text-slate-800">{{ $payment->booking->booking_code }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $payment->booking->flight->airline->name }} &middot; {{ $payment->booking->flight->departureAirport->code }} &rarr; {{ $payment->booking->flight->arrivalAirport->code }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Batas Pembayaran</p>
                        <p class="font-semibold text-slate-800">{{ optional($payment->booking->expired_at)->format('d M Y H:i:s') ?: '-' }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Simulated Scan URL</p>
                        <p class="mt-1 break-all text-xs text-slate-500">{{ $scanUrl }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ $scanUrl }}" target="_blank" class="portal-btn-gold">Simulate QR Scan</a>
                        <a href="{{ route('payments.show', $payment) }}" class="portal-btn-blue">Payment Detail</a>
                        <a href="{{ route('my-bookings.show', $payment->booking) }}" class="portal-btn-blue">Back to Booking</a>
                    </div>
                </div>
            </div>
        </article>

        <aside class="portal-side-panel">
            <p class="portal-kicker">Status</p>
            <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Live State</h2>
            <div class="mt-5 space-y-3">
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Payment Status</p>
                    <p class="font-semibold text-slate-800">{{ ucfirst($payment->payment_status) }}</p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Booking Status</p>
                    <p class="font-semibold text-slate-800">{{ ucfirst($payment->booking->status) }}</p>
                </div>
            </div>
        </aside>
    </section>

    <script>
        function qrisCountdown(expiresAt) {
            return {
                expiresAt,
                countdown: '',
                init() {
                    this.updateCountdown();
                    setInterval(() => this.updateCountdown(), 1000);
                },
                updateCountdown() {
                    if (!this.expiresAt) {
                        this.countdown = '';
                        return;
                    }

                    const diff = new Date(this.expiresAt).getTime() - Date.now();

                    if (diff <= 0) {
                        this.countdown = 'Sesi pembayaran telah berakhir. Refresh halaman.';
                        return;
                    }

                    const minutes = Math.floor(diff / 60000);
                    const seconds = Math.floor((diff % 60000) / 1000).toString().padStart(2, '0');
                    this.countdown = `Sisa waktu ${minutes}:${seconds}`;
                },
            };
        }

        setTimeout(() => window.location.reload(), 10000);
    </script>
@endsection
