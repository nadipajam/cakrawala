@extends('layouts.portal')

@section('title', 'Cakrawala | Contact')
@section('active', 'contact')

@section('content')
    <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <article class="portal-card">
            <div class="portal-section-head">
                <div>
                    <p class="portal-kicker">Hubungi Kami</p>
                    <h1 class="portal-section-title">Butuh bantuan terkait perjalanan Anda?</h1>
                    <p class="portal-section-copy">
                        Hubungi tim Cakrawala untuk pertanyaan booking, perubahan jadwal, atau bantuan pembayaran.
                        Seluruh kanal layanan pelanggan disusun dalam satu halaman agar lebih mudah diakses.
                    </p>
                </div>
                <span class="portal-inline-note">Waktu respons rata-rata 15 - 30 menit</span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2">
                <div class="portal-card-soft">
                    <p class="text-sm uppercase tracking-wide text-slate-500">Layanan Pelanggan</p>
                    <p class="mt-2 text-xl font-semibold text-slate-800">support@cakrawalaair.com</p>
                    <p class="mt-1 text-sm text-slate-500">Target respons hingga 15 menit</p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm uppercase tracking-wide text-slate-500">Call Center</p>
                    <p class="mt-2 text-xl font-semibold text-slate-800">0800-100-9666</p>
                    <p class="mt-1 text-sm text-slate-500">Layanan 24 jam untuk booking dan pembayaran</p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm uppercase tracking-wide text-slate-500">Jam Operasional</p>
                    <p class="mt-2 text-xl font-semibold text-slate-800">Senin - Minggu</p>
                    <p class="mt-1 text-sm text-slate-500">00:00 - 23:59 WIB</p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm uppercase tracking-wide text-slate-500">Kantor Pusat</p>
                    <p class="mt-2 text-xl font-semibold text-slate-800">Jakarta Operations Center</p>
                    <p class="mt-1 text-sm text-slate-500">Sudirman Business District, Jakarta</p>
                </div>
            </div>

            <div class="mt-8 portal-surface-muted">
                <p class="portal-kicker">Formulir layanan</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-slate-800">Kirim permintaan secara detail</h2>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">Pesan yang dikirim dari formulir ini akan masuk ke kotak masuk tim operasional agar dapat ditangani lebih rapi.</p>

                <form method="POST" action="{{ route('contact.submit') }}" class="mt-6 grid gap-4 md:grid-cols-2">
                    @csrf
                    <div>
                        <label class="portal-label" for="name">Full Name</label>
                        <input id="name" name="name" value="{{ old('name', auth()->user()?->name) }}" class="portal-input" required>
                    </div>
                    <div>
                        <label class="portal-label" for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', auth()->user()?->email) }}" class="portal-input" required>
                    </div>
                    <div>
                        <label class="portal-label" for="phone">Phone</label>
                        <input id="phone" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" class="portal-input" placeholder="+62...">
                    </div>
                    <div>
                        <label class="portal-label" for="subject">Subject</label>
                        <input id="subject" name="subject" value="{{ old('subject') }}" class="portal-input" placeholder="Perubahan jadwal, refund, atau kendala pembayaran" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="portal-label" for="message">Message</label>
                        <textarea id="message" name="message" rows="6" class="portal-input" required>{{ old('message') }}</textarea>
                    </div>
                    <div class="md:col-span-2 flex flex-wrap items-center gap-3">
                        <button type="submit" class="portal-btn-gold">Kirim Permintaan Layanan</button>
                        <p class="text-sm text-slate-500">Gunakan subjek dan detail yang jelas agar staf dapat langsung menindaklanjuti.</p>
                    </div>
                </form>
            </div>
        </article>

        <aside class="portal-side-panel">
            <p class="portal-kicker">Bandara unggulan</p>
            <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Bandara Rekomendasi</h2>
            <div class="mt-5 space-y-3">
                @foreach ($airports as $airport)
                    <div class="portal-card-soft">
                        <p class="font-semibold text-slate-800">{{ $airport->city }} ({{ $airport->code }})</p>
                        <p class="text-sm text-slate-500">{{ $airport->name }}</p>
                    </div>
                @endforeach
            </div>

            @auth
                <div class="mt-6 border-t border-slate-200 pt-6">
                    <p class="portal-kicker">Riwayat bantuan</p>
                    <h3 class="mt-2 font-heading text-2xl font-bold text-[#c2410c]">Permintaan Layanan Terbaru Anda</h3>
                    <div class="mt-4 space-y-3">
                        @forelse ($recentMessages as $recentMessage)
                            <div class="portal-card-soft">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $recentMessage->subject }}</p>
                                        <p class="mt-1 text-sm text-slate-500">{{ $recentMessage->created_at?->format('d M Y H:i') }}</p>
                                    </div>
                                    @if ($recentMessage->status === 'resolved')
                                        <span class="portal-status-confirmed">Selesai</span>
                                    @elseif ($recentMessage->status === 'closed')
                                        <span class="portal-status-cancelled">Ditutup</span>
                                    @elseif ($recentMessage->status === 'in_progress')
                                        <span class="portal-status-pending">Diproses</span>
                                    @else
                                        <span class="portal-status-default">Terbuka</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="portal-card-soft text-sm text-slate-500">Belum ada permintaan bantuan yang Anda kirim.</div>
                        @endforelse
                    </div>
                </div>
            @endauth
        </aside>
    </section>
@endsection
