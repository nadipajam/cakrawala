@extends('layouts.portal')

@section('title', 'Cakrawala | Dashboard User')
@section('active', 'bookings')

@section('content')
    <section class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <article class="portal-card">
                <p class="text-sm text-slate-500">Active Bookings</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['active_bookings'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Perjalanan yang masih berjalan atau menunggu langkah berikutnya.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Pending Payment</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['pending_payments'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Booking yang masih perlu diselesaikan pembayarannya.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Completed Trips</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['completed_trips'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Total perjalanan yang sudah berakhir dengan tiket terbit.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Saved Passengers</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['saved_passengers'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Profil penumpang yang siap dipakai untuk booking berikutnya.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Unread Notifications</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['unread_notifications'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Update yang belum Anda buka dari sistem dan admin.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Open Support Cases</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['open_support_cases'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Permintaan layanan yang masih dalam proses penanganan.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Pending Check-In</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['pending_checkins'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Booking terkonfirmasi yang belum menyelesaikan check-in.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Active Add-Ons</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['active_addons'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Tambahan layanan aktif yang melekat pada booking Anda.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Open Change Request</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['open_change_requests'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Request refund atau perubahan yang belum selesai diproses.</p>
            </article>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <a href="{{ route('my-bookings.change-requests.index') }}" class="quick-link-card">
                <span class="quick-link-icon">R</span>
                <span>
                    <span class="quick-link-title">Refund / Change</span>
                    <span class="text-sm text-slate-500">Kelola request pasca booking</span>
                </span>
            </a>
            <a href="{{ route('my-bookings.index') }}" class="quick-link-card">
                <span class="quick-link-icon">C</span>
                <span>
                    <span class="quick-link-title">Online Check-In</span>
                    <span class="text-sm text-slate-500">Akses check-in per booking</span>
                </span>
            </a>
            <a href="{{ route('passengers.index') }}" class="quick-link-card">
                <span class="quick-link-icon">P</span>
                <span>
                    <span class="quick-link-title">Passenger List</span>
                    <span class="text-sm text-slate-500">Update data penumpang tersimpan</span>
                </span>
            </a>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <article class="portal-card">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-heading text-2xl font-bold text-[#0f3f78]">Recent Booking</h2>
                    <a href="{{ route('my-bookings.index') }}" class="portal-btn-blue px-4 py-2 text-sm">View All</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($bookings as $booking)
                        <a href="{{ route('my-bookings.show', $booking) }}" class="portal-card-soft block transition hover:bg-white">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $booking->booking_code }}</p>
                                    <p class="text-sm text-slate-500">{{ $booking->flight->flight_number }} Â· {{ $booking->flight->airline->name }}</p>
                                </div>
                                @if ($booking->status === 'pending')
                                    <span class="portal-status-pending">Pending</span>
                                @elseif ($booking->status === 'confirmed')
                                    <span class="portal-status-confirmed">Confirmed</span>
                                @elseif ($booking->status === 'cancelled')
                                    <span class="portal-status-cancelled">Cancelled</span>
                                @else
                                    <span class="portal-status-default">{{ ucfirst($booking->status) }}</span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <p class="portal-card-soft text-slate-500">No booking yet.</p>
                    @endforelse
                </div>
            </article>

            <article class="portal-card">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-heading text-2xl font-bold text-[#0f3f78]">Latest Notifications</h2>
                    <a href="{{ route('notifications.index') }}" class="portal-btn-blue px-4 py-2 text-sm">Open Inbox</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($notifications as $notification)
                        @php($payload = $notification->data)
                        <article class="portal-card-soft">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="font-semibold text-slate-800">{{ $payload['title'] }}</p>
                                        @if (blank($notification->read_at))
                                            <span class="portal-status-pending">Unread</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600">{{ $payload['message'] }}</p>
                                </div>
                                <span class="text-xs text-slate-500">{{ $notification->created_at?->format('d M Y H:i') }}</span>
                            </div>
                        </article>
                    @empty
                        <p class="portal-card-soft text-slate-500">No notifications yet.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
@endsection
