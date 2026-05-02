@extends('layouts.portal')

@section('title', 'Cakrawala | Dashboard')

@section('content')
    <section class="space-y-6">
        <article class="support-hero-panel">
            <p class="booking-shell-kicker">Akun pelanggan</p>
            <h1 class="booking-shell-title">Dashboard Anda siap digunakan.</h1>
            <p class="booking-shell-copy">Anda sudah login dan bisa melanjutkan ke booking, pembayaran, atau tiket dari menu navigasi.</p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('my-bookings.index') }}" class="portal-btn-gold">Buka Booking Saya</a>
                <a href="{{ route('flights.index') }}" class="portal-btn-blue">Cari Penerbangan</a>
            </div>
        </article>
    </section>
@endsection
