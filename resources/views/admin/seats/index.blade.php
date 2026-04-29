@extends('layouts.admin')

@section('title', 'Admin Seats | Cakrawala')
@section('page-title', 'Seats')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_1fr]">
            <form method="GET" class="admin-card space-y-5">
                <div>
                    <p class="admin-section-kicker">Seat Directory</p>
                    <h2 class="admin-section-title">Filter data kursi</h2>
                    <p class="admin-section-copy">Saring kursi berdasarkan nomor seat, pesawat, atau class agar pengelolaan inventory kursi lebih cepat.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="search">Seat Number</label>
                        <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="Contoh: 12A">
                    </div>
                    <div>
                        <label class="admin-label" for="airplane_id">Airplane</label>
                        <select id="airplane_id" name="airplane_id" class="admin-field">
                            <option value="">Semua airplane</option>
                            @foreach ($airplanes as $airplane)
                                <option value="{{ $airplane->id }}" @selected((int) $airplaneId === (int) $airplane->id)>{{ $airplane->model }} ({{ $airplane->registration_number }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-label" for="class">Class</label>
                        <select id="class" name="class" class="admin-field">
                            <option value="">Semua class</option>
                            <option value="economy" @selected($class === 'economy')>Economy</option>
                            <option value="business" @selected($class === 'business')>Business</option>
                            <option value="first" @selected($class === 'first')>First</option>
                        </select>
                    </div>
                </div>
                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Panel ini membantu memisahkan kursi per armada dan class agar audit konfigurasi tetap rapi.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a class="admin-btn-secondary" href="{{ route('admin.seats.index') }}">Reset</a>
                        <a href="{{ route('admin.seats.create') }}" class="admin-btn-secondary">Tambah Seat</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-hero-card">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Seat Ledger</p>
                            <h2 class="admin-section-title">Daftar kursi tersimpan</h2>
                            <p class="admin-section-copy">Inventory kursi sekarang lebih mudah dipindai karena dikelompokkan bersama armada, airline, dan class.</p>
                        </div>
                        <span class="admin-chip">{{ $seats->total() }} seat</span>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Visible seats</p>
                            <p class="admin-metric-value">{{ $seats->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Kursi pada halaman aktif.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Economy</p>
                            <p class="admin-metric-value text-emerald-700">{{ $seats->where('class', 'economy')->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Seat economy pada halaman ini.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Premium mix</p>
                            <p class="admin-metric-value text-[#0f3f78]">{{ $seats->whereIn('class', ['business', 'first'])->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Seat business dan first pada halaman ini.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Airplane</th>
                                    <th>Airline</th>
                                    <th>Seat Number</th>
                                    <th>Class</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($seats as $seat)
                                    <tr>
                                        <td>{{ $seat->airplane?->model }} ({{ $seat->airplane?->registration_number }})</td>
                                        <td>{{ $seat->airplane?->airline?->name }}</td>
                                        <td class="font-semibold">{{ $seat->seat_number }}</td>
                                        <td>{{ ucfirst($seat->class) }}</td>
                                        <td>{{ $seat->created_at?->format('d M Y') }}</td>
                                        <td class="space-x-1">
                                            <a class="admin-btn-secondary" href="{{ route('admin.seats.show', $seat) }}">Detail</a>
                                            <a class="admin-btn-secondary" href="{{ route('admin.seats.edit', $seat) }}">Edit</a>
                                            <form action="{{ route('admin.seats.destroy', $seat) }}" method="POST" class="inline" onsubmit="return confirm('Hapus seat ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="admin-btn-secondary" type="submit">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-slate-500">Data seat belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $seats->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
