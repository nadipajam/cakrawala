@extends('layouts.admin')

@section('title', 'Detail Pesawat | Cakrawala')
@section('page-title', 'Detail Pesawat')

@section('content')
    <section class="space-y-6">
        <article class="admin-ops-detail-hero">
            <div class="admin-section-head">
                <div class="max-w-3xl">
                    <p class="admin-section-kicker">Ringkasan pesawat</p>
                    <h2 class="admin-section-title">{{ $airplane->model }}</h2>
                    <p class="admin-section-copy">Detail armada, layout kabin, dan relasi jadwal dikelompokkan agar pengelolaan kursi dan kapasitas jauh lebih mudah.</p>
                </div>
                <span class="admin-chip">{{ $airplane->registration_number }}</span>
            </div>

            <div class="admin-ops-inline-grid">
                <div class="admin-ops-info-card"><p class="admin-info-label">Maskapai</p><p class="admin-info-value">{{ $airplane->airline?->name }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Model</p><p class="admin-info-value">{{ $airplane->model }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Registrasi</p><p class="admin-info-value">{{ $airplane->registration_number }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Kapasitas</p><p class="admin-info-value">{{ $airplane->capacity }}</p></div>
            </div>

            @if ($airplane->description)
                <div class="admin-ops-sidecard mt-4">
                    <p class="admin-section-kicker">Deskripsi</p>
                    <p class="mt-3 text-sm leading-6 text-slate-600">{{ $airplane->description }}</p>
                </div>
            @endif

            <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_280px]">
                <form method="POST" action="{{ route('admin.airplanes.generate-seats', $airplane) }}" class="admin-ops-sidecard grid gap-4">
                    @csrf
                    <div>
                        <h2 class="font-heading text-xl font-bold text-slate-800">Buat tata letak kabin</h2>
                        <p class="mt-1 text-sm text-slate-500">Buat kombinasi class dalam satu pesawat. Layout default: First 1-1, Business 2-2, Economy 3-3.</p>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="admin-label" for="first_rows">Baris First Class</label>
                            <input id="first_rows" name="first_rows" type="number" min="0" value="{{ old('first_rows', $cabinSummary['first']['row_count']) }}" class="admin-field">
                        </div>
                        <div>
                            <label class="admin-label" for="business_rows">Baris Business Class</label>
                            <input id="business_rows" name="business_rows" type="number" min="0" value="{{ old('business_rows', $cabinSummary['business']['row_count']) }}" class="admin-field">
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                        <input type="checkbox" name="reset" value="1" class="rounded border-slate-300 text-[#c2410c]"> Atur ulang kursi yang ada dan bangun ulang kabin
                    </label>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Buat Kursi</button>
                        <span class="text-sm text-slate-500">Reset hanya bisa dipakai untuk pesawat yang belum punya histori booking.</span>
                    </div>
                </form>

                <div class="grid gap-3">
                    @foreach ($cabinSummary as $summary)
                        <div class="admin-ops-sidecard">
                            <p class="admin-info-label">{{ $summary['label'] }}</p>
                            <p class="mt-2 text-lg font-semibold text-slate-800">{{ $summary['seat_count'] }} kursi</p>
                            <p class="text-sm text-slate-500">{{ $summary['row_count'] }} baris</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-2">
            <article class="admin-ops-table-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Tata letak kabin</p>
                        <h2 class="admin-section-title">Peta kursi</h2>
                    </div>
                    <span class="admin-chip">{{ $airplane->seats_count }} kursi</span>
                </div>

                <div class="mt-4 space-y-5">
                    @forelse ($seatMap['classes'] as $classKey => $seatClass)
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $seatClass['label'] }}</p>
                                    <p class="text-sm text-slate-500">{{ $seatClass['description'] }}</p>
                                </div>
                                <span class="admin-badge admin-badge-default">{{ $seatClass['total_count'] }} kursi</span>
                            </div>
                            @include('partials.seat-map', ['seatClass' => $seatClass, 'interactive' => false, 'classKey' => $classKey])
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada seat.</p>
                    @endforelse
                </div>
            </article>

            <article class="admin-ops-table-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Penggunaan penerbangan</p>
                        <h2 class="admin-section-title">Penerbangan terkait</h2>
                    </div>
                    <span class="admin-chip">{{ $airplane->flights_count }}</span>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($airplane->flights as $flight)
                        <a href="{{ route('admin.flights.show', $flight) }}" class="admin-list-card block">
                            <p class="font-semibold text-slate-800">{{ $flight->flight_number }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ $flight->departureAirport?->code }} - {{ $flight->arrivalAirport?->code }}</p>
                        </a>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada flight terkait.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
@endsection
