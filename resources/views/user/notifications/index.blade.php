@extends('layouts.portal')

@section('title', 'Cakrawala | Notifications')
@section('active', 'profile')

@section('content')
    <section class="space-y-6">
        <article class="portal-card">
            <div class="portal-section-head">
                <div>
                    <p class="portal-kicker">Inbox</p>
                    <h1 class="portal-section-title">Notifications</h1>
                    <p class="portal-section-copy">Update booking, pembayaran, dan balasan layanan disusun seperti inbox agar lebih mudah ditinjau.</p>
                </div>
                @if ($unreadCount > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit" class="portal-btn-blue">Mark All Read</button>
                    </form>
                @endif
            </div>
        </article>

        <div class="space-y-4">
            @forelse ($notifications as $notification)
                @php($payload = $notification->data)
                <article class="portal-route-card">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="text-xl font-semibold text-slate-800">{{ $payload['title'] }}</h2>
                                @if (blank($notification->read_at))
                                    <span class="portal-status-pending">Unread</span>
                                @endif
                            </div>
                            <p class="mt-2 text-slate-600">{{ $payload['message'] }}</p>
                            @if (! empty($payload['action_url']))
                                <a href="{{ $payload['action_url'] }}" class="portal-anchor-link mt-3 inline-flex">Open related page</a>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <span class="text-xs text-slate-500">{{ $notification->created_at?->format('d M Y H:i') }}</span>
                            @if (blank($notification->read_at))
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="portal-btn-blue px-3 py-2 text-sm">Mark Read</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="portal-card text-center text-slate-600">No notification yet.</div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="portal-card">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
@endsection
