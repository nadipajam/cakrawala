@extends('layouts.portal')

@section('title', 'Cakrawala | Detail Penerbangan')
@section('active', 'flights')

@section('content')
    @php
        $durationMinutes = $flight->departure_time->diffInMinutes($flight->arrival_time);
        $durationHours = intdiv($durationMinutes, 60);
        $durationRemain = $durationMinutes % 60;
        $classKeys = $seatMap['class_keys'];
        $initialClass = in_array($selectedClass, $classKeys, true) ? $selectedClass : ($classKeys[0] ?? 'economy');
    @endphp

    <section x-data="{ selectedClass: '{{ $initialClass }}' }" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6">
            <article class="portal-card">
                <div class="portal-section-head">
                    <div>
                        <p class="portal-kicker">{{ $flight->airline->name }}</p>
                        <h1 class="portal-section-title">{{ $flight->flight_number }}</h1>
                        <p class="portal-section-copy">
                            {{ $flight->departureAirport->city }} ({{ $flight->departureAirport->code }})
                            &rarr; {{ $flight->arrivalAirport->city }} ({{ $flight->arrivalAirport->code }})
                        </p>
                    </div>
                    @if ($flight->status === 'scheduled')
                        <span class="portal-status-confirmed self-start">Tepat Waktu</span>
                    @elseif ($flight->status === 'delayed')
                        <span class="portal-status-pending self-start">Tertunda</span>
                    @elseif ($flight->status === 'cancelled')
                        <span class="portal-status-cancelled self-start">Dibatalkan</span>
                    @else
                        <span class="portal-status-default self-start">{{ ucfirst($flight->status) }}</span>
                    @endif
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="portal-metric-card">
                        <p class="portal-kicker">Departure</p>
                        <p class="mt-3 text-lg font-semibold text-slate-800">{{ $flight->departure_time->format('d M Y H:i') }}</p>
                    </div>
                    <div class="portal-metric-card">
                        <p class="portal-kicker">Arrival</p>
                        <p class="mt-3 text-lg font-semibold text-slate-800">{{ $flight->arrival_time->format('d M Y H:i') }}</p>
                    </div>
                    <div class="portal-metric-card">
                        <p class="portal-kicker">Duration</p>
                        <p class="mt-3 text-lg font-semibold text-slate-800">{{ $durationHours }}h {{ $durationRemain }}m</p>
                    </div>
                    <div class="portal-metric-card">
                        <p class="portal-kicker">Aircraft</p>
                        <p class="mt-3 text-lg font-semibold text-slate-800">{{ $flight->airplane->model }}</p>
                    </div>
                </div>
            </article>

            <article class="portal-card">
                <div class="portal-section-head">
                    <div>
                        <p class="portal-kicker">Cabin classes</p>
                        <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Ketersediaan Kursi</h2>
                        <p class="portal-section-copy">Pilih kabin yang ingin dipreview. Layout kursi mengikuti struktur yang sama dengan halaman booking.</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($seatMap['classes'] as $classKey => $seatClass)
                        <button
                            type="button"
                            @click="selectedClass = '{{ $classKey }}'"
                            class="portal-card-soft text-left transition duration-200"
                            :class="selectedClass === '{{ $classKey }}' ? 'ring-2 ring-[#c2410c] border-[#c2410c] bg-white' : 'hover:bg-white'"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-lg font-semibold text-slate-800">{{ $seatClass['label'] }}</p>
                                    <p class="mt-1 text-sm text-slate-500">{{ $seatClass['description'] }}</p>
                                </div>
                                <span class="portal-status-default self-start">{{ $availableSeatCounts[$classKey] ?? 0 }}</span>
                            </div>
                            <p class="mt-4 text-2xl font-bold text-[#c2410c]">Rp{{ number_format((float) ($classPrices[$classKey] ?? 0), 0, ',', '.') }}</p>
                        </button>
                    @endforeach
                </div>

                <div class="mt-5 space-y-4">
                    @foreach ($seatMap['classes'] as $classKey => $seatClass)
                        <div x-show="selectedClass === '{{ $classKey }}'" x-cloak class="portal-surface-muted space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="portal-kicker">{{ $seatClass['short_label'] }}</p>
                                    <h3 class="mt-2 text-2xl font-bold text-slate-800">{{ $seatClass['label'] }}</h3>
                                </div>
                                <span class="portal-status-confirmed">{{ $seatClass['available_count'] }} kursi tersedia</span>
                            </div>

                            @include('partials.seat-map', ['seatClass' => $seatClass, 'interactive' => false, 'classKey' => $classKey])
                        </div>
                    @endforeach
                </div>
            </article>
        </div>

        <aside class="portal-side-panel">
            <p class="portal-kicker">Booking panel</p>
            <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Siap Booking?</h2>
            <p class="mt-2 text-sm leading-7 text-slate-600">Pilih kelas, cek ketersediaan kursi, lalu lanjutkan ke booking untuk menentukan penumpang dan kursi.</p>

            <div class="mt-5 space-y-3">
                @foreach ($seatMap['classes'] as $classKey => $seatClass)
                    <div x-show="selectedClass === '{{ $classKey }}'" x-cloak class="space-y-3">
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Kabin</p>
                            <p class="text-xl font-semibold text-slate-800">{{ $seatClass['label'] }}</p>
                        </div>
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Kursi tersedia</p>
                            <p class="text-xl font-semibold text-slate-800">{{ $availableSeatCounts[$classKey] ?? 0 }} kursi</p>
                        </div>
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Harga per penumpang</p>
                            <p class="text-3xl font-bold text-[#c2410c]">Rp{{ number_format((float) ($classPrices[$classKey] ?? 0), 0, ',', '.') }}</p>
                        </div>
                    </div>
                @endforeach

                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Base fare</p>
                    <p class="text-xl font-semibold text-slate-800">Rp{{ number_format((float) $flight->price, 0, ',', '.') }}</p>
                </div>
            </div>

            @auth
                @if (auth()->user()->isCustomer())
                    @foreach ($seatMap['classes'] as $classKey => $seatClass)
                        <a
                            x-show="selectedClass === '{{ $classKey }}'"
                            x-cloak
                            href="{{ route('booking.create', ['flight' => $flight->id, 'class' => $classKey]) }}"
                            class="portal-btn-gold mt-6 w-full justify-center"
                        >
                            Booking {{ $seatClass['short_label'] }}
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('admin.dashboard') }}" class="portal-btn-blue mt-6 w-full justify-center">
                        Buka Dashboard Admin
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="portal-btn-gold mt-6 w-full justify-center">
                    Masuk untuk Booking
                </a>
            @endauth
        </aside>
    </section>
@endsection
