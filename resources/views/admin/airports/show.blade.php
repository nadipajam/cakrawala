@extends('layouts.admin')

@section('title', 'Airport Detail | Cakrawala')
@section('page-title', 'Airport Detail')

@section('content')
    <section class="space-y-6">
        <article class="admin-ops-detail-hero">
            <div class="admin-section-head">
                <div class="max-w-3xl">
                    <p class="admin-section-kicker">Airport Overview</p>
                    <h2 class="admin-section-title">{{ $airport->name }}</h2>
                    <p class="admin-section-copy">Gunakan tampilan ini untuk membaca identitas airport dan relasi jadwal keberangkatan maupun kedatangan dengan lebih cepat.</p>
                </div>
                <span class="admin-chip">{{ $airport->code }}</span>
            </div>

            <div class="admin-ops-inline-grid">
                <div class="admin-ops-info-card"><p class="admin-info-label">Code</p><p class="admin-info-value">{{ $airport->code }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Name</p><p class="admin-info-value">{{ $airport->name }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">City</p><p class="admin-info-value">{{ $airport->city }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Country</p><p class="admin-info-value">{{ $airport->country }}</p></div>
            </div>
        </article>

        <div class="grid gap-6 md:grid-cols-2">
            <article class="admin-ops-table-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Departure Flow</p>
                        <h2 class="admin-section-title">Departure flights</h2>
                    </div>
                    <span class="admin-chip">{{ $airport->departure_flights_count }}</span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($airport->departureFlights as $flight)
                        <a href="{{ route('admin.flights.show', $flight) }}" class="admin-list-card block">
                            <p class="font-semibold text-slate-800">{{ $flight->flight_number }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $flight->arrivalAirport?->code }} - {{ $flight->airline?->name }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada departure flight.</p>
                    @endforelse
                </div>
            </article>

            <article class="admin-ops-table-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Arrival Flow</p>
                        <h2 class="admin-section-title">Arrival flights</h2>
                    </div>
                    <span class="admin-chip">{{ $airport->arrival_flights_count }}</span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($airport->arrivalFlights as $flight)
                        <a href="{{ route('admin.flights.show', $flight) }}" class="admin-list-card block">
                            <p class="font-semibold text-slate-800">{{ $flight->flight_number }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $flight->departureAirport?->code }} - {{ $flight->airline?->name }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada arrival flight.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
@endsection
