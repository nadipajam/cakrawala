@extends('layouts.admin')

@section('title', 'Admin Airlines | Cakrawala')
@section('page-title', 'Airlines')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
            <form method="GET" class="admin-ops-filter space-y-5">
                <div>
                    <p class="admin-section-kicker">Airline Directory</p>
                    <h2 class="admin-section-title">Filter data maskapai</h2>
                    <p class="admin-section-copy">Cari maskapai berdasarkan nama atau kode, lalu pantau hubungan armada dan jadwal aktifnya dari satu canvas.</p>
                </div>

                <div>
                    <label class="admin-label" for="search">Cari nama / kode</label>
                    <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="Nama atau kode airline">
                </div>

                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Panel ini menjaga tabel tetap fokus dan membantu saat data maskapai bertambah banyak.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a href="{{ route('admin.airlines.index') }}" class="admin-btn-secondary">Atur Ulang</a>
                        <a href="{{ route('admin.airlines.create') }}" class="admin-btn-secondary">Tambah Airline</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-ops-hero">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Airline Ledger</p>
                            <h2 class="admin-section-title">Daftar maskapai aktif</h2>
                            <p class="admin-section-copy">Logo, kode, armada, dan volume flight ditata ulang agar perbandingan antar maskapai lebih cepat.</p>
                        </div>
                        <span class="admin-chip">{{ $airlines->total() }} airline</span>
                    </div>

                    <div class="admin-ops-summary-grid">
                        <article class="admin-ops-summary-card">
                            <p class="label">Visible airlines</p>
                            <p class="value">{{ $airlines->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Maskapai pada halaman aktif.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Airplanes</p>
                            <p class="value text-[#c2410c]">{{ $airlines->sum('airplanes_count') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Total armada yang tampil pada halaman ini.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Flights</p>
                            <p class="value text-emerald-700">{{ $airlines->sum('flights_count') }}</p>
                            <p class="mt-2 text-sm text-slate-500">Total jadwal terkait pada halaman ini.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-ops-table-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Logo</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Airplanes Count</th>
                                    <th>Flights Count</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($airlines as $airline)
                                    <tr>
                                        <td>
                                            @if ($airline->logo)
                                                <img src="{{ str_starts_with($airline->logo, 'http') ? $airline->logo : asset('storage/'.$airline->logo) }}" alt="{{ $airline->name }}" class="h-8 w-8 rounded-full object-cover">
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="font-semibold">{{ $airline->code }}</td>
                                        <td>{{ $airline->name }}</td>
                                        <td>{{ $airline->airplanes_count }}</td>
                                        <td>{{ $airline->flights_count }}</td>
                                        <td class="space-x-1">
                                            <a class="admin-btn-secondary" href="{{ route('admin.airlines.show', $airline) }}">Detail</a>
                                            <a class="admin-btn-secondary" href="{{ route('admin.airlines.edit', $airline) }}">Edit</a>
                                            <form action="{{ route('admin.airlines.destroy', $airline) }}" method="POST" class="inline" onsubmit="return confirm('Hapus airline ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="admin-btn-secondary" type="submit">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-slate-500">Data airline belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $airlines->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
