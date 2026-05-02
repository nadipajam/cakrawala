<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Models\User;
use App\Services\PortalNotificationService;
use App\Support\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminContactMessageController extends Controller
{
    public function __construct(
        protected PortalNotificationService $notificationService
    ) {
    }

    public function index(Request $request)
    {
        $search = trim((string) $request->string('search'));
        $status = trim((string) $request->string('status'));
        $assignedTo = $request->integer('assigned_to');

        $messages = ContactMessage::query()
            ->with(['user', 'assignedUser'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($messageQuery) use ($search) {
                    $messageQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%");
                });
            })
            ->when(in_array($status, ['open', 'in_progress', 'resolved', 'closed'], true), fn ($query) => $query->where('status', $status))
            ->when($assignedTo > 0, fn ($query) => $query->where('assigned_to', $assignedTo))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $backofficeUsers = User::query()
            ->whereIn('role', ['admin', 'staff', 'manager'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('admin.contact-messages.index', compact('messages', 'backofficeUsers', 'search', 'status', 'assignedTo'));
    }

    public function show(ContactMessage $contactMessage)
    {
        $contactMessage->load(['user', 'assignedUser']);
        $backofficeUsers = User::query()
            ->whereIn('role', ['admin', 'staff', 'manager'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('admin.contact-messages.show', compact('contactMessage', 'backofficeUsers'));
    }

    public function update(Request $request, ContactMessage $contactMessage): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! empty($data['assigned_to'])) {
            $assignedUser = User::query()->find($data['assigned_to']);
            if (! $assignedUser || ! in_array($assignedUser->role, UserRole::backofficeValues(), true)) {
                return back()->withErrors([
                    'assigned_to' => 'PIC harus berasal dari role backoffice.',
                ]);
            }
        }

        $assignedTo = $data['assigned_to'] ?? null;
        $internalNotes = $data['internal_notes'] ?? null;
        if (
            $contactMessage->status === $data['status']
            && (int) ($contactMessage->assigned_to ?? 0) === (int) ($assignedTo ?? 0)
            && (string) ($contactMessage->internal_notes ?? '') === (string) ($internalNotes ?? '')
        ) {
            return back()
                ->with('status', 'Tidak ada perubahan pada pesan bantuan.')
                ->with('status_type', 'warning');
        }

        $contactMessage->fill($data);
        $contactMessage->resolved_at = in_array($data['status'], ['resolved', 'closed'], true) ? now() : null;
        $contactMessage->save();

        if ($contactMessage->user_id) {
            $this->notificationService->contactMessageUpdated($contactMessage->fresh('user'));
        }

        return back()
            ->with('status', 'Status pesan bantuan berhasil diperbarui.')
            ->with('status_type', 'success');
    }
}
