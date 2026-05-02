@extends('layouts.admin')

@section('title', 'Admin Airports | Cakrawala')
@section('page-title', 'Airports')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
            <form method="GET" class="admin-ops-filter space-y-5">
                <div>
                    <p class="admin-section-kicker">Airport Directory</p>
                    <h2 class="admin-section-title">Filter data bandara</h2>
                    <p class="admin-section-copy">Cari bandara berdasarkan kode, kota, atau negara agar pemetaan rute tetap cepat dan rapi.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="search">Cari</label>
                        <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="Code atau nama">
                    </div>
                    <div>
                        <label class="admin-label" for="city">City</label>
                        <input id="city" name="city" value="{{ $city }}" class="admin-field" placeholder="Kota">
                    </div>
                    <div>
                        <label class="admin-label" for="country">Country</label>
                        <input id="country" name="country" value="{{ $country }}" class="admin-field" placeholder="Negara">
                    </div>
                </div>

                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Panel filter memudahkan pemisahan airport internasional dan domestik saat data mulai besar.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a class="admin-btn-secondary" href="{{ route('admin.airports.index') }}">Atur Ulang</a>
                        <a href="{{ route('admin.airports.create') }}" class="admin-btn-secondary">Tambah Airport</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-ops-hero">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Airport Ledger</p>
                            <h2 class="admin-section-title">Daftar bandara aktif</h2>
                            <p class="admin-section-copy">Tabel airport sekarang lebih fokus ke hubungan kode, lokasi, dan volume flight.</p>
                        </div>
                        <span class="admin-chip">{{ $airports->total() }} airport</span>
                    </div>

                    <div class="admin-ops-summary-grid">
                        <article class="admin-ops-summary-card">
                            <p class="label">Visible airports</p>
                            <p class="value">{{ $airports->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Bandara pada halaman aktif.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Cities covered</p>
                            <p class="value text-[#c2410c]">{{ $airports->pluck('city')->filter()->unique()->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Jumlah kota unik pada halaman ini.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Countries covered</p>
                            <p class="value text-emerald-700">{{ $airports->pluck('country')->filter()->unique()->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Jumlah negara unik pada halaman ini.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-ops-table-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>City</th>
                                    <th>Country</th>
                                    <th>Flights Count</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($airports as $airport)
                                    <tr>
                                        <td class="font-semibold">{{ $airport->code }}</td>
                                        <td>{{ $airport->name }}</td>
                                        <td>{{ $airport->city }}</td>
                                        <td>{{ $airport->country }}</td>
                                        <td>{{ $airport->departure_flights_count + $airport->arrival_flights_count }}</td>
                                        <td class="space-x-1">
                                            <a class="admin-btn-secondary" href="{{ route('admin.airports.show', $airport) }}">Detail</a>
                                            <a class="admin-btn-secondary" href="{{ route('admin.airports.edit', $airport) }}">Edit</a>
                                            <form action="{{ route('admin.airports.destroy', $airport) }}" method="POST" class="inline" onsubmit="return confirm('Hapus airport ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="admin-btn-secondary" type="submit">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-slate-500">Data airport belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $airports->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
