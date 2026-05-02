@extends('layouts.admin')

@section('title', 'Detail Pengguna | Cakrawala')
@section('page-title', 'Detail Pengguna')

@section('content')
    <section class="space-y-6">
        <article class="admin-ops-detail-hero">
            <div class="admin-section-head">
                <div class="max-w-3xl">
                    <p class="admin-section-kicker">Profil pengguna</p>
                    <h2 class="admin-section-title">{{ $user->name }}</h2>
                    <p class="admin-section-copy">Detail profil, booking, passenger, dan aktivitas pembayaran user dipusatkan dalam satu tampilan yang lebih mudah dibaca.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="admin-chip">{{ $user->roleLabel() }}</span>
                    @if (auth()->user()->canManageUsers())
                        <a href="{{ route('admin.users.edit', $user) }}" class="admin-btn-secondary">Edit Pengguna</a>
                    @endif
                </div>
            </div>

            <div class="mt-5 grid gap-4 xl:grid-cols-[1.35fr_.95fr]">
                <div class="admin-ops-inline-grid">
                    <div class="admin-ops-info-card"><p class="admin-info-label">Nama</p><p class="admin-info-value">{{ $user->name }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Email</p><p class="admin-info-value break-all">{{ $user->email }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Telepon</p><p class="admin-info-value">{{ $user->phone ?: '-' }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Terdaftar</p><p class="admin-info-value">{{ $user->created_at?->format('d M Y H:i') }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Peran</p><p class="admin-info-value">{{ $user->roleLabel() }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">ID Karyawan</p><p class="admin-info-value">{{ $user->employee_id ?: '-' }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Departemen</p><p class="admin-info-value">{{ $user->department ?: '-' }}</p></div>
                    <div class="admin-ops-info-card"><p class="admin-info-label">Jabatan</p><p class="admin-info-value">{{ $user->job_title ?: '-' }}</p></div>
                </div>

                <aside class="grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
                    <article class="admin-ops-sidecard">
                        <p class="admin-info-label">Total Penumpang</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ $user->passengers_count }}</p>
                    </article>
                    <article class="admin-ops-sidecard">
                        <p class="admin-info-label">Total Booking</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ $user->bookings_count }}</p>
                    </article>
                    <article class="admin-ops-sidecard">
                        <p class="admin-info-label">Total Tiket</p>
                        <p class="mt-2 text-2xl font-bold text-[#c2410c]">{{ $ticketCount }}</p>
                    </article>
                    <article class="admin-ops-sidecard">
                        <p class="admin-section-kicker">Ringkasan pembayaran</p>
                        <p class="mt-2 text-sm text-slate-700">Lunas {{ $paymentStats['paid'] }}, Menunggu {{ $paymentStats['pending'] }}, Gagal {{ $paymentStats['failed'] }}.</p>
                    </article>
                </aside>
            </div>
        </article>

        <article class="admin-ops-table-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Manifest penumpang</p>
                    <h2 class="admin-section-title">Daftar penumpang</h2>
                </div>
            </div>
            <div class="admin-table-wrap mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Gender</th>
                            <th>Tanggal Lahir</th>
                            <th>Identitas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($passengers as $passenger)
                            <tr>
                                <td>{{ $passenger->full_name }}</td>
                                <td>{{ ucfirst($passenger->gender) }}</td>
                                <td>{{ $passenger->birth_date?->format('d M Y') }}</td>
                                <td>{{ $passenger->identity_number ?: ($passenger->passport_number ?: '-') }}</td>
                                <td><a href="{{ route('admin.passengers.show', $passenger) }}" class="admin-btn-secondary">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-slate-500">Belum ada passenger.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="admin-ops-table-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Permintaan terbaru</p>
                    <h2 class="admin-section-title">Booking terbaru</h2>
                </div>
            </div>
            <div class="admin-table-wrap mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Penerbangan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bookings as $booking)
                            @php($latestPayment = $booking->payments->sortByDesc('created_at')->first())
                            <tr>
                                <td>{{ $booking->booking_code }}</td>
                                <td>{{ $booking->flight?->flight_number }} - {{ $booking->flight?->airline?->name }}</td>
                                <td>Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</td>
                                <td>@include('admin.partials.status-badge', ['status' => $booking->status])</td>
                                <td>@include('admin.partials.status-badge', ['status' => $latestPayment?->payment_status])</td>
                                <td><a href="{{ route('admin.bookings.show', $booking) }}" class="admin-btn-secondary">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-500">Belum ada booking.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
