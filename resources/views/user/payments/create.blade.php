@extends('layouts.portal')

@section('title', 'Cakrawala | Pembayaran')
@section('active', 'bookings')

@section('content')
    @php($pendingMidtrans = $latestPayment && $latestPayment->payment_method === 'midtrans_snap' && $latestPayment->payment_status === 'pending')

    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <div class="space-y-6">
            <article class="payment-hero-panel">
                <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
                    <div>
                        <p class="booking-shell-kicker">Pembayaran booking</p>
                        <h1 class="booking-shell-title">Bayar booking ini lewat Midtrans Snap.</h1>
                        <p class="booking-shell-copy">Satu halaman ini cukup untuk memulai pembayaran, melanjutkan sesi Snap yang masih aktif, dan mengecek status tanpa setup nginx.</p>
                    </div>

                    <div class="payment-hero-meta">
                        <div class="payment-hero-meta-card">
                            <span>Booking</span>
                            <strong>{{ $booking->booking_code }}</strong>
                        </div>
                        <div class="payment-hero-meta-card">
                            <span>Total</span>
                            <strong>Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>
            </article>

            @if (! $midtransConfigured)
                <article class="portal-card border border-amber-200 bg-amber-50/80">
                    <p class="portal-kicker">Konfigurasi dibutuhkan</p>
                    <h2 class="mt-2 font-heading text-2xl font-bold text-amber-700">Midtrans belum siap dipakai.</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Isi <code>MIDTRANS_SERVER_KEY</code> dan <code>MIDTRANS_CLIENT_KEY</code> di <code>.env</code> dengan kredensial sandbox Midtrans terlebih dulu.
                    </p>
                </article>
            @endif

            <article class="portal-card">
                <div class="portal-section-head">
                    <div>
                        <p class="portal-kicker">Alur simpel</p>
                        <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Satu tombol untuk buka Snap</h2>
                        <p class="portal-section-copy">Midtrans akan menampilkan pilihan VA, e-wallet, QRIS, dan kartu sesuai channel yang tersedia di akun sandbox kamu.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Channel</p>
                        <p class="mt-2 font-semibold text-slate-800">VA, QRIS, GoPay, ShopeePay, kartu, dan metode Snap lainnya</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Status lokal</p>
                        <p class="mt-2 font-semibold text-slate-800">
                            {{ $midtransLocalMode ? 'Mode lokal tanpa webhook publik' : 'Mode webhook publik aktif' }}
                        </p>
                    </div>
                </div>

                @if ($midtransLocalMode)
                    <div class="payment-instruction-panel mt-5">
                        <p class="portal-kicker">Mode lokal</p>
                        <div class="mt-3 space-y-2 text-sm leading-7 text-slate-600">
                            <p>Status pembayaran akan dicoba sinkron otomatis saat kamu kembali dari halaman Midtrans.</p>
                            <p>Kalau status belum berubah, tinggal buka detail pembayaran lalu klik tombol cek status. Tidak perlu nginx.</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('payments.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    <input type="hidden" name="payment_method" value="midtrans_snap">

                    @error('booking_id')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('payment_method')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('payment')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="portal-btn-gold" @disabled(! $midtransConfigured)>
                            {{ $pendingMidtrans ? 'Buat Sesi Midtrans Baru' : 'Buka Midtrans Snap' }}
                        </button>

                        @if ($pendingMidtrans && filled($latestPayment->midtrans_redirect_url))
                            <a href="{{ $latestPayment->midtrans_redirect_url }}" target="_blank" rel="noopener" class="portal-btn-blue">
                                Lanjutkan Pembayaran Sebelumnya
                            </a>
                        @endif

                        @if ($pendingMidtrans)
                            <a href="{{ route('payments.show', $latestPayment) }}" class="portal-btn-blue">
                                Detail Pembayaran Terakhir
                            </a>
                        @endif

                        <a href="{{ route('my-bookings.show', $booking) }}" class="portal-btn-blue">Kembali ke Booking</a>
                    </div>
                </form>
            </article>
        </div>

        <aside class="payment-summary-rail">
            <div class="payment-summary-rail-card">
                <p class="portal-kicker">Ringkasan booking</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Konteks pembayaran</h2>

                <div class="mt-5 space-y-3">
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Rute</p>
                        <p class="font-semibold text-slate-800">{{ $booking->flight->departureAirport->code }} ke {{ $booking->flight->arrivalAirport->code }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $booking->flight->airline->name }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Jadwal</p>
                        <p class="font-semibold text-slate-800">{{ $booking->flight->departure_time->format('d M Y H:i') }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Status booking</p>
                        <p class="font-semibold text-slate-800">{{ ucfirst($booking->status) }}</p>
                        @if ($booking->expired_at)
                            <p class="mt-1 text-sm text-amber-600">Deadline {{ $booking->expired_at->format('d M Y H:i:s') }}</p>
                        @endif
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Transaksi terakhir</p>
                        @if ($latestPayment)
                            <p class="font-semibold text-slate-800">{{ ucfirst($latestPayment->payment_status) }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $latestPayment->submitted_at?->format('d M Y H:i:s') ?: '-' }}</p>
                        @else
                            <p class="font-semibold text-slate-800">Belum ada transaksi.</p>
                        @endif
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Total</p>
                        <p class="text-3xl font-bold text-[#c2410c]">Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </aside>
    </section>
@endsection
