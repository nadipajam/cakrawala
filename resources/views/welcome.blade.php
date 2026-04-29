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
                        <span class="portal-inline-note">Search . Manage . Fly</span>
                    </div>

                    <div class="max-w-4xl">
                        <h1 class="font-heading text-5xl font-extrabold leading-[0.98] tracking-tight text-[color:var(--cakrawala-ink-900)] sm:text-6xl xl:text-[5.3rem]">
                            Cari penerbangan, kelola booking, dan lanjutkan perjalanan dari satu tempat.
                        </h1>
                        <p class="mt-6 max-w-3xl text-lg leading-9 text-slate-700">
                            Halaman ini merangkum pencarian jadwal, booking, pembayaran, dan ticketing dengan tampilan yang lebih sederhana dan mudah dipakai.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('flights.index') }}" class="portal-btn-gold">Cari Penerbangan</a>
                        <a href="{{ auth()->check() ? (auth()->user()->isCustomer() ? route('my-bookings.index') : route('admin.dashboard')) : route('login') }}" class="portal-btn-blue">
                            {{ auth()->check() ? 'Buka Dashboard' : 'Login' }}
                        </a>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                    <div class="portal-bento-card">
                        <p class="portal-kicker">Live metrics</p>
                        <div class="mt-4 grid gap-4 sm:grid-cols-3 xl:grid-cols-1">
                            <div class="portal-stack-card">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Featured flights</p>
                                <p class="mt-2 text-3xl font-bold text-[#0f3f78]">{{ $featuredFlights->count() }}</p>
                            </div>
                            <div class="portal-stack-card">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Airline partners</p>
                                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $airlines->count() }}</p>
                            </div>
                            <div class="portal-stack-card">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Recent bookings</p>
                                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $recentBookings->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="portal-bento-card">
                        <p class="portal-kicker">Core loop</p>
                        <div class="mt-4 space-y-3">
                            <div class="portal-process-step portal-process-step-active">
                                <span class="portal-brand-mark h-10 w-10 text-sm">01</span>
                                <div>
                                    <p class="text-slate-800">Search routes</p>
                                    <p class="text-xs font-medium text-slate-500">Origin, destination, class, and fare in one pass.</p>
                                </div>
                            </div>
                            <div class="portal-process-step">
                                <span class="portal-brand-mark h-10 w-10 text-sm">02</span>
                                <div>
                                    <p class="text-slate-800">Build booking</p>
                                    <p class="text-xs font-medium text-slate-500">Passenger, seat, and addon flow stays connected.</p>
                                </div>
                            </div>
                            <div class="portal-process-step">
                                <span class="portal-brand-mark h-10 w-10 text-sm">03</span>
                                <div>
                                    <p class="text-slate-800">Manage trip</p>
                                    <p class="text-xs font-medium text-slate-500">Payment, check-in, and ticket remain close to the booking.</p>
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
                        <p class="portal-kicker">Route discovery</p>
                        <h2 class="text-3xl font-bold text-[color:var(--cakrawala-ink-900)]">Quick search canvas</h2>
                    </div>
                </div>

                <form action="{{ route('flights.index') }}" method="GET" class="mt-6 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="from" class="portal-label">From</label>
                        <select id="from" name="from" class="portal-select">
                            <option value="">Select departure</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->id }}" @selected(($filters['departure_airport_id'] ?? null) == $airport->id)>{{ $airport->city }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="to" class="portal-label">To</label>
                        <select id="to" name="to" class="portal-select">
                            <option value="">Select destination</option>
                            @foreach ($airports as $airport)
                                <option value="{{ $airport->id }}" @selected(($filters['arrival_airport_id'] ?? null) == $airport->id)>{{ $airport->city }} ({{ $airport->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date" class="portal-label">Departure Date</label>
                        <input id="date" name="date" type="date" value="{{ $filters['departure_date'] ?? '' }}" class="portal-input">
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
                    <div class="md:col-span-2 flex flex-wrap gap-3 pt-2">
                        <button type="submit" class="portal-btn-gold">Search Flights</button>
                        <a href="{{ route('flights.index') }}" class="portal-btn-blue">Open Board</a>
                    </div>
                </form>
            </article>

            <article class="portal-bento-card">
                <p class="portal-kicker">Travel actions</p>
                <h2 class="mt-2 text-3xl font-bold text-[color:var(--cakrawala-ink-900)]">Open the next step faster</h2>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <a href="{{ route('flights.index') }}" class="quick-link-card">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="7" />
                                <path d="M20 20l-3.5-3.5" />
                            </svg>
                        </span>
                        <span class="quick-link-title">Search Flights</span>
                    </a>
                    <a href="{{ auth()->check() && auth()->user()->isCustomer() ? route('my-bookings.index') : (auth()->check() ? route('admin.dashboard') : route('login')) }}" class="quick-link-card">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8 3h8l4 4v14H4V3h4z" />
                                <path d="M8 11h8M8 15h8" />
                            </svg>
                        </span>
                        <span class="quick-link-title">My Bookings</span>
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
                        <span class="quick-link-title">Passengers</span>
                    </a>
                    <a href="{{ auth()->check() && auth()->user()->isCustomer() && $ticketBooking ? route('my-bookings.tickets', $ticketBooking) : (auth()->check() ? route('admin.dashboard') : route('login')) }}" class="quick-link-card">
                        <span class="quick-link-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 9a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V9z" />
                                <path d="M9 7v10" />
                            </svg>
                        </span>
                        <span class="quick-link-title">My Tickets</span>
                    </a>
                </div>
            </article>
        </div>

        <section>
            <div class="portal-section-head">
                <div>
                    <p class="portal-kicker">Popular routes</p>
                    <h2 class="portal-section-title">Featured flights</h2>
                    <p class="portal-section-copy">Pilihan rute unggulan untuk membantu pengguna menemukan jadwal yang sesuai lebih cepat.</p>
                </div>
                <a href="{{ route('flights.index') }}" class="portal-btn-blue">View All Flights</a>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($featuredFlights as $flight)
                    @php($availableSeats = (int) ($seatAvailability[$flight->id] ?? 0))
                    <article class="portal-flight-strip">
                        <div class="grid gap-5 xl:grid-cols-[220px_minmax(0,1fr)_220px] xl:items-center">
                            <div>
                                <p class="portal-kicker">{{ $flight->airline->name }}</p>
                                <h3 class="mt-2 text-4xl font-bold tracking-tight text-slate-800">{{ $flight->flight_number }}</h3>
                                <span class="mt-3 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ $availableSeats }} seats ready</span>
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
                                    <p class="text-sm text-slate-500">Starting fare</p>
                                    <p class="mt-2 text-4xl font-bold text-[#0f3f78]">Rp{{ number_format((float) $flight->price, 0, ',', '.') }}</p>
                                </div>
                                <a href="{{ route('flights.show', $flight) }}" class="portal-btn-gold w-full justify-center">View Detail</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="portal-card text-center text-slate-600">No featured flights available at the moment.</div>
                @endforelse
            </div>
        </section>
    </section>
@endsection
