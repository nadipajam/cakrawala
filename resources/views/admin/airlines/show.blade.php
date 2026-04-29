@extends('layouts.admin')

@section('title', 'Airline Detail | Cakrawala')
@section('page-title', 'Airline Detail')

@section('content')
    <section class="space-y-6">
        <article class="admin-hero-card">
            <div class="admin-section-head">
                <div class="max-w-3xl">
                    <p class="admin-section-kicker">Airline Overview</p>
                    <h2 class="admin-section-title">{{ $airline->name }}</h2>
                    <p class="admin-section-copy">Gunakan tampilan ini untuk membaca identitas brand, volume booking, armada, dan jadwal terkait dalam satu layar.</p>
                </div>
                <span class="admin-chip">{{ $airline->code }}</span>
            </div>

            <div class="mt-5 grid gap-4 xl:grid-cols-[1.35fr_.95fr]">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="admin-info-card">
                        <p class="admin-info-label">Logo</p>
                        @if ($airline->logo)
                            <img src="{{ str_starts_with($airline->logo, 'http') ? $airline->logo : asset('storage/'.$airline->logo) }}" alt="{{ $airline->name }}" class="mt-2 h-12 w-12 rounded-full object-cover">
                        @else
                            <p class="admin-info-value">-</p>
                        @endif
                    </div>
                    <div class="admin-info-card"><p class="admin-info-label">Code</p><p class="admin-info-value">{{ $airline->code }}</p></div>
                    <div class="admin-info-card"><p class="admin-info-label">Name</p><p class="admin-info-value">{{ $airline->name }}</p></div>
                    <div class="admin-info-card"><p class="admin-info-label">Total Bookings</p><p class="admin-info-value">{{ $bookingCount }}</p></div>
                </div>

                @if ($airline->description)
                    <article class="admin-list-card">
                        <p class="admin-section-kicker">Description</p>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $airline->description }}</p>
                    </article>
                @endif
            </div>
        </article>

        <div class="grid gap-6 lg:grid-cols-2">
            <article class="admin-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Fleet</p>
                        <h2 class="admin-section-title">Airplanes</h2>
                    </div>
                    <span class="admin-chip">{{ $airline->airplanes_count }}</span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($airline->airplanes as $airplane)
                        <a href="{{ route('admin.airplanes.show', $airplane) }}" class="admin-list-card block">
                            <p class="font-semibold text-slate-800">{{ $airplane->model }}</p>
                            <p class="mt-1 text-sm text-slate-500">Reg: {{ $airplane->registration_number }} | Capacity: {{ $airplane->capacity }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada airplane.</p>
                    @endforelse
                </div>
            </article>

            <article class="admin-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Traffic</p>
                        <h2 class="admin-section-title">Recent flights</h2>
                    </div>
                    <span class="admin-chip">{{ $airline->flights_count }}</span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($airline->flights as $flight)
                        <a href="{{ route('admin.flights.show', $flight) }}" class="admin-list-card block">
                            <p class="font-semibold text-slate-800">{{ $flight->flight_number }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $flight->departureAirport?->code }} - {{ $flight->arrivalAirport?->code }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada flight.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
@endsection
