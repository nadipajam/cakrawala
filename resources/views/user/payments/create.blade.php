@extends('layouts.portal')

@section('title', 'Cakrawala | Pembayaran')
@section('active', 'bookings')

@section('content')
    @php($initialMethod = old('payment_method', 'midtrans_snap'))

    <section
        x-data="paymentForm({{ \Illuminate\Support\Js::from($paymentMethods) }}, '{{ $initialMethod }}')"
        class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]"
    >
        <article class="space-y-6">
            <article class="payment-hero-panel">
                <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
                    <div>
                        <p class="booking-shell-kicker">Pembayaran</p>
                        <h1 class="booking-shell-title">Kirim pembayaran booking dengan kanal yang paling sesuai.</h1>
                        <p class="booking-shell-copy">Semua transaksi online diproses melalui Midtrans Snap dan status pembayaran diperbarui otomatis.</p>
                    </div>

                    <div class="payment-hero-meta">
                        <div class="payment-hero-meta-card">
                            <span>Status booking</span>
                            <strong>{{ ucfirst($booking->status) }}</strong>
                        </div>
                        <div class="payment-hero-meta-card">
                            <span>Status pembayaran</span>
                            <strong>Diproses via Midtrans</strong>
                        </div>
                    </div>
                </div>
            </article>

            <article class="portal-card">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="portal-metric-card">
                        <p class="portal-kicker">Ketentuan waktu</p>
                        <p class="mt-3 text-lg font-semibold text-slate-800">Ikuti instruksi pembayaran di halaman Midtrans sampai transaksi selesai.</p>
                        @if ($booking->expired_at)
                            <p class="mt-2 text-sm font-semibold text-amber-600" x-text="countdown"></p>
                        @endif
                    </div>
                    <div class="portal-metric-card">
                        <p class="portal-kicker">Route</p>
                        <p class="mt-3 text-lg font-semibold text-slate-800">{{ $booking->flight->departureAirport->code }} ke {{ $booking->flight->arrivalAirport->code }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">

                    <div>
                        <div class="portal-section-head">
                            <div>
                                <p class="portal-kicker">Metode</p>
                                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Pilih kanal pembayaran</h2>
                                <p class="portal-section-copy">Pilih metode pembayaran, lalu isi data pengirim sesuai kebutuhan. Petunjuk akan menyesuaikan metode yang dipilih.</p>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @foreach ($paymentMethods as $value => $method)
                                <label class="payment-channel-card" :class="method === '{{ $value }}' ? 'payment-channel-card-active' : ''">
                                    <input type="radio" name="payment_method" value="{{ $value }}" class="mt-1 h-4 w-4 border-slate-300 text-[#c2410c]" x-model="method">
                                    <span class="min-w-0">
                                        <span class="flex items-center gap-3">
                                            <span class="portal-brand-mark h-10 w-10 text-xs">{{ $method['icon'] }}</span>
                                            <span class="block text-base font-semibold text-slate-800">{{ $method['label'] }}</span>
                                        </span>
                                        <span class="mt-2 block text-sm text-slate-500">{{ $method['description'] }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @error('payment_method')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="payment-instruction-panel" x-show="selectedMethod" x-cloak>
                        <p class="portal-kicker">Petunjuk</p>
                        <template x-if="selectedMethod">
                            <div class="mt-3 space-y-2 text-sm leading-7 text-slate-600">
                                <p class="font-semibold text-slate-800" x-text="selectedMethod.label"></p>
                                <p x-text="selectedMethod.description"></p>
                                <template x-if="selectedMethod.destination?.bank_name">
                                    <p>
                                        Tujuan transfer:
                                        <span class="font-semibold text-slate-800" x-text="selectedMethod.destination.bank_name"></span>
                                        |
                                        <span class="font-semibold text-slate-800" x-text="selectedMethod.destination.account_number"></span>
                                        a/n
                                        <span class="font-semibold text-slate-800" x-text="selectedMethod.destination.account_name"></span>
                                    </p>
                                </template>
                                <template x-if="!selectedMethod.destination?.bank_name && selectedMethod.destination?.account_number">
                                    <p>
                                        Tujuan pembayaran:
                                        <span class="font-semibold text-slate-800" x-text="selectedMethod.destination.account_number"></span>
                                        a/n
                                        <span class="font-semibold text-slate-800" x-text="selectedMethod.destination.account_name"></span>
                                    </p>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div x-show="selectedType !== 'qris' && selectedType !== 'gateway'" x-cloak>
                            <label for="payer_name" class="portal-label">Nama Pengirim / Pemilik Akun</label>
                            <input id="payer_name" name="payer_name" value="{{ old('payer_name') }}" class="portal-input" placeholder="Nama pengirim">
                            @error('payer_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="selectedType === 'e_wallet' || selectedType === 'card'" x-cloak>
                            <label for="payer_phone" class="portal-label">Nomor Telepon Pembayar</label>
                            <input id="payer_phone" name="payer_phone" value="{{ old('payer_phone') }}" class="portal-input" placeholder="08xxxxxxxxxx">
                            @error('payer_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="selectedType === 'bank_transfer' || selectedType === 'virtual_account'" x-cloak>
                            <label for="payer_bank_name" class="portal-label">Bank Pengirim</label>
                            <input id="payer_bank_name" name="payer_bank_name" value="{{ old('payer_bank_name') }}" class="portal-input" placeholder="Contoh: BCA / BRI / Mandiri">
                            @error('payer_bank_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="selectedType === 'bank_transfer' || selectedType === 'virtual_account'" x-cloak>
                            <label for="payer_bank_account_number" class="portal-label">Nomor Rekening Pengirim</label>
                            <input id="payer_bank_account_number" name="payer_bank_account_number" value="{{ old('payer_bank_account_number') }}" class="portal-input" placeholder="Nomor rekening pengirim">
                            @error('payer_bank_account_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div x-show="selectedType === 'card'" x-cloak>
                        <label for="payment_notes" class="portal-label">Catatan Referensi Transaksi</label>
                        <textarea id="payment_notes" name="payment_notes" rows="3" class="portal-input" placeholder="Contoh: ref auth 9921 / 4 digit akhir kartu">{{ old('payment_notes') }}</textarea>
                        @error('payment_notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div x-show="selectedMethod && selectedMethod.requires_proof" x-cloak>
                        <label for="proof_file" class="portal-label">Upload Bukti Pembayaran</label>
                        <input id="proof_file" name="proof_file" type="file" class="portal-input">
                        <p class="mt-2 text-sm text-slate-500">Format yang diterima: JPG, PNG, PDF. Maksimum 2MB.</p>
                        @error('proof_file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <button type="submit" class="portal-btn-gold">Lanjut ke Midtrans</button>
                        <a href="{{ route('my-bookings.show', $booking) }}" class="portal-btn-blue">Kembali ke Booking</a>
                    </div>
                </form>
            </article>
        </article>

        <aside class="payment-summary-rail">
            <div class="payment-summary-rail-card">
                <p class="portal-kicker">Ringkasan booking</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Konteks Pembayaran</h2>
                <div class="mt-5 space-y-3">
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Booking code</p>
                        <p class="font-semibold text-slate-800">{{ $booking->booking_code }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Route</p>
                        <p class="font-semibold text-slate-800">{{ $booking->flight->departureAirport->code }} ke {{ $booking->flight->arrivalAirport->code }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Total</p>
                        <p class="text-3xl font-bold text-[#c2410c]">Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Pengajuan terakhir</p>
                        <p class="font-semibold text-slate-800">{{ ucfirst($latestPayment?->payment_status ?? 'pending') }}</p>
                        @if ($latestPayment?->submitted_at)
                            <p class="mt-1 text-sm text-slate-500">{{ $latestPayment->submitted_at->format('d M Y H:i:s') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </aside>
    </section>

    <script>
        function paymentForm(methods, initialMethod) {
            return {
                methods,
                method: initialMethod,
                expiresAt: @js(optional($booking->expired_at)?->toIso8601String()),
                countdown: '',
                get selectedMethod() {
                    return this.methods[this.method] || null;
                },
                get selectedType() {
                    return this.selectedMethod?.type || null;
                },
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
                        this.countdown = 'Sesi pembayaran telah berakhir.';
                        return;
                    }

                    const minutes = Math.floor(diff / 60000);
                    const seconds = Math.floor((diff % 60000) / 1000).toString().padStart(2, '0');
                    this.countdown = `Sisa waktu ${minutes}:${seconds}`;
                },
            };
        }
    </script>
@endsection
