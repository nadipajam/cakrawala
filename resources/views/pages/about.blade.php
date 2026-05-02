@extends('layouts.portal')

@section('title', 'Cakrawala | About')
@section('active', 'about')

@section('content')
    <section class="space-y-6">
        <article class="portal-card">
            <div class="portal-section-head">
                <div>
                    <p class="portal-kicker">Tentang Cakrawala</p>
                    <h1 class="portal-section-title">Layanan pemesanan penerbangan yang ringkas dan mudah digunakan.</h1>
                    <p class="portal-section-copy">
                        Cakrawala dirancang untuk membantu pelanggan menemukan penerbangan, melakukan pemesanan, menyelesaikan pembayaran, dan mengakses tiket
                        dalam pengalaman yang jelas, nyaman, dan konsisten di berbagai perangkat.
                    </p>
                </div>
            </div>
        </article>

        <section class="grid gap-4 lg:grid-cols-3">
            <article class="portal-metric-card">
                <p class="portal-kicker">Jangkauan</p>
                <p class="portal-metric-value">{{ $airportCount }}</p>
                <p class="mt-2 text-slate-600">Bandara yang tersedia di jaringan rute saat ini.</p>
            </article>
            <article class="portal-metric-card">
                <p class="portal-kicker">Operasional</p>
                <p class="portal-metric-value">{{ $activeFlightCount }}</p>
                <p class="mt-2 text-slate-600">Penerbangan aktif yang siap dipesan.</p>
            </article>
            <article class="portal-metric-card">
                <p class="portal-kicker">Kemitraan</p>
                <p class="portal-metric-value">{{ $airlines->count() }}</p>
                <p class="mt-2 text-slate-600">Maskapai mitra yang tersedia di katalog penerbangan.</p>
            </article>
        </section>

        <article class="portal-card">
            <p class="portal-kicker">Maskapai mitra</p>
            <h2 class="mt-2 font-heading text-3xl font-bold text-slate-800">Maskapai Tepercaya</h2>

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
