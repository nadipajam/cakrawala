@extends('layouts.admin')

@section('title', 'Detail Passenger | Cakrawala')
@section('page-title', 'Passenger Detail')

@section('content')
    <section class="space-y-6">
        <article class="admin-ops-detail-hero">
            <div class="admin-section-head">
                <div class="max-w-3xl">
                    <p class="admin-section-kicker">Passenger Profile</p>
                    <h2 class="admin-section-title">{{ $passenger->full_name }}</h2>
                    <p class="admin-section-copy">Data identitas dasar dan histori booking passenger ditata ulang agar tracing manifest lebih mudah dilakukan.</p>
                </div>
            </div>

            <div class="admin-ops-inline-grid">
                <div class="admin-ops-info-card"><p class="admin-info-label">Full Name</p><p class="admin-info-value">{{ $passenger->full_name }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Gender</p><p class="admin-info-value">{{ ucfirst($passenger->gender) }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Birth Date</p><p class="admin-info-value">{{ $passenger->birth_date?->format('d M Y') }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">User Owner</p><p class="admin-info-value">{{ $passenger->user?->name }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Identity Number</p><p class="admin-info-value">{{ $passenger->identity_number ?: '-' }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Passport Number</p><p class="admin-info-value">{{ $passenger->passport_number ?: '-' }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Nationality</p><p class="admin-info-value">{{ $passenger->nationality ?: '-' }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Created At</p><p class="admin-info-value">{{ $passenger->created_at?->format('d M Y H:i') }}</p></div>
            </div>
        </article>

        <article class="admin-ops-table-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Travel History</p>
                    <h2 class="admin-section-title">Booking history</h2>
                </div>
                <span class="admin-chip">{{ $passenger->bookingDetails->count() }} record</span>
            </div>
            <div class="admin-table-wrap mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Booking Code</th>
                            <th>User</th>
                            <th>Flight</th>
                            <th>Seat</th>
                            <th>Ticket</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($passenger->bookingDetails as $detail)
                            <tr>
                                <td>{{ $detail->booking?->booking_code }}</td>
                                <td>{{ $detail->booking?->user?->name }}</td>
                                <td>{{ $detail->booking?->flight?->flight_number }}</td>
                                <td>{{ $detail->seat?->seat_number }} ({{ ucfirst($detail->seat?->class ?? '-') }})</td>
                                <td>{{ $detail->ticket?->id ? ($detail->ticket_number ?: '-') : '-' }}</td>
                                <td>
                                    @if ($detail->booking)
                                        <a class="admin-btn-secondary" href="{{ route('admin.bookings.show', $detail->booking) }}">Booking</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-500">Belum pernah ikut booking.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
