@extends('layouts.admin')

@section('title', 'Inbox Bantuan | Cakrawala')
@section('page-title', 'Inbox Bantuan')

@section('content')
    <section class="space-y-5">
        <div class="grid gap-5 xl:grid-cols-[320px_minmax(0,1fr)]">
            <form method="GET" class="admin-ops-filter space-y-5">
                <div>
                    <p class="admin-section-kicker">Layanan pelanggan</p>
                    <h2 class="admin-section-title">Filter inbox bantuan</h2>
                    <p class="admin-section-copy">Pencarian, status, dan assignment PIC dipisahkan ke panel samping agar daftar pesan tetap fokus dibaca.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="admin-label" for="search">Cari</label>
                        <input id="search" name="search" value="{{ $search }}" class="admin-field" placeholder="Nama, email, atau subject">
                    </div>
                    <div>
                        <label class="admin-label" for="status">Status</label>
                        <select id="status" name="status" class="admin-field">
                            <option value="">Semua status</option>
                            <option value="open" @selected($status === 'open')>Terbuka</option>
                            <option value="in_progress" @selected($status === 'in_progress')>Diproses</option>
                            <option value="resolved" @selected($status === 'resolved')>Resolved</option>
                            <option value="closed" @selected($status === 'closed')>Closed</option>
                        </select>
                    </div>
                    <div>
                        <label class="admin-label" for="assigned_to">Ditugaskan ke</label>
                        <select id="assigned_to" name="assigned_to" class="admin-field">
                            <option value="">Semua PIC</option>
                            @foreach ($backofficeUsers as $backofficeUser)
                                <option value="{{ $backofficeUser->id }}" @selected((int) $assignedTo === (int) $backofficeUser->id)>{{ $backofficeUser->name }} | {{ $backofficeUser->roleLabel() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="admin-divider space-y-3">
                    <p class="text-sm text-slate-500">Gunakan filter PIC untuk membagi beban tiket bantuan dan mengecek kasus yang belum ditangani.</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <button class="admin-btn-primary" type="submit">Filter</button>
                        <a href="{{ route('admin.contact-messages.index') }}" class="admin-btn-secondary">Atur Ulang</a>
                    </div>
                </div>
            </form>

            <div class="space-y-5">
                <article class="admin-support-hero">
                    <div class="admin-section-head">
                        <div>
                            <p class="admin-section-kicker">Antrean bantuan</p>
                            <h2 class="admin-section-title">Inbox kontak website</h2>
                            <p class="admin-section-copy">Permintaan bantuan dari halaman contact masuk ke daftar ini untuk assignment, follow-up, dan resolusi tim operasional.</p>
                        </div>
                        <span class="admin-chip">{{ $messages->total() }} pesan</span>
                    </div>

                    <div class="admin-ops-summary-grid">
                        <article class="admin-ops-summary-card">
                            <p class="label">Pesan terlihat</p>
                            <p class="value">{{ $messages->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Jumlah pesan pada halaman aktif.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Kasus terbuka</p>
                            <p class="value text-amber-600">{{ $messages->where('status', 'open')->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Kasus terbuka yang menunggu penanganan.</p>
                        </article>
                        <article class="admin-ops-summary-card">
                            <p class="label">Kasus bertugas</p>
                            <p class="value text-[#c2410c]">{{ $messages->filter(fn ($message) => filled($message->assigned_to))->count() }}</p>
                            <p class="mt-2 text-sm text-slate-500">Kasus yang sudah memiliki PIC.</p>
                        </article>
                    </div>
                </article>

                <article class="admin-ops-table-card">
                    <div class="admin-table-wrap mt-4">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Customer</th>
                                    <th>Subject</th>
                                    <th>Sumber</th>
                                    <th>Status</th>
                                    <th>PIC</th>
                                    <th>Received</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($messages as $message)
                                    <tr>
                                        <td>{{ ($messages->firstItem() ?? 1) + $loop->index }}</td>
                                        <td>
                                            <p class="font-semibold text-slate-800">{{ $message->name }}</p>
                                            <p class="text-xs text-slate-500">{{ $message->email }}</p>
                                        </td>
                                        <td>
                                            <p class="font-semibold text-slate-800">{{ $message->subject }}</p>
                                            <p class="max-w-[280px] whitespace-normal text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($message->message, 120) }}</p>
                                        </td>
                                        <td>
                                            @if ($message->source === 'payment_escalation')
                                                <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700">Escalation Payment</span>
                                            @else
                                                <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-600">{{ ucfirst(str_replace('_', ' ', $message->source)) }}</span>
                                            @endif
                                        </td>
                                        <td>@include('admin.partials.status-badge', ['status' => $message->status])</td>
                                        <td>{{ $message->assignedUser?->name ?: 'Belum ditugaskan' }}</td>
                                        <td>{{ $message->created_at?->format('d M Y H:i') }}</td>
                                        <td><a href="{{ route('admin.contact-messages.show', $message) }}" class="admin-btn-secondary">Buka</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-slate-500">Belum ada pesan bantuan dari website.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $messages->links() }}</div>
                </article>
            </div>
        </div>
    </section>
@endsection
