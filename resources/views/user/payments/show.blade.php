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
                    <p class="booking-shell-copy">Status pembayaran, route booking, dan aksi lanjutan dipusatkan di sini supaya kamu tidak perlu pindah-pindah halaman.</p>
                </div>
                <div class="payment-detail-status">
                    @if ($payment->payment_status === 'paid')
                        <span class="portal-status-confirmed">Lunas</span>
                    @elseif ($payment->payment_status === 'pending')
                        <span class="portal-status-pending">Menunggu Pembayaran</span>
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

        <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
            <article class="portal-card">
                <p class="portal-kicker">Ringkasan status</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Status transaksi saat ini</h2>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Booking code</p>
                        <p class="font-semibold text-slate-800">{{ $payment->booking->booking_code }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Status booking</p>
                        <p class="font-semibold text-slate-800">{{ ucfirst($payment->booking->status) }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Diajukan pada</p>
                        <p class="font-semibold text-slate-800">{{ $payment->submitted_at?->format('d M Y H:i:s') ?: '-' }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Dibayar pada</p>
                        <p class="font-semibold text-slate-800">{{ $payment->paid_at?->format('d M Y H:i:s') ?: '-' }}</p>
                    </div>
                    <div class="portal-card-soft md:col-span-2">
                        <p class="text-sm text-slate-500">Rute</p>
                        <p class="font-semibold text-slate-800">{{ $payment->booking->flight->departureAirport->code }} ke {{ $payment->booking->flight->arrivalAirport->code }}</p>
                    </div>
                </div>

                @if ($isMidtransSnap)
                    <div class="mt-6 border-t border-slate-200 pt-6">
                        <p class="portal-kicker">Data Midtrans</p>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div class="portal-card-soft">
                                <p class="text-sm text-slate-500">Order ID</p>
                                <p class="font-semibold text-slate-800">{{ $payment->midtrans_order_id ?: '-' }}</p>
                            </div>
                            <div class="portal-card-soft">
                                <p class="text-sm text-slate-500">Status code</p>
                                <p class="font-semibold text-slate-800">{{ $payment->midtrans_status_code ?: '-' }}</p>
                            </div>
                            <div class="portal-card-soft">
                                <p class="text-sm text-slate-500">Tipe pembayaran</p>
                                <p class="font-semibold text-slate-800">{{ $payment->midtrans_payment_type ?: '-' }}</p>
                            </div>
                            <div class="portal-card-soft">
                                <p class="text-sm text-slate-500">Transaction ID</p>
                                <p class="font-semibold text-slate-800">{{ $payment->midtrans_transaction_id ?: '-' }}</p>
                            </div>
                        </div>

                        @if ($midtransLocalMode)
                            <div class="payment-instruction-panel mt-5">
                                <p class="portal-kicker">Mode lokal</p>
                                <div class="mt-3 space-y-2 text-sm leading-7 text-slate-600">
                                    <p>Webhook publik tidak dipakai. Status biasanya ikut terbarui saat kamu kembali dari halaman Midtrans.</p>
                                    <p>Kalau masih pending, klik tombol cek status di bawah ini.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </article>

            <article class="portal-card">
                <p class="portal-kicker">Aksi cepat</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Langkah berikutnya</h2>

                <div class="mt-5 space-y-3">
                    @if ($qrisUrl)
                        <a href="{{ $qrisUrl }}" class="portal-btn-gold w-full justify-center">Buka QRIS</a>
                    @endif

                    @if ($midtransUrl)
                        <a href="{{ $midtransUrl }}" class="portal-btn-gold w-full justify-center" target="_blank" rel="noopener">Lanjutkan Pembayaran Midtrans</a>
                    @endif

                    @if ($isMidtransSnap && $payment->payment_status === 'pending')
                        <form method="POST" action="{{ route('payments.midtrans.refresh', $payment) }}">
                            @csrf
                            <button type="submit" class="portal-btn-blue w-full justify-center">Cek Status Midtrans</button>
                        </form>
                    @endif

                    @if ($isMidtransSnap && $midtransSimulatorEnabled && $payment->payment_status === 'pending')
                        <form method="POST" action="{{ route('payments.midtrans.simulate', $payment) }}">
                            @csrf
                            <button type="submit" class="portal-btn-blue w-full justify-center">Simulasi Lunas Lokal</button>
                        </form>
                    @endif

                    @if ($payment->payment_status !== 'paid' && $payment->booking->status === 'pending')
                        <a href="{{ route('payments.create', ['booking' => $payment->booking->id]) }}" class="portal-btn-blue w-full justify-center">Buat Sesi Pembayaran Baru</a>
                    @endif

                    <a href="{{ route('my-bookings.show', $payment->booking) }}" class="portal-btn-blue w-full justify-center">Kembali ke Booking</a>
                </div>
            </article>
        </div>
    </section>
@endsection
