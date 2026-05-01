@extends('layouts.portal')

@section('title', 'Cakrawala | QRIS Completed')
@section('active', 'bookings')

@section('content')
    <section class="mx-auto max-w-5xl space-y-6">
        <article class="payment-success-panel text-center">
            <div class="mx-auto grid h-16 w-16 place-content-center rounded-full bg-emerald-100 text-2xl font-bold text-emerald-700">&#10003;</div>
            <p class="mt-5 booking-shell-kicker">Payment completed</p>
            <h1 class="mt-2 font-heading text-4xl font-bold text-[#0f3f78]">{{ $paymentLabel }}</h1>
            <p class="mt-3 text-lg text-slate-600">Pembayaran demo berhasil diproses otomatis dan booking siap dilanjutkan ke tahap berikutnya.</p>
            <p class="mt-2 text-sm text-slate-500">Nilai yang diterima QRIS: Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
            <p class="mt-6 text-4xl font-bold text-slate-800">Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</p>
        </article>

        <article class="portal-card">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Booking</p>
                    <p class="font-semibold text-slate-800">{{ $payment->booking->booking_code }}</p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Transaction Code</p>
                    <p class="font-semibold text-slate-800">{{ $payment->transaction_code }}</p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Passenger Account</p>
                    <p class="font-semibold text-slate-800">{{ $payment->booking->user->name }}</p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Route</p>
                    <p class="font-semibold text-slate-800">{{ $payment->booking->flight->departureAirport->code }} to {{ $payment->booking->flight->arrivalAirport->code }}</p>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <a href="{{ route('my-bookings.show', $payment->booking) }}" class="portal-btn-gold">Open Booking</a>
                <a href="{{ route('payments.show', $payment) }}" class="portal-btn-blue">Payment Detail</a>
            </div>
        </article>
    </section>
@endsection
