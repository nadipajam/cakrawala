<?php

namespace App\Http\Controllers\Web\User;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationWebController extends Controller
{
    public function index(Request $request): View
    {
        return view('user.notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(12),
            'unreadCount' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->firstOrFail();

        if ($item->read_at) {
            return back()
                ->with('status', 'Notifikasi ini sudah dibaca sebelumnya.')
                ->with('status_type', 'warning');
        }

        $item->markAsRead();

        return back()
            ->with('status', 'Notifikasi ditandai sudah dibaca.')
            ->with('status_type', 'success');
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $updated = $request->user()->unreadNotifications()->update(['read_at' => now()]);

        if ($updated === 0) {
            return back()
                ->with('status', 'Semua notifikasi sudah dibaca.')
                ->with('status_type', 'warning');
        }

        return back()
            ->with('status', 'Semua notifikasi berhasil ditandai dibaca.')
            ->with('status_type', 'success');
    }
}
