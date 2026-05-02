@extends('layouts.admin')

@section('title', 'Admin Passengers | Cakrawala')
@section('page-title', 'Passengers')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
            <form method="GET" class="admin-ops-filter space-y-5">
                <div>
                    <p class="admin-section-kicker">Passenger Directory</p>
                    <h2 class="admin-section-title">Filter manifest passenger</h2>
                    <p class="admin-section-copy">Cari passenger berdasarkan nama atau pemilik akun agar histori perjalanan dan data identitas lebih cepat dilacak.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label for="search" class="admin-label">Cari penumpang</label>
                        <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="Nama passenger">
                    </div>
                    <div>
                        <label for="user_id" class="admin-label">Filter User</label>
                        <select id="user_id" name="user_id" class="admin-field">
                            <option value="">Semua user</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((int) $userId === (int) $user->id)>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Gunakan filter user saat satu akun menyimpan banyak profil passenger.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a href="{{ route('admin.passengers.index') }}" class="admin-btn-secondary">Atur Ulang</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-ops-hero">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Passenger Ledger</p>
                            <h2 class="admin-section-title">Daftar passenger tersimpan</h2>
                            <p class="admin-section-copy">Semua profil passenger kini tampil bersama pemilik akun, identitas dasar, dan tanggal pembuatan.</p>
                        </div>
                        <span class="admin-chip">{{ $passengers->total() }} passenger</span>
                    </div>

                    <div class="admin-ops-summary-grid">
                        <article class="admin-ops-summary-card">
                            <p class="label">Visible passengers</p>
                            <p class="value">{{ $passengers->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Passenger pada halaman aktif.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Male</p>
                            <p class="value text-[#c2410c]">{{ $passengers->where('gender', 'male')->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Profil dengan gender male pada halaman ini.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Female</p>
                            <p class="value text-emerald-700">{{ $passengers->where('gender', 'female')->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Profil dengan gender female pada halaman ini.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-ops-table-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>User</th>
                                    <th>Full Name</th>
                                    <th>Gender</th>
                                    <th>Birth Date</th>
                                    <th>Nationality</th>
                                    <th>Dibuat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($passengers as $passenger)
                                    <tr>
                                        <td>{{ ($passengers->firstItem() ?? 1) + $loop->index }}</td>
                                        <td>{{ $passenger->user?->name }}</td>
                                        <td class="font-semibold">{{ $passenger->full_name }}</td>
                                        <td>{{ ucfirst($passenger->gender) }}</td>
                                        <td>{{ $passenger->birth_date?->format('d M Y') }}</td>
                                        <td>{{ $passenger->nationality ?: '-' }}</td>
                                        <td>{{ $passenger->created_at?->format('d M Y') }}</td>
                                        <td><a class="admin-btn-secondary" href="{{ route('admin.passengers.show', $passenger) }}">Detail</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-slate-500">Data passenger belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $passengers->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
