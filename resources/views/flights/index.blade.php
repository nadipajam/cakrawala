@extends('layouts.portal')

@section('title', 'Cakrawala | Penerbangan')
@section('active', 'flights')

@section('content')
    <section class="space-y-6">
        <article class="portal-editorial-panel">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
                <div>
                    <p class="portal-kicker">Daftar penerbangan</p>
                    <h1 class="portal-section-title max-w-4xl">Cari jadwal penerbangan yang sesuai dengan rute, waktu, dan anggaran Anda.</h1>
                    <p class="portal-section-copy max-w-3xl">Gunakan filter di bawah untuk menyaring hasil, lalu pilih penerbangan yang ingin dipesan.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-3 xl:grid-cols-1">
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-orange-700/80">Hasil</p>
                        <p class="mt-2 text-3xl font-bold text-[#c2410c]">{{ $flights->total() }}</p>
                        <p class="mt-1 text-sm text-slate-600">Penerbangan cocok dengan filter aktif.</p>
                    </div>
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-orange-700/80">Maskapai</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800">{{ $flights->pluck('airline_id')->filter()->unique()->count() }}</p>
                        <p class="mt-1 text-sm text-slate-600">Maskapai tampil di halaman ini.</p>
                    </div>
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-orange-700/80">Rentang tarif</p>
                        <p class="mt-2 text-lg font-bold text-slate-800">
                            @if ($flights->count() > 0)
                                Rp{{ number_format((float) $flights->min('price'), 0, ',', '.') }} - Rp{{ number_format((float) $flights->max('price'), 0, ',', '.') }}
                            @else
                                Tidak ada tarif
                            @endif
                        </p>
                        <p class="mt-1 text-sm text-slate-600">Rentang harga dasar dari hasil saat ini.</p>
                    </div>
                </div>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
            <article class="portal-bento-card">
                <div class="portal-section-head">
                    <div>
                        <p class="portal-kicker">Matriks pencarian</p>
                        <h2 class="text-2xl font-bold text-[color:var(--cakrawala-ink-900)] sm:text-3xl">Filter penerbangan</h2>
                    </div>
                    <a href="{{ route('flights.index') }}" class="portal-inline-note">Reset semua</a>
                </div>

                <form action="{{ route('flights.index') }}" method="GET" class="mt-6 grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
                    <div>
                        <label for="from" class="portal-label">Dari</label>
                        <select id="from" name="from" class="portal-select">
                            <option value="">Semua asal</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->id }}" @selected(($filters['from'] ?? null) == $airport->id)>{{ $airport->city }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="to" class="portal-label">Ke</label>
                        <select id="to" name="to" class="portal-select">
                            <option value="">Semua tujuan</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->id }}" @selected(($filters['to'] ?? null) == $airport->id)>{{ $airport->city }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date" class="portal-label">Tanggal</label>
                        <input id="date" type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="portal-input">
                    </div>
                    <div>
                        <label for="airline_id" class="portal-label">Maskapai</label>
                        <select id="airline_id" name="airline_id" class="portal-select">
                            <option value="">Semua maskapai</option>
                            @foreach ($airlines as $airline)
                                <option value="{{ $airline->id }}" @selected(($filters['airline_id'] ?? null) == $airline->id)>{{ $airline->name }}</option>
                            @endforeach
                        </select>
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
                    <div>
                        <label for="max_price" class="portal-label">Harga Maksimum</label>
                        <input id="max_price" type="number" min="0" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="portal-input" placeholder="Contoh: 1500000">
                    </div>
                    <div>
                        <label for="time" class="portal-label">Waktu Berangkat</label>
                        <select id="time" name="time" class="portal-select">
                            <option value="">Semua waktu</option>
                            <option value="morning" @selected(($filters['time'] ?? '') === 'morning')>Pagi</option>
                            <option value="afternoon" @selected(($filters['time'] ?? '') === 'afternoon')>Siang</option>
                            <option value="evening" @selected(($filters['time'] ?? '') === 'evening')>Sore</option>
                            <option value="night" @selected(($filters['time'] ?? '') === 'night')>Malam</option>
                        </select>
                    </div>
                    <div>
                        <label for="sort" class="portal-label">Urutkan</label>
                        <select id="sort" name="sort" class="portal-select">
                            <option value="time_asc" @selected(($filters['sort'] ?? 'time_asc') === 'time_asc')>Waktu berangkat terawal</option>
                            <option value="time_desc" @selected(($filters['sort'] ?? '') === 'time_desc')>Waktu berangkat terbaru</option>
                            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Harga termurah</option>
                            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Harga termahal</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 2xl:col-span-4 flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="portal-btn-gold">Terapkan Filter</button>
                        <a href="{{ route('flights.index') }}" class="portal-btn-blue">Bersihkan</a>
                    </div>
                </form>
            </article>

            <article class="portal-bento-card">
                <p class="portal-kicker">Filter aktif</p>
                <h2 class="mt-2 text-2xl font-bold text-[color:var(--cakrawala-ink-900)]">Ringkasan cepat</h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Route</p>
                        <p class="mt-2 font-semibold text-slate-800">
                            {{ ($filters['from'] ?? null) ? optional($airports->firstWhere('id', (int) $filters['from']))?->code : 'Semua' }}
                            -
                            {{ ($filters['to'] ?? null) ? optional($airports->firstWhere('id', (int) $filters['to']))?->code : 'Semua' }}
                        </p>
                    </div>
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Date and time</p>
                        <p class="mt-2 font-semibold text-slate-800">{{ $filters['date'] ?? 'Semua tanggal' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $filters['time'] ?? 'Semua waktu' }}</p>
                    </div>
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Cabin and price</p>
                        <p class="mt-2 font-semibold text-slate-800">{{ $filters['class'] ? \App\Support\CabinClass::label($filters['class']) : 'Semua kelas' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $filters['max_price'] ? 'Hingga Rp'.number_format((float) $filters['max_price'], 0, ',', '.') : 'Semua tarif' }}</p>
                    </div>
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Sort</p>
                        <p class="mt-2 font-semibold text-slate-800">{{ str_replace('_', ' ', $filters['sort'] ?? 'time_asc') }}</p>
                    </div>
                </div>
            </article>
        </div>

        <div class="space-y-4">
            @forelse ($flights as $flight)
                @php
                    $availableSeats = (int) ($seatAvailability[$flight->id] ?? 0);
                    $selectedCabinClass = $filters['class'] ?? null;
                    $displayPrice = $selectedCabinClass
                        ? \App\Support\CabinClass::price((float) $flight->price, $selectedCabinClass)
                        : (float) $flight->price;
                @endphp
                <article class="portal-flight-strip">
                    <div class="grid gap-5 xl:grid-cols-[220px_minmax(0,1fr)_220px] xl:items-center">
                        <div class="space-y-3">
                            <p class="portal-kicker">{{ $flight->airline->name }}</p>
                            <h3 class="text-4xl font-bold tracking-tight text-slate-800">{{ $flight->flight_number }}</h3>
                            <span class="portal-inline-note">{{ $availableSeats }} kursi tersisa</span>
                        </div>

                        <div class="grid gap-3 md:grid-cols-3">
                            <div class="portal-stack-card">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Route</p>
                                <p class="mt-2 text-xl font-bold text-slate-800">{{ $flight->departureAirport->code }} &rarr; {{ $flight->arrivalAirport->code }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $flight->departureAirport->city }} ke {{ $flight->arrivalAirport->city }}</p>
                            </div>
                            <div class="portal-stack-card">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Departure</p>
                                <p class="mt-2 text-lg font-semibold text-slate-800">{{ $flight->departure_time->format('d M Y H:i') }}</p>
                            </div>
                            <div class="portal-stack-card">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Arrival</p>
                                <p class="mt-2 text-lg font-semibold text-slate-800">{{ $flight->arrival_time->format('d M Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="portal-surface-muted flex flex-col gap-4">
                            <div>
                                <p class="text-sm text-slate-500">{{ $selectedCabinClass ? \App\Support\CabinClass::label($selectedCabinClass) : 'Tarif mulai dari' }}</p>
                                <p class="mt-2 text-4xl font-bold text-[#c2410c]">Rp{{ number_format($displayPrice, 0, ',', '.') }}</p>
                            </div>
                            <a href="{{ route('flights.show', ['flight' => $flight, 'class' => $selectedCabinClass]) }}" class="portal-btn-gold w-full justify-center">Pilih Penerbangan</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="portal-card text-center text-slate-600">Tidak ada penerbangan yang cocok dengan filter Anda.</div>
            @endforelse
        </div>

        @if ($flights->hasPages())
            <div class="portal-card">
                {{ $flights->links() }}
            </div>
        @endif
    </section>
@endsection
