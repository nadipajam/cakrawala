@extends('layouts.admin')

@section('title', 'Admin Flights | Cakrawala')
@section('page-title', 'Flights')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_1fr]">
            <form method="GET" class="admin-card space-y-5">
                <div>
                    <p class="admin-section-kicker">Flight Desk</p>
                    <h2 class="admin-section-title">Filter jadwal penerbangan</h2>
                    <p class="admin-section-copy">Gunakan panel samping untuk menyaring jadwal berdasarkan nomor flight, tanggal, rute, airline, dan status operasional.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="search">Flight Number</label>
                        <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="Contoh: GA123">
                    </div>
                    <div>
                        <label class="admin-label" for="date">Date</label>
                        <input id="date" name="date" type="date" value="{{ $date }}" class="admin-field">
                    </div>
                    <div>
                        <label class="admin-label" for="route">Route Code</label>
                        <input id="route" name="route" value="{{ $route }}" class="admin-field" placeholder="CGK / DPS">
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
                    <div>
                        <label class="admin-label" for="status">Status</label>
                        <select id="status" name="status" class="admin-field">
                            <option value="">Semua status</option>
                            <option value="scheduled" @selected($status === 'scheduled')>Scheduled</option>
                            <option value="delayed" @selected($status === 'delayed')>Delayed</option>
                            <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                            <option value="completed" @selected($status === 'completed')>Completed</option>
                        </select>
                    </div>
                </div>

                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Panel ini dipisahkan dari tabel agar pengecekan jadwal tetap nyaman di mobile, tablet, dan desktop.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a href="{{ route('admin.flights.index') }}" class="admin-btn-secondary">Reset</a>
                        @if (auth()->user()->canManageMasterData())
                            <a href="{{ route('admin.flights.create') }}" class="admin-btn-secondary">Tambah Flight</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-hero-card">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Flight Monitor</p>
                            <h2 class="admin-section-title">Daftar jadwal aktif</h2>
                            <p class="admin-section-copy">Pantau rute, harga, okupansi kursi, dan status penerbangan dalam satu canvas kerja.</p>
                        </div>
                        <span class="admin-chip">{{ $flights->total() }} flight</span>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Visible schedules</p>
                            <p class="admin-metric-value">{{ $flights->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Jumlah jadwal pada halaman aktif.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Scheduled</p>
                            <p class="admin-metric-value text-emerald-700">{{ $flights->where('status', 'scheduled')->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Flight berstatus scheduled pada halaman ini.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Delayed / Cancelled</p>
                            <p class="admin-metric-value text-amber-600">{{ $flights->whereIn('status', ['delayed', 'cancelled'])->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Jadwal yang perlu perhatian operasional.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-card">
                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Flight Number</th>
                                    <th>Airline</th>
                                    <th>Route</th>
                                    <th>Departure</th>
                                    <th>Arrival</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Seats</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($flights as $flight)
                                    <tr>
                                        <td class="font-semibold">{{ $flight->flight_number }}</td>
                                        <td>{{ $flight->airline?->name }}</td>
                                        <td>{{ $flight->departureAirport?->code }} - {{ $flight->arrivalAirport?->code }}</td>
                                        <td>{{ $flight->departure_time?->format('d M Y H:i') }}</td>
                                        <td>{{ $flight->arrival_time?->format('d M Y H:i') }}</td>
                                        <td>Rp{{ number_format((float) $flight->price, 0, ',', '.') }}</td>
                                        <td>@include('admin.partials.status-badge', ['status' => $flight->status])</td>
                                        <td>{{ $flight->booked_seats }} / {{ $flight->airplane?->capacity }}</td>
                                        <td class="space-x-1">
                                            <a href="{{ route('admin.flights.show', $flight) }}" class="admin-btn-secondary">Detail</a>
                                            @if (auth()->user()->canManageMasterData())
                                                <a href="{{ route('admin.flights.edit', $flight) }}" class="admin-btn-secondary">Edit</a>
                                                <form action="{{ route('admin.flights.destroy', $flight) }}" method="POST" class="inline" onsubmit="return confirm('Hapus flight ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="admin-btn-secondary" type="submit">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-slate-500">Data flight belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $flights->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
