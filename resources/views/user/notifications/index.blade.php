@extends('layouts.portal')

@section('title', 'Cakrawala | Notifikasi')
@section('active', 'notifications')

@section('content')
    <section class="space-y-6">
        <article class="support-hero-panel">
            <div class="grid gap-6 xl:grid-cols-[1.18fr_.82fr]">
                <div>
                    <p class="booking-shell-kicker">Pusat notifikasi</p>
                    <h1 class="booking-shell-title">Seluruh pembaruan booking, pembayaran, dan layanan tersaji dalam satu halaman.</h1>
                    <p class="booking-shell-copy">Notifikasi terbaru, tautan tindakan, dan waktu masuk disusun jelas agar langkah berikutnya lebih cepat dipahami.</p>
                </div>
                <div class="support-summary-grid">
                    <div class="support-summary-card">
                        <span>Total notifikasi</span>
                        <strong>{{ $notifications->total() }}</strong>
                    </div>
                    <div class="support-summary-card">
                        <span>Belum dibaca</span>
                        <strong>{{ $unreadCount }}</strong>
                    </div>
                </div>
            </div>
            @if ($unreadCount > 0)
                <div class="mt-5">
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="portal-btn-blue">Tandai Semua Dibaca</button>
                    </form>
                </div>
            @endif
        </article>

        <div class="space-y-4">
            @forelse ($notifications as $notification)
                @php($payload = $notification->data)
                <article class="notification-inbox-card">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl font-semibold text-slate-800">{{ $payload['title'] }}</h2>
                                @if (blank($notification->read_at))
                                    <span class="portal-status-pending">Belum Dibaca</span>
                                @endif
                            </div>
                            <p class="mt-2 text-slate-600">{{ $payload['message'] }}</p>
                            @if (! empty($payload['action_url']))
                                <a href="{{ $payload['action_url'] }}" class="portal-anchor-link mt-3 inline-flex">Buka Halaman Terkait</a>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="text-xs text-slate-500">{{ $notification->created_at?->format('d M Y H:i') }}</span>
                            @if (blank($notification->read_at))
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="portal-btn-blue px-3 py-2 text-sm">Tandai Dibaca</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="portal-card text-center text-slate-600">Belum ada notifikasi.</div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="portal-card">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
@endsection
