@extends('layouts.admin')

@section('title', 'Seat Detail | Cakrawala')
@section('page-title', 'Seat Detail')

@section('content')
    <section class="space-y-6">
        <article class="admin-ops-detail-hero">
            <div class="admin-section-head">
                <div class="max-w-3xl">
                    <p class="admin-section-kicker">Seat Overview</p>
                    <h2 class="admin-section-title">{{ $seat->seat_number }}</h2>
                    <p class="admin-section-copy">Detail seat, armada, airline, dan histori pemakaian digabungkan agar tracing kursi lebih cepat.</p>
                </div>
                <span class="admin-chip">{{ ucfirst($seat->class) }}</span>
            </div>

            <div class="admin-ops-inline-grid">
                <div class="admin-ops-info-card"><p class="admin-info-label">Airline</p><p class="admin-info-value">{{ $seat->airplane?->airline?->name }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Airplane</p><p class="admin-info-value">{{ $seat->airplane?->model }} ({{ $seat->airplane?->registration_number }})</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Seat Number</p><p class="admin-info-value">{{ $seat->seat_number }}</p></div>
                <div class="admin-ops-info-card"><p class="admin-info-label">Class</p><p class="admin-info-value">{{ ucfirst($seat->class) }}</p></div>
            </div>
        </article>

        <article class="admin-ops-table-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Usage History</p>
                    <h2 class="admin-section-title">Booking usage</h2>
                </div>
                <span class="admin-chip">{{ $seat->bookingDetails->count() }} record</span>
            </div>
            <div class="admin-table-wrap mt-4">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Booking Code</th>
                            <th>User</th>
                            <th>Passenger</th>
                            <th>Flight</th>
                            <th>Status</th>
                            <th>Ticket</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($seat->bookingDetails as $detail)
                            <tr>
                                <td>
                                    @if ($detail->booking)
                                        <a href="{{ route('admin.bookings.show', $detail->booking) }}" class="font-semibold text-[#0f3f78]">{{ $detail->booking->booking_code }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $detail->booking?->user?->name ?: '-' }}</td>
                                <td>{{ $detail->passenger?->full_name ?: '-' }}</td>
                                <td>{{ $detail->booking?->flight?->flight_number ?: '-' }}</td>
                                <td>@include('admin.partials.status-badge', ['status' => $detail->booking?->status])</td>
                                <td>{{ $detail->ticket?->id ? ($detail->ticket_number ?: '-') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-slate-500">Seat belum pernah dipakai.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
