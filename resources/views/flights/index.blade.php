@extends('layouts.portal')

@section('title', 'Cakrawala | Flights')
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
                        <p class="text-xs uppercase tracking-[0.22em] text-orange-700/80">Results</p>
                        <p class="mt-2 text-3xl font-bold text-[#0f3f78]">{{ $flights->total() }}</p>
                        <p class="mt-1 text-sm text-slate-600">Flight cocok dengan filter aktif.</p>
                    </div>
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-orange-700/80">Airlines</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800">{{ $flights->pluck('airline_id')->filter()->unique()->count() }}</p>
                        <p class="mt-1 text-sm text-slate-600">Maskapai tampil di halaman ini.</p>
                    </div>
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-orange-700/80">Fare range</p>
                        <p class="mt-2 text-lg font-bold text-slate-800">
                            @if ($flights->count() > 0)
                                Rp{{ number_format((float) $flights->min('price'), 0, ',', '.') }} - Rp{{ number_format((float) $flights->max('price'), 0, ',', '.') }}
                            @else
                                No fare
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
                        <p class="portal-kicker">Search matrix</p>
                        <h2 class="text-2xl font-bold text-[color:var(--cakrawala-ink-900)] sm:text-3xl">Filter flights</h2>
                    </div>
                    <a href="{{ route('flights.index') }}" class="portal-inline-note">Reset all</a>
                </div>

                <form action="{{ route('flights.index') }}" method="GET" class="mt-6 grid gap-4 md:grid-cols-2 2xl:grid-cols-4">
                    <div>
                        <label for="from" class="portal-label">From</label>
                        <select id="from" name="from" class="portal-select">
                            <option value="">All origin</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->id }}" @selected(($filters['from'] ?? null) == $airport->id)>{{ $airport->city }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="to" class="portal-label">To</label>
                        <select id="to" name="to" class="portal-select">
                            <option value="">All destination</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->id }}" @selected(($filters['to'] ?? null) == $airport->id)>{{ $airport->city }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date" class="portal-label">Date</label>
                        <input id="date" type="date" name="date" value="{{ $filters['date'] ?? '' }}" class="portal-input">
                    </div>
                    <div>
                        <label for="airline_id" class="portal-label">Airline</label>
                        <select id="airline_id" name="airline_id" class="portal-select">
                            <option value="">All airlines</option>
                            @foreach ($airlines as $airline)
                                <option value="{{ $airline->id }}" @selected(($filters['airline_id'] ?? null) == $airline->id)>{{ $airline->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="class" class="portal-label">Class</label>
                        <select id="class" name="class" class="portal-select">
                            <option value="">Any class</option>
                            <option value="economy" @selected(($filters['class'] ?? '') === 'economy')>Economy</option>
                            <option value="business" @selected(($filters['class'] ?? '') === 'business')>Business</option>
                            <option value="first" @selected(($filters['class'] ?? '') === 'first')>First</option>
                        </select>
                    </div>
                    <div>
                        <label for="max_price" class="portal-label">Max Price</label>
                        <input id="max_price" type="number" min="0" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="portal-input" placeholder="Example: 1500000">
                    </div>
                    <div>
                        <label for="time" class="portal-label">Departure Time</label>
                        <select id="time" name="time" class="portal-select">
                            <option value="">Any time</option>
                            <option value="morning" @selected(($filters['time'] ?? '') === 'morning')>Morning</option>
                            <option value="afternoon" @selected(($filters['time'] ?? '') === 'afternoon')>Afternoon</option>
                            <option value="evening" @selected(($filters['time'] ?? '') === 'evening')>Evening</option>
                            <option value="night" @selected(($filters['time'] ?? '') === 'night')>Night</option>
                        </select>
                    </div>
                    <div>
                        <label for="sort" class="portal-label">Sort</label>
                        <select id="sort" name="sort" class="portal-select">
                            <option value="time_asc" @selected(($filters['sort'] ?? 'time_asc') === 'time_asc')>Departure Time Up</option>
                            <option value="time_desc" @selected(($filters['sort'] ?? '') === 'time_desc')>Departure Time Down</option>
                            <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>Price Up</option>
                            <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>Price Down</option>
                        </select>
                    </div>
                    <div class="md:col-span-2 2xl:col-span-4 flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="portal-btn-gold">Apply Filter</button>
                        <a href="{{ route('flights.index') }}" class="portal-btn-blue">Clear</a>
                    </div>
                </form>
            </article>

            <article class="portal-bento-card">
                <p class="portal-kicker">Active filters</p>
                <h2 class="mt-2 text-2xl font-bold text-[color:var(--cakrawala-ink-900)]">Quick reading</h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Route</p>
                        <p class="mt-2 font-semibold text-slate-800">
                            {{ ($filters['from'] ?? null) ? optional($airports->firstWhere('id', (int) $filters['from']))?->code : 'Any' }}
                            -
                            {{ ($filters['to'] ?? null) ? optional($airports->firstWhere('id', (int) $filters['to']))?->code : 'Any' }}
                        </p>
                    </div>
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Date and time</p>
                        <p class="mt-2 font-semibold text-slate-800">{{ $filters['date'] ?? 'Any date' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $filters['time'] ?? 'Any time' }}</p>
                    </div>
                    <div class="portal-stack-card">
                        <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Cabin and price</p>
                        <p class="mt-2 font-semibold text-slate-800">{{ $filters['class'] ? \App\Support\CabinClass::label($filters['class']) : 'Any class' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $filters['max_price'] ? 'Up to Rp'.number_format((float) $filters['max_price'], 0, ',', '.') : 'Any fare' }}</p>
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
                            <span class="portal-inline-note">{{ $availableSeats }} seats left</span>
                        </div>

                        <div class="grid gap-3 md:grid-cols-3">
                            <div class="portal-stack-card">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Route</p>
                                <p class="mt-2 text-xl font-bold text-slate-800">{{ $flight->departureAirport->code }} &rarr; {{ $flight->arrivalAirport->code }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $flight->departureAirport->city }} to {{ $flight->arrivalAirport->city }}</p>
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
                                <p class="text-sm text-slate-500">{{ $selectedCabinClass ? \App\Support\CabinClass::label($selectedCabinClass) : 'Starting fare' }}</p>
                                <p class="mt-2 text-4xl font-bold text-[#0f3f78]">Rp{{ number_format($displayPrice, 0, ',', '.') }}</p>
                            </div>
                            <a href="{{ route('flights.show', ['flight' => $flight, 'class' => $selectedCabinClass]) }}" class="portal-btn-gold w-full justify-center">Select Flight</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="portal-card text-center text-slate-600">No flights match your filters.</div>
            @endforelse
        </div>

        @if ($flights->hasPages())
            <div class="portal-card">
                {{ $flights->links() }}
            </div>
        @endif
    </section>
@endsection
