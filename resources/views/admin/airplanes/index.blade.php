@extends('layouts.admin')

@section('title', 'Admin Airplanes | Cakrawala')
@section('page-title', 'Airplanes')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_1fr]">
            <form method="GET" class="admin-card space-y-5">
                <div>
                    <p class="admin-section-kicker">Fleet Directory</p>
                    <h2 class="admin-section-title">Filter data pesawat</h2>
                    <p class="admin-section-copy">Saring armada berdasarkan model, registrasi, atau maskapai agar kontrol kapasitas dan relasi flight lebih cepat.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="search">Search Model / Registration</label>
                        <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="Model atau registration number">
                    </div>
                    <div>
                        <label class="admin-label" for="airline_id">Airline</label>
                        <select id="airline_id" name="airline_id" class="admin-field">
                            <option value="">Semua airline</option>
                            @foreach ($airlines as $airline)
                                <option value="{{ $airline->id }}" @selected((int) $airlineId === (int) $airline->id)>{{ $airline->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Pisahkan armada per maskapai untuk memudahkan audit kursi dan kapasitas.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a class="admin-btn-secondary" href="{{ route('admin.airplanes.index') }}">Reset</a>
                        <a href="{{ route('admin.airplanes.create') }}" class="admin-btn-secondary">Tambah Airplane</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-hero-card">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Fleet Ledger</p>
                            <h2 class="admin-section-title">Daftar armada aktif</h2>
                            <p class="admin-section-copy">Model, registrasi, kapasitas, seat count, dan relasi flight kini tampil dalam struktur yang lebih padat dan jelas.</p>
                        </div>
                        <span class="admin-chip">{{ $airplanes->total() }} airplane</span>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Visible airplanes</p>
                            <p class="admin-metric-value">{{ $airplanes->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Armada pada halaman aktif.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Seat capacity</p>
                            <p class="admin-metric-value text-[#0f3f78]">{{ $airplanes->sum('capacity') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Akumulasi kapasitas armada pada halaman ini.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Linked flights</p>
                            <p class="admin-metric-value text-emerald-700">{{ $airplanes->sum('flights_count') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Total flight yang menggunakan armada ini.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Airline</th>
                                    <th>Model</th>
                                    <th>Registration</th>
                                    <th>Capacity</th>
                                    <th>Seats Count</th>
                                    <th>Flights Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($airplanes as $airplane)
                                    <tr>
                                        <td>{{ $airplane->airline?->name }}</td>
                                        <td class="font-semibold">{{ $airplane->model }}</td>
                                        <td>{{ $airplane->registration_number }}</td>
                                        <td>{{ $airplane->capacity }}</td>
                                        <td>{{ $airplane->seats_count }}</td>
                                        <td>{{ $airplane->flights_count }}</td>
                                        <td class="space-x-1">
                                            <a class="admin-btn-secondary" href="{{ route('admin.airplanes.show', $airplane) }}">Detail</a>
                                            <a class="admin-btn-secondary" href="{{ route('admin.airplanes.edit', $airplane) }}">Edit</a>
                                            <form action="{{ route('admin.airplanes.destroy', $airplane) }}" method="POST" class="inline" onsubmit="return confirm('Hapus airplane ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="admin-btn-secondary" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-slate-500">Data airplane belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $airplanes->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
