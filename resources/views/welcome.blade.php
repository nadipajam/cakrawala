@extends('layouts.portal')

@section('title', 'Cakrawala | Home')
@section('active', 'home')

@section('content')
    @php
        $ticketBooking = $recentBookings->first(fn ($booking) => $booking->details->contains(fn ($detail) => $detail->ticket !== null));
    @endphp

    <section class="space-y-8">
        <article class="portal-editorial-panel">
            <div class="grid gap-6 xl:grid-cols-[1.12fr_.88fr]">
                <div class="space-y-6">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="portal-pill">Portal penerbangan</span>
                        <span class="portal-inline-note">Jadwal . Booking . Perjalanan</span>
                    </div>

                    <div class="max-w-4xl">
                        <h1 class="font-heading text-5xl font-extrabold leading-[0.98] tracking-tight text-[color:var(--cakrawala-ink-900)] sm:text-6xl xl:text-[5.3rem]">
                            Rencanakan perjalanan Anda dengan lebih mudah dari satu portal.
                        </h1>
                        <p class="mt-6 max-w-3xl text-lg leading-9 text-slate-700">
                            Cakrawala menyatukan pencarian jadwal, pemesanan, pembayaran, check-in, dan tiket dalam alur yang jelas serta nyaman digunakan.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('flights.index') }}" class="portal-btn-gold">Cari Penerbangan</a>
                        <a href="{{ auth()->check() ? (auth()->user()->isCustomer() ? route('my-bookings.index') : route('admin.dashboard')) : route('login') }}" class="portal-btn-blue">
                            {{ auth()->check() ? 'Buka Dashboard' : 'Masuk' }}
                        </a>
                    </div>
                </div>

                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                        <div class="portal-bento-card">
                        <p class="portal-kicker">Ringkasan layanan</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-3 xl:grid-cols-1">
                            <div class="portal-stack-card">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Penerbangan unggulan</p>
                                <p class="mt-2 text-3xl font-bold text-[#c2410c]">{{ $featuredFlights->count() }}</p>
                            </div>
                            <div class="portal-stack-card">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Maskapai mitra</p>
                                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $airlines->count() }}</p>
                            </div>
                            <div class="portal-stack-card">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Booking terbaru</p>
                                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $recentBookings->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="portal-bento-card">
                        <p class="portal-kicker">Alur perjalanan</p>
                        <div class="mt-4 space-y-3">
                            <div class="portal-process-step portal-process-step-active">
                                <span class="portal-brand-mark h-10 w-10 text-sm">01</span>
                                <div>
                                    <p class="text-slate-800">Cari rute</p>
                                    <p class="text-xs font-medium text-slate-500">Pilih asal, tujuan, tanggal, dan kelas penerbangan dalam satu langkah.</p>
                                </div>
                            </div>
                            <div class="portal-process-step">
                                <span class="portal-brand-mark h-10 w-10 text-sm">02</span>
                                <div>
                                    <p class="text-slate-800">Lengkapi booking</p>
                                    <p class="text-xs font-medium text-slate-500">Data penumpang, kursi, dan layanan tambahan tersusun rapi.</p>
                                </div>
                            </div>
                            <div class="portal-process-step">
                                <span class="portal-brand-mark h-10 w-10 text-sm">03</span>
                                <div>
                                    <p class="text-slate-800">Kelola perjalanan</p>
                                    <p class="text-xs font-medium text-slate-500">Pembayaran, check-in, dan tiket dapat diakses dari halaman booking yang sama.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-[1.02fr_.98fr]">
            <article class="portal-bento-card">
                <div class="portal-section-head">
                    <div>
                        <p class="portal-kicker">Pencarian rute</p>
                        <h2 class="text-3xl font-bold text-[color:var(--cakrawala-ink-900)]">Cari penerbangan dengan cepat</h2>
                    </div>
                </div>

                <form action="{{ route('flights.index') }}" method="GET" class="mt-6 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="from" class="portal-label">Dari</label>
                        <select id="from" name="from" class="portal-select">
                            <option value="">Pilih bandara asal</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->id }}" @selected(($filters['departure_airport_id'] ?? null) == $airport->id)>{{ $airport->city }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="to" class="portal-label">Ke</label>
                        <select id="to" name="to" class="portal-select">
                            <option value="">Pilih bandara tujuan</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->id }}" @selected(($filters['arrival_airport_id'] ?? null) == $airport->id)>{{ $airport->city }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date" class="portal-label">Tanggal Berangkat</label>
                        <input id="date" name="date" type="date" value="{{ $filters['departure_date'] ?? '' }}" class="portal-input">
                    </div>
                    <div>
                        <label for="class" class="portal-label">Kelas</label>
                        <select id="class" name="class" class="portal-select">
                            <option value="">Semua kelas</option>
                            <option value="economy" @selected(($filters['class'] ?? '') === 'economy')>Ekonomi</option>
                            <option value="business" @selected(($filters['class'] ?? '') === 'business')>Bisnis</option>
                            <option value="first" @selected(($filters['class'] ?? '') === 'first')>First Class</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="portal-btn-gold">Cari Penerbangan</button>
                        <a href="{{ route('flights.index') }}" class="portal-btn-blue">Lihat Semua Jadwal</a>
                    </div>
                </form>
            </article>

            <article class="portal-bento-card">
                <p class="portal-kicker">Akses cepat</p>
                <h2 class="mt-2 text-3xl font-bold text-[color:var(--cakrawala-ink-900)]">Lanjutkan proses perjalanan lebih cepat</h2>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('flights.index') }}" class="quick-link-card">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="7" />
                                <path d="M20 20l-3.5-3.5" />
                            </svg>
                        </span>
                        <span class="quick-link-title">Cari Penerbangan</span>
                    </a>
                    <a href="{{ auth()->check() && auth()->user()->isCustomer() ? route('my-bookings.index') : (auth()->check() ? route('admin.dashboard') : route('login')) }}" class="quick-link-card">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 3h8l4 4v14H4V3h4z" />
                                <path d="M8 11h8M8 15h8" />
                            </svg>
                        </span>
                        <span class="quick-link-title">Booking Saya</span>
                    </a>
                    <a href="{{ auth()->check() && auth()->user()->isCustomer() ? route('passengers.index') : (auth()->check() ? route('admin.dashboard') : route('login')) }}" class="quick-link-card">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="9" cy="8" r="3" />
                                <path d="M4 19a5 5 0 0 1 10 0" />
                                <circle cx="17" cy="9" r="2" />
                                <path d="M14 19a4 4 0 0 1 6 0" />
                            </svg>
                        </span>
                        <span class="quick-link-title">Data Penumpang</span>
                    </a>
                    <a href="{{ auth()->check() && auth()->user()->isCustomer() && $ticketBooking ? route('my-bookings.tickets', $ticketBooking) : (auth()->check() ? route('admin.dashboard') : route('login')) }}" class="quick-link-card">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V9z" />
                                <path d="M9 7v10" />
                            </svg>
                        </span>
                        <span class="quick-link-title">Tiket Saya</span>
                    </a>
                </div>
            </article>
        </div>

        <section>
            <div class="portal-section-head">
                <div>
                    <p class="portal-kicker">Rute populer</p>
                    <h2 class="portal-section-title">Penerbangan unggulan</h2>
                    <p class="portal-section-copy">Pilihan rute unggulan untuk membantu pengguna menemukan jadwal yang sesuai lebih cepat.</p>
                </div>
                <a href="{{ route('flights.index') }}" class="portal-btn-blue">Lihat Semua Penerbangan</a>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($featuredFlights as $flight)
                    @php($availableSeats = (int) ($seatAvailability[$flight->id] ?? 0))
                    <article class="portal-flight-strip">
                        <div class="grid gap-5 xl:grid-cols-[220px_minmax(0,1fr)_220px] xl:items-center">
                            <div>
                                <p class="portal-kicker">{{ $flight->airline->name }}</p>
                                <h3 class="mt-2 text-4xl font-bold tracking-tight text-slate-800">{{ $flight->flight_number }}</h3>
                                <span class="mt-3 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $availableSeats }} kursi tersedia</span>
                            </div>
                            <div class="grid gap-3 md:grid-cols-3">
                                <div class="portal-stack-card">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Rute</p>
                                    <p class="mt-2 text-xl font-bold text-slate-800">{{ $flight->departureAirport->code }} &rarr; {{ $flight->arrivalAirport->code }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $flight->departureAirport->city }} ke {{ $flight->arrivalAirport->city }}</p>
                                </div>
                                <div class="portal-stack-card">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Keberangkatan</p>
                                    <p class="mt-2 text-lg font-semibold text-slate-800">{{ $flight->departure_time->format('d M Y H:i') }}</p>
                                </div>
                                <div class="portal-stack-card">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Kedatangan</p>
                                    <p class="mt-2 text-lg font-semibold text-slate-800">{{ $flight->arrival_time->format('d M Y H:i') }}</p>
                                </div>
                            </div>
                            <div class="portal-surface-muted flex flex-col gap-4">
                                <div>
                                    <p class="text-sm text-slate-500">Tarif mulai dari</p>
                                    <p class="mt-2 text-4xl font-bold text-[#c2410c]">Rp{{ number_format((float) $flight->price, 0, ',', '.') }}</p>
                                </div>
                                <a href="{{ route('flights.show', $flight) }}" class="portal-btn-gold w-full justify-center">Lihat Detail</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="portal-card text-center text-slate-600">Belum ada penerbangan unggulan saat ini.</div>
                @endforelse
            </div>
        </section>
    </section>
@endsection
