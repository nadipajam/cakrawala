@extends('layouts.admin')

@section('title', 'Admin Pengguna | Cakrawala')
@section('page-title', 'Pengguna')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
            <form method="GET" class="admin-ops-filter space-y-5">
                <div>
                    <p class="admin-section-kicker">Direktori pengguna</p>
                    <h2 class="admin-section-title">Filter akun dan backoffice</h2>
                    <p class="admin-section-copy">Cari pengguna berdasarkan identitas dasar, peran, dan departemen agar monitoring pelanggan serta tim internal tetap rapi.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="search">Cari nama / email</label>
                        <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="Cari nama, email, telepon, atau employee ID">
                    </div>
                    <div>
                        <label class="admin-label" for="role">Peran</label>
                        <select id="role" name="role" class="admin-field">
                            <option value="">Semua peran</option>
                            @foreach ($roleOptions as $roleKey => $roleOption)
                                <option value="{{ $roleKey }}" @selected($role === $roleKey)>{{ $roleOption['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-label" for="department">Departemen</label>
                        <input id="department" name="department" value="{{ $department }}" class="admin-field" placeholder="Ops, Finance, Revenue">
                    </div>
                </div>

                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Pisahkan user customer dan backoffice dengan role atau departemen agar list tetap mudah dipindai.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a href="{{ route('admin.users.index') }}" class="admin-btn-secondary">Atur Ulang</a>
                        @if (auth()->user()->canManageUsers())
                        <a href="{{ route('admin.users.create') }}" class="admin-btn-secondary">Tambah Pengguna</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-ops-hero">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Ringkasan pengguna</p>
                            <h2 class="admin-section-title">Daftar akun terdaftar</h2>
                            <p class="admin-section-copy">Tampilkan akun customer maupun backoffice lengkap dengan statistik passenger dan booking langsung dari satu kanvas.</p>
                        </div>
                        <span class="admin-chip">{{ $users->total() }} user</span>
                    </div>

                    <div class="admin-ops-summary-grid">
                        <article class="admin-ops-summary-card">
                            <p class="label">Akun terlihat</p>
                            <p class="value">{{ $users->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Jumlah akun pada halaman aktif.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Akun backoffice</p>
                            <p class="value text-[#c2410c]">{{ $users->filter(fn ($user) => method_exists($user, 'isCustomer') ? ! $user->isCustomer() : true)->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Akun non-customer pada halaman ini.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Pelanggan</p>
                            <p class="value text-emerald-700">{{ $users->filter(fn ($user) => method_exists($user, 'isCustomer') ? $user->isCustomer() : false)->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Akun customer pada halaman ini.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-ops-table-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama</th>
                                    <th>Email</th>
                                    <th>Telepon</th>
                                    <th>Peran</th>
                                    <th>Departemen</th>
                                    <th>Penumpang</th>
                                    <th>Bookings</th>
                                    <th>Terdaftar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($users as $user)
                                    <tr>
                                        <td>{{ ($users->firstItem() ?? 1) + $loop->index }}</td>
                                        <td class="font-semibold">{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?: '-' }}</td>
                                        <td>{{ $user->roleLabel() }}</td>
                                        <td>{{ $user->department ?: '-' }}</td>
                                        <td>{{ $user->passengers_count }}</td>
                                        <td>{{ $user->bookings_count }}</td>
                                        <td>{{ $user->created_at?->format('d M Y') }}</td>
                                        <td class="space-x-1 whitespace-nowrap">
                                            <a href="{{ route('admin.users.show', $user) }}" class="admin-btn-secondary">Detail</a>
                                            @if (auth()->user()->canManageUsers())
                                                <a href="{{ route('admin.users.edit', $user) }}" class="admin-btn-secondary">Edit</a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center text-slate-500">Data user belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $users->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
