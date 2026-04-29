@extends('layouts.admin')

@section('title', 'Admin Change Requests | Cakrawala')
@section('page-title', 'Change Requests')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_1fr]">
            <form method="GET" class="admin-card space-y-5">
                <div>
                    <p class="admin-section-kicker">Service Desk</p>
                    <h2 class="admin-section-title">Refund dan change queue</h2>
                    <p class="admin-section-copy">Kelola request refund, reschedule, perbaikan nama, dan cancel request dari customer lewat panel filter yang lebih fokus.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="search">Search</label>
                        <input id="search" name="search" value="{{ $filters['search'] }}" class="admin-field" placeholder="Booking code / user">
                    </div>
                    <div>
                        <label class="admin-label" for="type">Type</label>
                        <select id="type" name="type" class="admin-field">
                            <option value="">Semua type</option>
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
                        <a href="{{ route('admin.change-requests.index') }}" class="admin-btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-hero-card">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Request Queue</p>
                            <h2 class="admin-section-title">Daftar request perubahan</h2>
                            <p class="admin-section-copy">Queue ini menggabungkan booking, user, preferred flight, dan PIC pemroses dalam satu tampilan yang lebih mudah dibaca.</p>
                        </div>
                        <span class="admin-chip">{{ $requests->total() }} request</span>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Visible requests</p>
                            <p class="admin-metric-value">{{ $requests->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Request pada halaman aktif.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Pending / Open</p>
                            <p class="admin-metric-value text-amber-600">{{ $requests->whereIn('status', ['pending', 'open'])->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Kasus yang masih menunggu keputusan.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Processed</p>
                            <p class="admin-metric-value text-emerald-700">{{ $requests->filter(fn ($item) => filled($item->processed_by))->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Request yang sudah pernah ditangani PIC.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Request</th>
                                    <th>Booking</th>
                                    <th>User</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Preferred Flight</th>
                                    <th>Processed By</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($requests as $item)
                                    <tr>
                                        <td>{{ ($requests->firstItem() ?? 1) + $loop->index }}</td>
                                        <td class="whitespace-nowrap">
                                            <a href="{{ route('admin.bookings.show', $item->booking) }}" class="font-semibold text-[#0f3f78]">
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
