@extends('layouts.admin')

@section('title', 'Permintaan Perubahan | Cakrawala')
@section('page-title', 'Permintaan Perubahan')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
            <form method="GET" class="admin-ops-filter space-y-5">
                <div>
                    <p class="admin-section-kicker">Layanan perubahan</p>
                    <h2 class="admin-section-title">Antrean refund dan perubahan</h2>
                    <p class="admin-section-copy">Kelola refund, reschedule, perbaikan nama, dan pembatalan dari pelanggan lewat panel filter yang lebih fokus.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="search">Cari</label>
                        <input id="search" name="search" value="{{ $filters['search'] }}" class="admin-field" placeholder="Booking code / user">
                    </div>
                    <div>
                        <label class="admin-label" for="type">Jenis</label>
                        <select id="type" name="type" class="admin-field">
                            <option value="">Semua jenis</option>
                            @foreach ($requestTypes as $value => $type)
                                <option value="{{ $value }}" @selected($filters['type'] === $value)>{{ $type['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-label" for="status">Status</label>
                        <select id="status" name="status" class="admin-field">
                            <option value="">Semua status</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Pisahkan antrean berdasarkan tipe atau status untuk mempercepat keputusan penanganan.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="submit" class="admin-btn-primary">Filter</button>
                        <a href="{{ route('admin.change-requests.index') }}" class="admin-btn-secondary">Atur Ulang</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-ops-hero">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Antrean permintaan</p>
                            <h2 class="admin-section-title">Daftar permintaan perubahan</h2>
                            <p class="admin-section-copy">Queue ini menggabungkan booking, user, preferred flight, dan PIC pemroses dalam satu tampilan yang lebih mudah dibaca.</p>
                        </div>
                        <span class="admin-chip">{{ $requests->total() }} permintaan</span>
                    </div>

                    <div class="admin-ops-summary-grid">
                        <article class="admin-ops-summary-card">
                            <p class="label">Permintaan terlihat</p>
                            <p class="value">{{ $requests->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Permintaan pada halaman aktif.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Tertunda / Terbuka</p>
                            <p class="value text-amber-600">{{ $requests->whereIn('status', ['pending', 'open'])->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Kasus yang masih menunggu keputusan.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Sudah diproses</p>
                            <p class="value text-emerald-700">{{ $requests->filter(fn ($item) => filled($item->processed_by))->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Request yang sudah pernah ditangani PIC.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-ops-table-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Booking</th>
                                    <th>Pengguna</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                    <th>Penerbangan Pilihan</th>
                                    <th>Diproses Oleh</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($requests as $item)
                                    <tr>
                                        <td>{{ ($requests->firstItem() ?? 1) + $loop->index }}</td>
                                        <td class="whitespace-nowrap">
                                            <a href="{{ route('admin.bookings.show', $item->booking) }}" class="font-semibold text-[#c2410c]">
                                                {{ $item->booking?->booking_code }}
                                            </a>
                                        </td>
                                        <td>{{ $item->user?->name ?: '-' }}</td>
                                        <td>{{ \App\Support\BookingChangeRequestCatalog::label($item->request_type) }}</td>
                                        <td>@include('admin.partials.status-badge', ['status' => $item->status])</td>
                                        <td>
                                            @if ($item->preferredFlight)
                                                {{ $item->preferredFlight->flight_number }}
                                                <div class="text-xs text-slate-500">
                                                    {{ $item->preferredFlight->departureAirport?->code }} - {{ $item->preferredFlight->arrivalAirport?->code }}
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $item->processedByUser?->name ?: '-' }}</td>
                                        <td><a href="{{ route('admin.change-requests.show', $item) }}" class="admin-btn-secondary">Detail</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-slate-500">Belum ada request perubahan.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $requests->links() }}
                    </div>
                </article>
            </div>
        </div>
    </section>
@endsection
