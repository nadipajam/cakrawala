@extends('layouts.admin')

@section('title', 'Admin Bookings | Cakrawala')
@section('page-title', 'Bookings')

@section('content')
    @php($hasFilters = filled($search) || filled($status) || filled($date) || filled($user) || filled($flight) || filled($paymentStatus))
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_1fr]">
            <form method="GET" class="admin-card space-y-5">
                <div class="space-y-3">
                    <p class="admin-section-kicker">Booking Console</p>
                    <h2 class="admin-section-title">Filter booking activity</h2>
                    <p class="admin-section-copy">Semua filter ditempatkan di panel samping agar tabel tetap fokus dibaca saat dipakai di desktop, tablet, maupun mobile.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="search">Booking Code</label>
                        <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="BK-...">
                    </div>
                    <div>
                        <label class="admin-label" for="status">Booking Status</label>
                        <select id="status" name="status" class="admin-field">
                            <option value="">Semua</option>
                            <option value="pending" @selected($status === 'pending')>Pending</option>
                            <option value="confirmed" @selected($status === 'confirmed')>Confirmed</option>
                            <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                            <option value="completed" @selected($status === 'completed')>Completed</option>
                        </select>
                    </div>
                    <div>
                        <label class="admin-label" for="payment_status">Payment Status</label>
                        <select id="payment_status" name="payment_status" class="admin-field">
                            <option value="">Semua</option>
                            <option value="pending" @selected($paymentStatus === 'pending')>Pending</option>
                            <option value="paid" @selected($paymentStatus === 'paid')>Paid</option>
                            <option value="failed" @selected($paymentStatus === 'failed')>Failed</option>
                            <option value="refunded" @selected($paymentStatus === 'refunded')>Refunded</option>
                        </select>
                    </div>
                    <div>
                        <label class="admin-label" for="date">Tanggal Booking</label>
                        <input id="date" name="date" type="date" value="{{ $date }}" class="admin-field">
                    </div>
                    <div>
                        <label class="admin-label" for="user">User</label>
                        <input id="user" name="user" value="{{ $user }}" class="admin-field" placeholder="Nama user">
                    </div>
                    <div>
                        <label class="admin-label" for="flight">Flight</label>
                        <input id="flight" name="flight" value="{{ $flight }}" class="admin-field" placeholder="Flight number">
                    </div>
                </div>

                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Hasil tabel tetap menyimpan parameter filter saat pagination agar workflow admin tidak terputus.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a href="{{ route('admin.bookings.index') }}" class="admin-btn-secondary">Reset</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-hero-card">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Booking Records</p>
                            <h2 class="admin-section-title">Daftar booking terbaru</h2>
                            <p class="admin-section-copy">Lihat user, rute, status booking, dan status pembayaran dalam satu kanvas yang lebih padat namun tetap mudah dipindai.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="admin-chip">{{ $bookings->total() }} booking ditemukan</span>
                            @if ($hasFilters)
                                <span class="admin-chip">Filter aktif</span>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Visible records</p>
                            <p class="admin-metric-value">{{ $bookings->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Jumlah row pada halaman aktif.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Pending focus</p>
                            <p class="admin-metric-value text-amber-600">{{ $bookings->where('status', 'pending')->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Booking pending di halaman saat ini.</p>
                        </article>
                        <article class="admin-metric-tile">
                            <p class="admin-metric-label">Confirmed focus</p>
                            <p class="admin-metric-value text-emerald-700">{{ $bookings->where('status', 'confirmed')->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Booking confirmed di halaman saat ini.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-card space-y-4">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Booking Records</p>
                            <h2 class="admin-section-title">Booking activity table</h2>
                            <p class="admin-section-copy">Detail user, rute, expiry, dan nilai transaksi dari setiap booking yang masuk.</p>
                        </div>
                    </div>

                    <div class="admin-table-wrap">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Booking Code</th>
                                    <th>User</th>
                                    <th>Flight</th>
                                    <th>Route</th>
                                    <th>Passengers</th>
                                    <th>Total Price</th>
                                    <th>Status</th>
                                    <th>Expired At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($bookings as $booking)
                                    @php($latestPayment = $booking->payments->sortByDesc('created_at')->first())
                                    <tr>
                                        <td class="whitespace-nowrap font-semibold">{{ $booking->booking_code }}</td>
                                        <td>
                                            <div class="min-w-[180px]">
                                                <p class="font-semibold text-slate-800">{{ $booking->user?->name ?: '-' }}</p>
                                                <p class="mt-1 break-all text-xs text-slate-500">{{ $booking->user?->email ?: 'Tidak ada email' }}</p>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="min-w-[150px]">
                                                <p class="font-semibold text-slate-800">{{ $booking->flight?->flight_number ?: '-' }}</p>
                                                <p class="mt-1 text-xs text-slate-500">{{ $booking->flight?->airline?->name ?: 'Airline tidak tersedia' }}</p>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap">{{ $booking->flight?->departureAirport?->code }} - {{ $booking->flight?->arrivalAirport?->code }}</td>
                                        <td class="whitespace-nowrap">{{ $booking->total_passengers }}</td>
                                        <td class="whitespace-nowrap font-semibold text-slate-800">Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</td>
                                        <td>
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Booking</span>
                                                    @include('admin.partials.status-badge', ['status' => $booking->status])
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-400">Payment</span>
                                                    @include('admin.partials.status-badge', ['status' => $latestPayment?->payment_status])
                                                </div>
                                            </div>
                                        </td>
                                        <td class="whitespace-nowrap">{{ $booking->expired_at?->format('d M Y H:i') ?: '-' }}</td>
                                        <td class="whitespace-nowrap"><a href="{{ route('admin.bookings.show', $booking) }}" class="admin-btn-secondary">Detail</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-slate-500">Data booking belum tersedia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $bookings->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
