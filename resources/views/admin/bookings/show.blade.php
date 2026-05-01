@extends('layouts.admin')

@section('title', 'Booking Detail | Cakrawala')
@section('page-title', 'Booking Detail')

@section('content')
    @php($latestPayment = $booking->payments->sortByDesc('created_at')->first())
    <section class="space-y-6">
        <article class="admin-ops-detail-hero space-y-5">
            <div class="admin-section-head">
                <div class="min-w-0 max-w-3xl">
                    <p class="admin-section-kicker">Booking Overview</p>
                    <h2 class="admin-section-title break-all">{{ $booking->booking_code }}</h2>
                    <p class="admin-section-copy">Ringkasan penumpang, jadwal, status pembayaran, check-in, add-on, dan request perubahan ditempatkan di halaman kerja yang lebih tersegmentasi.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @include('admin.partials.status-badge', ['status' => $booking->status])
                    @include('admin.partials.status-badge', ['status' => $latestPayment?->payment_status])
                </div>
            </div>

            <div class="grid gap-4 xl:grid-cols-[1.35fr_.95fr]">
                <div class="admin-ops-inline-grid">
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">User</p>
                        <p class="admin-info-value break-words">{{ $booking->user?->name ?: '-' }}</p>
                        <p class="mt-2 break-all text-sm text-slate-500">{{ $booking->user?->email ?: 'Email tidak tersedia' }}</p>
                    </article>
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Flight</p>
                        <p class="admin-info-value">{{ $booking->flight?->flight_number ?: '-' }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $booking->flight?->airline?->name ?: 'Airline tidak tersedia' }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $booking->flight?->airplane?->model ?: 'Pesawat tidak tersedia' }}</p>
                    </article>
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Route</p>
                        <p class="admin-info-value">{{ $booking->flight?->departureAirport?->code }} - {{ $booking->flight?->arrivalAirport?->code }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $booking->flight?->departureAirport?->city }} ke {{ $booking->flight?->arrivalAirport?->city }}</p>
                    </article>
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Schedule</p>
                        <p class="admin-info-value">{{ $booking->flight?->departure_time?->format('d M Y H:i') ?: '-' }}</p>
                        <p class="mt-2 text-sm text-slate-500">Tiba {{ $booking->flight?->arrival_time?->format('d M Y H:i') ?: '-' }}</p>
                    </article>
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Total Passenger</p>
                        <p class="admin-info-value">{{ $booking->total_passengers }}</p>
                        <p class="mt-2 text-sm text-slate-500">Manifest passenger</p>
                    </article>
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Total Price</p>
                        <p class="admin-info-value">Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</p>
                        <p class="mt-2 text-sm text-slate-500">Termasuk add-on aktif</p>
                    </article>
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Add-Ons</p>
                        <p class="admin-info-value">{{ $booking->addons->whereIn('status', ['selected', 'paid'])->count() }}</p>
                        <p class="mt-2 text-sm text-slate-500">Selected atau paid addon item</p>
                    </article>
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Change Requests</p>
                        <p class="admin-info-value">{{ $booking->changeRequests->count() }}</p>
                        <p class="mt-2 text-sm text-slate-500">Request perubahan terkait booking ini</p>
                    </article>
                </div>

                <aside class="space-y-4">
                    <article class="admin-ops-sidecard">
                        <p class="admin-section-kicker">Workflow</p>
                        <h3 class="mt-2 font-heading text-lg font-bold text-slate-800">Operational action</h3>
                        <div class="admin-ops-trail">
                            <div class="admin-ops-trail-item">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Booking status</p>
                                    <p class="text-sm text-slate-500">Status utama transaksi booking.</p>
                                </div>
                                <div>@include('admin.partials.status-badge', ['status' => $booking->status])</div>
                            </div>
                            <div class="admin-ops-trail-item">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Payment status</p>
                                    <p class="text-sm text-slate-500">Mengikuti transaksi pembayaran terakhir.</p>
                                </div>
                                <div>@include('admin.partials.status-badge', ['status' => $latestPayment?->payment_status])</div>
                            </div>
                            <div class="admin-ops-trail-item">
                                <div>
                                    <p class="text-sm font-semibold text-slate-700">Expired at</p>
                                    <p class="text-sm text-slate-500">Batas waktu sebelum booking hangus.</p>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">{{ $booking->expired_at?->format('d M Y H:i') ?: '-' }}</p>
                            </div>
                        </div>
                    </article>

                    @if (auth()->user()->isAdmin() || auth()->user()->isStaff())
                        <article class="admin-ops-sidecard space-y-4">
                            <div>
                                <p class="admin-section-kicker">Booking Actions</p>
                                <h3 class="mt-2 font-heading text-lg font-bold text-slate-800">Update transaksi</h3>
                                <p class="mt-2 text-sm text-slate-600">Perbarui status booking atau batalkan transaksi secara manual jika diperlukan.</p>
                            </div>
                            <form method="POST" action="{{ route('admin.bookings.status', $booking) }}" class="space-y-3">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="admin-field">
                                    <option value="pending" @selected($booking->status === 'pending')>Pending</option>
                                    <option value="confirmed" @selected($booking->status === 'confirmed')>Confirmed</option>
                                    <option value="cancelled" @selected($booking->status === 'cancelled')>Cancelled</option>
                                    <option value="completed" @selected($booking->status === 'completed')>Completed</option>
                                </select>
                                <button class="admin-btn-primary w-full" type="submit">Update Status</button>
                            </form>

                            @if ($booking->status !== 'cancelled')
                                <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" onsubmit="return confirm('Batalkan booking ini?')">
                                    @csrf
                                    <button class="admin-btn-secondary w-full" type="submit">Cancel Booking</button>
                                </form>
                            @endif
                        </article>
                    @endif
                </aside>
            </div>
        </article>

        <article class="admin-ops-table-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Passenger Manifest</p>
                    <h2 class="admin-section-title">Passenger, Seat, and Boarding</h2>
                    <p class="admin-section-copy">Status boarding diupdate per passenger agar operasional check-in lebih akurat.</p>
                </div>
                <span class="admin-chip">{{ $booking->details->count() }} passenger</span>
            </div>

            <div class="admin-table-wrap mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Passenger</th>
                            <th>Seat</th>
                            <th>Ticket</th>
                            <th>Boarding Status</th>
                            <th>Check-In Ref</th>
                            <th>Boarding Pass</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($booking->details as $detail)
                            <tr>
                                <td>{{ $detail->passenger?->full_name }}</td>
                                <td>{{ $detail->seat?->seat_number }} ({{ ucfirst($detail->seat?->class ?? '-') }})</td>
                                <td class="whitespace-nowrap">
                                    @if ($detail->ticket)
                                        <a href="{{ route('admin.tickets.show', $detail->ticket) }}" class="font-semibold text-[#0f3f78]">{{ $detail->ticket_number }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>@include('admin.partials.status-badge', ['status' => $detail->boarding_status])</td>
                                <td class="whitespace-nowrap">{{ $detail->checkin_reference ?: '-' }}</td>
                                <td class="whitespace-nowrap">
                                    @if (in_array($detail->boarding_status, ['checked_in', 'boarded'], true))
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('admin.bookings.boarding-pass.pdf', [$booking, $detail]) }}" class="admin-btn-secondary">PDF</a>
                                            <a href="{{ route('admin.bookings.boarding-pass.qr', [$booking, $detail]) }}" target="_blank" class="admin-btn-secondary">QR</a>
                                        </div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if (auth()->user()->isAdmin() || auth()->user()->isStaff())
                                        <form method="POST" action="{{ route('admin.bookings.boarding-status', [$booking, $detail]) }}" class="flex flex-wrap items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="boarding_status" class="admin-field min-w-[150px]">
                                                <option value="not_checked_in" @selected($detail->boarding_status === 'not_checked_in')>Not Checked In</option>
                                                <option value="checked_in" @selected($detail->boarding_status === 'checked_in')>Checked In</option>
                                                <option value="boarded" @selected($detail->boarding_status === 'boarded')>Boarded</option>
                                            </select>
                                            <button type="submit" class="admin-btn-secondary">Update</button>
                                        </form>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-slate-500">Detail booking kosong.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-2">
            <article class="admin-ops-table-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Ancillary</p>
                        <h2 class="admin-section-title">Booking Add-Ons</h2>
                    </div>
                    <a href="{{ route('admin.addons.index') }}" class="admin-btn-secondary">Open Add-Ons</a>
                </div>

                <div class="admin-table-wrap mt-4">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Addon</th>
                                <th>Passenger</th>
                                <th>Qty</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($booking->addons->sortByDesc('created_at') as $addon)
                                <tr>
                                    <td>{{ $addon->addon_name }}</td>
                                    <td>{{ $addon->bookingDetail?->passenger?->full_name ?: 'Booking-level' }}</td>
                                    <td>{{ $addon->quantity }}</td>
                                    <td>Rp{{ number_format((float) $addon->total_price, 0, ',', '.') }}</td>
                                    <td>@include('admin.partials.status-badge', ['status' => $addon->status])</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-slate-500">Belum ada add-on.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="admin-ops-table-card">
                <div class="admin-section-head">
                    <div>
                        <p class="admin-section-kicker">Service Desk</p>
                        <h2 class="admin-section-title">Change Requests</h2>
                    </div>
                    <a href="{{ route('admin.change-requests.index') }}" class="admin-btn-secondary">Open Queue</a>
                </div>

                <div class="admin-table-wrap mt-4">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Processed By</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($booking->changeRequests->sortByDesc('created_at') as $changeRequest)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \App\Support\BookingChangeRequestCatalog::label($changeRequest->request_type) }}</td>
                                    <td>@include('admin.partials.status-badge', ['status' => $changeRequest->status])</td>
                                    <td>{{ $changeRequest->processedByUser?->name ?: '-' }}</td>
                                    <td><a href="{{ route('admin.change-requests.show', $changeRequest) }}" class="admin-btn-secondary">Detail</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-slate-500">Belum ada request perubahan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <article class="admin-ops-table-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Payment Ledger</p>
                    <h2 class="admin-section-title">Payment List</h2>
                    <p class="admin-section-copy">Riwayat pembayaran, metode yang dipilih, dan status validasinya.</p>
                </div>
                <span class="admin-chip">{{ $booking->payments->count() }} transaksi</span>
            </div>

            <div class="admin-table-wrap mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Method</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Proof</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($booking->payments->sortByDesc('created_at') as $payment)
                            <tr>
                                <td class="whitespace-nowrap">{{ $loop->iteration }}</td>
                                <td>{{ \App\Support\PaymentMethodCatalog::label($payment->payment_method) }}</td>
                                <td class="whitespace-nowrap">Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
                                <td>@include('admin.partials.status-badge', ['status' => $payment->payment_status])</td>
                                <td>{{ $payment->proof_file ? 'Ada' : 'Tidak ada' }}</td>
                                <td class="whitespace-nowrap"><a href="{{ route('admin.payments.show', $payment) }}" class="admin-btn-secondary">Detail</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-500">Belum ada payment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
