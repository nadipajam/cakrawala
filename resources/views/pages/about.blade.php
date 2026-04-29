@extends('layouts.portal')

@section('title', 'Cakrawala | About')
@section('active', 'about')

@section('content')
    <section class="space-y-6">
        <article class="portal-card">
            <div class="portal-section-head">
                <div>
                    <p class="portal-kicker">About Cakrawala</p>
                    <h1 class="portal-section-title">Responsive booking flow with a warmer airline identity.</h1>
                    <p class="portal-section-copy">
                        Cakrawala dirancang sebagai web pemesanan tiket yang terasa seperti portal maskapai modern:
                        pencarian flight yang cepat, seat map kabin yang realistis, pembayaran yang ringkas, dan e-ticket yang siap diunduh.
                    </p>
                </div>
            </div>
        </article>

        <section class="grid gap-4 lg:grid-cols-3">
            <article class="portal-metric-card">
                <p class="portal-kicker">Coverage</p>
                <p class="portal-metric-value">{{ $airportCount }}</p>
                <p class="mt-2 text-slate-600">Airports tersedia di jaringan demo domestik.</p>
            </article>
            <article class="portal-metric-card">
                <p class="portal-kicker">Operations</p>
                <p class="portal-metric-value">{{ $activeFlightCount }}</p>
                <p class="mt-2 text-slate-600">Active flights siap dipesan saat ini.</p>
            </article>
            <article class="portal-metric-card">
                <p class="portal-kicker">Partners</p>
                <p class="portal-metric-value">{{ $airlines->count() }}</p>
                <p class="mt-2 text-slate-600">Airline partners tampil pada katalog dan rute populer.</p>
            </article>
        </section>

        <article class="portal-card">
            <p class="portal-kicker">Airline partners</p>
            <h2 class="mt-2 font-heading text-3xl font-bold text-slate-800">Trusted Airlines</h2>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($airlines as $airline)
                    <div class="portal-feature-panel flex items-center gap-3 !p-4">
                        <span class="portal-brand-mark h-11 w-11 text-xs">
                            {{ strtoupper(substr($airline->code, 0, 3)) }}
                        </span>
                        <div>
                            <p class="font-semibold text-slate-800">{{ $airline->name }}</p>
                            <p class="text-sm text-slate-500">{{ strtoupper($airline->code) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>
    </section>
@endsection
