@extends('layouts.portal')

@section('title', 'Cakrawala | Pembayaran QRIS')
@section('active', 'bookings')

@section('content')
    <section x-data="qrisCountdown(@js(optional($payment->booking->expired_at)?->toIso8601String()))" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_370px]">
        <article class="space-y-6">
            <article class="payment-hero-panel">
                <div class="grid gap-6 xl:grid-cols-[1.06fr_.94fr]">
                    <div>
                        <p class="booking-shell-kicker">Pembayaran instan</p>
                        <h1 class="booking-shell-title">{{ $paymentLabel }}</h1>
                        <p class="booking-shell-copy">Scan QR berikut sebelum timer habis. Saat scan sukses, status pembayaran akan berubah otomatis dan booking bisa lanjut ke tahap berikutnya.</p>
                    </div>
                    <div class="payment-hero-meta">
                        <div class="payment-hero-meta-card">
                            <span>Hitung mundur</span>
                            <strong x-text="countdown"></strong>
                        </div>
                        <div class="payment-hero-meta-card">
                            <span>Amount</span>
                            <strong>Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </article>

            <article class="portal-card">
                <div class="grid gap-6 lg:grid-cols-[320px_minmax(0,1fr)]">
                    <div class="qris-scan-stage text-center">
                        <div class="portal-brand-mark mx-auto h-12 w-12 text-sm">QR</div>
                        <div class="mt-4 flex justify-center">
                            <img src="{{ $qrCode }}" alt="QRIS Demo" class="h-64 w-64 object-contain">
                        </div>
                        <p class="mt-4 text-sm leading-7 text-slate-500">Saat QR berhasil dipindai, status pembayaran otomatis menjadi lunas dan tiket dapat diterbitkan.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Booking</p>
                            <p class="font-semibold text-slate-800">{{ $payment->booking->booking_code }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $payment->booking->flight->airline->name }} | {{ $payment->booking->flight->departureAirport->code }} ke {{ $payment->booking->flight->arrivalAirport->code }}</p>
                        </div>
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Batas pembayaran</p>
                            <p class="font-semibold text-slate-800">{{ optional($payment->booking->expired_at)->format('d M Y H:i:s') ?: '-' }}</p>
                        </div>
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Tautan simulasi pindai</p>
                            <p class="mt-1 break-all text-xs text-slate-500">{{ $scanUrl }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <a href="{{ $scanUrl }}" target="_blank" class="portal-btn-gold">Simulasikan Pindai QR</a>
                            <a href="{{ route('payments.show', $payment) }}" class="portal-btn-blue">Detail Pembayaran</a>
                            <a href="{{ route('my-bookings.show', $payment->booking) }}" class="portal-btn-blue">Kembali ke Booking</a>
                        </div>
                    </div>
                </div>
            </article>
        </article>

        <aside class="payment-summary-rail">
            <div class="payment-summary-rail-card">
                <p class="portal-kicker">Status langsung</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">QRIS status</h2>
                <div class="mt-5 space-y-3">
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Status pembayaran</p>
                        <p class="font-semibold text-slate-800">{{ ucfirst($payment->payment_status) }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Status booking</p>
                        <p class="font-semibold text-slate-800">{{ ucfirst($payment->booking->status) }}</p>
                    </div>
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
