@extends('layouts.admin')

@section('title', 'Support Message Detail | Cakrawala')
@section('page-title', 'Support Message')

@section('content')
    <section class="space-y-6">
        <article class="admin-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Support Request</p>
                    <h2 class="admin-section-title">{{ $contactMessage->subject }}</h2>
                    <p class="admin-section-copy">Pesan masuk dari halaman contact yang dapat diassign dan ditangani oleh tim backoffice.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @include('admin.partials.status-badge', ['status' => $contactMessage->status])
                    <span class="admin-chip">#{{ $contactMessage->id }}</span>
                </div>
            </div>

            <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <article class="admin-info-card">
                    <p class="admin-info-label">Name</p>
                    <p class="admin-info-value">{{ $contactMessage->name }}</p>
                </article>
                <article class="admin-info-card">
                    <p class="admin-info-label">Email</p>
                    <p class="admin-info-value break-all">{{ $contactMessage->email }}</p>
                </article>
                <article class="admin-info-card">
                    <p class="admin-info-label">Phone</p>
                    <p class="admin-info-value">{{ $contactMessage->phone ?: '-' }}</p>
                </article>
                <article class="admin-info-card">
                    <p class="admin-info-label">Customer Link</p>
                    <p class="admin-info-value">{{ $contactMessage->user?->name ?: 'Guest / public form' }}</p>
                </article>
            </div>

            <div class="mt-5 rounded-2xl border border-slate-200 bg-white/75 p-5">
                <p class="admin-info-label">Customer Message</p>
                <div class="mt-3 whitespace-pre-wrap text-slate-700">{{ $contactMessage->message }}</div>
            </div>
        </article>

        <article class="admin-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Case Handling</p>
                    <h2 class="admin-section-title">Assign dan update status</h2>
                    <p class="admin-section-copy">Tentukan PIC, progres penanganan, dan catatan internal untuk case support ini.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.contact-messages.update', $contactMessage) }}" class="mt-5 space-y-4">
                @csrf
                @method('PATCH')

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div>
                        <label class="admin-label" for="status">Status</label>
                        <select id="status" name="status" class="admin-field">
                            <option value="open" @selected($contactMessage->status === 'open')>Open</option>
                            <option value="in_progress" @selected($contactMessage->status === 'in_progress')>In Progress</option>
                            <option value="resolved" @selected($contactMessage->status === 'resolved')>Resolved</option>
                            <option value="closed" @selected($contactMessage->status === 'closed')>Closed</option>
                        </select>
                    </div>
                    <div>
                        <label class="admin-label" for="assigned_to">Assigned To</label>
                        <select id="assigned_to" name="assigned_to" class="admin-field">
                            <option value="">Unassigned</option>
                            @foreach ($backofficeUsers as $backofficeUser)
                                <option value="{{ $backofficeUser->id }}" @selected((int) old('assigned_to', $contactMessage->assigned_to) === (int) $backofficeUser->id)>{{ $backofficeUser->name }} · {{ $backofficeUser->roleLabel() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-label">Resolved At</label>
                        <div class="admin-field bg-slate-50">{{ $contactMessage->resolved_at?->format('d M Y H:i') ?: '-' }}</div>
                    </div>
                </div>

                <div>
                    <label class="admin-label" for="internal_notes">Internal Notes</label>
                    <textarea id="internal_notes" name="internal_notes" rows="6" class="admin-field">{{ old('internal_notes', $contactMessage->internal_notes) }}</textarea>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button class="admin-btn-primary" type="submit">Save Case Update</button>
                    <a href="{{ route('admin.contact-messages.index') }}" class="admin-btn-secondary">Back to Inbox</a>
                </div>
            </form>
        </article>
    </section>
@endsection
