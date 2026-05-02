@extends('layouts.portal')

@section('title', 'Cakrawala | Dashboard Pelanggan')
@section('active', 'bookings')

@section('content')
    @php
        $focusCards = [
            [
                'label' => 'Pembayaran tertunda',
                'value' => $stats['pending_payments'],
                'copy' => 'Booking yang masih menunggu pembayaran atau verifikasi.',
                'route' => route('my-bookings.index'),
                'action' => 'Tinjau booking',
            ],
            [
                'label' => 'Check-in tertunda',
                'value' => $stats['pending_checkins'],
                'copy' => 'Perjalanan terkonfirmasi yang belum menyelesaikan check-in.',
                'route' => route('my-bookings.index'),
                'action' => 'Buka perjalanan',
            ],
            [
                'label' => 'Layanan aktif',
                'value' => $stats['open_support_cases'],
                'copy' => 'Permintaan layanan yang masih diproses oleh tim layanan pelanggan.',
                'route' => route('my-bookings.change-requests.index'),
                'action' => 'Buka permintaan',
            ],
        ];
    @endphp

    <section class="space-y-6">
        <article class="customer-command-panel">
            <div class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
                <div class="space-y-5">
                    <div>
                        <p class="customer-section-kicker">Langkah berikutnya</p>
                        <h2 class="customer-section-title">Dashboard perjalanan yang disusun berdasarkan langkah berikutnya.</h2>
                        <p class="customer-section-copy">Alih-alih daftar KPI datar, halaman ini menonjolkan pekerjaan yang masih perlu diselesaikan agar booking, pembayaran, check-in, dan layanan purna jual lebih cepat ditindaklanjuti.</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <article class="customer-spotlight-card">
                            <p class="customer-spotlight-label">Booking aktif</p>
                            <p class="customer-spotlight-value">{{ $stats['active_bookings'] }}</p>
                            <p class="customer-spotlight-copy">Perjalanan yang masih berjalan di akun Anda.</p>
                        </article>
                        <article class="customer-spotlight-card">
                            <p class="customer-spotlight-label">Perjalanan selesai</p>
                            <p class="customer-spotlight-value">{{ $stats['completed_trips'] }}</p>
                            <p class="customer-spotlight-copy">Perjalanan yang sudah berakhir dan masuk arsip.</p>
                        </article>
                        <article class="customer-spotlight-card">
                            <p class="customer-spotlight-label">Penumpang tersimpan</p>
                            <p class="customer-spotlight-value">{{ $stats['saved_passengers'] }}</p>
                            <p class="customer-spotlight-copy">Profil yang siap dipakai untuk booking berikutnya.</p>
                        </article>
                    </div>
                </div>

                <div class="customer-queue-panel">
                    <p class="customer-section-kicker">Prioritas utama</p>
                    <div class="mt-4 space-y-3">
                        @foreach ($focusCards as $card)
                            <article class="customer-queue-item">
                                <div>
                                    <p class="customer-queue-label">{{ $card['label'] }}</p>
                                    <p class="customer-queue-copy">{{ $card['copy'] }}</p>
                                </div>
                                <div class="customer-queue-meta">
                                    <p class="customer-queue-value">{{ $card['value'] }}</p>
                                    <a href="{{ $card['route'] }}" class="customer-queue-link">{{ $card['action'] }}</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </div>
        </article>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="portal-card">
                <p class="text-sm text-slate-500">Notifikasi belum dibaca</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['unread_notifications'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Update sistem dan admin yang belum Anda buka.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Permintaan perubahan aktif</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['open_change_requests'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Refund atau perubahan yang belum selesai diproses.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Add-on aktif</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['active_addons'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Tambahan layanan aktif yang melekat pada booking.</p>
            </article>
            <article class="portal-card">
                <p class="text-sm text-slate-500">Permintaan layanan</p>
                <p class="mt-2 text-4xl font-bold text-slate-800">{{ $stats['open_support_cases'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Kasus layanan yang masih perlu dipantau.</p>
            </article>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <a href="{{ route('my-bookings.change-requests.index') }}" class="quick-link-card">
                <span class="quick-link-icon">R</span>
                <span>
                    <span class="quick-link-title">Refund / Perubahan</span>
                    <span class="text-sm text-slate-500">Kelola permintaan setelah booking</span>
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
                    <span class="quick-link-title">Daftar Penumpang</span>
                    <span class="text-sm text-slate-500">Update data penumpang tersimpan</span>
                </span>
            </a>
        </div>

        <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
            <article class="portal-card">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-heading text-2xl font-bold text-[#c2410c]">Booking terbaru</h2>
                    <a href="{{ route('my-bookings.index') }}" class="portal-btn-blue px-4 py-2 text-sm">Lihat Semua</a>
                </div>
                <div class="mt-4 space-y-3">
                    @forelse ($bookings as $booking)
                        <a href="{{ route('my-bookings.show', $booking) }}" class="portal-card-soft block transition hover:bg-white">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $booking->booking_code }}</p>
                                    <p class="text-sm text-slate-500">{{ $booking->flight->flight_number }} | {{ $booking->flight->airline->name }}</p>
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
                        <p class="portal-card-soft text-slate-500">Belum ada booking.</p>
                    @endforelse
                </div>
            </article>

            <article class="portal-card">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-heading text-2xl font-bold text-[#c2410c]">Notifikasi terbaru</h2>
                    <a href="{{ route('notifications.index') }}" class="portal-btn-blue px-4 py-2 text-sm">Buka Notifikasi</a>
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
                                            <span class="portal-status-pending">Belum Dibaca</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600">{{ $payload['message'] }}</p>
                                </div>
                                <span class="text-xs text-slate-500">{{ $notification->created_at?->format('d M Y H:i') }}</span>
                            </div>
                        </article>
                    @empty
                        <p class="portal-card-soft text-slate-500">Belum ada notifikasi.</p>
                    @endforelse
                </div>
            </article>
        </div>
    </section>
@endsection
