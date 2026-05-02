@extends('layouts.admin')

@section('title', 'Detail Permintaan Perubahan | Cakrawala')
@section('page-title', 'Detail Permintaan Perubahan')

@section('content')
    <section class="space-y-6">
        <article class="admin-ops-detail-hero space-y-5">
            <div class="admin-section-head">
                <div class="max-w-3xl">
                    <p class="admin-section-kicker">Request #{{ $changeRequest->id }}</p>
                    <h2 class="admin-section-title">{{ \App\Support\BookingChangeRequestCatalog::label($changeRequest->request_type) }}</h2>
                    <p class="admin-section-copy">Dibuat {{ $changeRequest->created_at?->format('d M Y H:i') }} oleh {{ $changeRequest->user?->name }}. Detail booking, alasan, dan penyelesaian dipusatkan dalam satu halaman.</p>
                </div>
                @include('admin.partials.status-badge', ['status' => $changeRequest->status])
            </div>

            <div class="grid gap-4 xl:grid-cols-[1.35fr_.95fr]">
                <div class="admin-ops-inline-grid">
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Booking</p>
                        <p class="admin-info-value">{{ $changeRequest->booking?->booking_code ?: '-' }}</p>
                        @if ($changeRequest->booking)
                            <a href="{{ route('admin.bookings.show', $changeRequest->booking) }}" class="mt-2 inline-flex text-sm font-semibold text-[#c2410c] underline">Buka booking</a>
                        @endif
                    </article>
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Pengguna</p>
                        <p class="admin-info-value">{{ $changeRequest->user?->name ?: '-' }}</p>
                        <p class="mt-2 break-all text-sm text-slate-500">{{ $changeRequest->user?->email ?: '-' }}</p>
                    </article>
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Rute Saat Ini</p>
                        <p class="admin-info-value">{{ $changeRequest->booking?->flight?->departureAirport?->code ?: '-' }} - {{ $changeRequest->booking?->flight?->arrivalAirport?->code ?: '-' }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ $changeRequest->booking?->flight?->flight_number ?: '-' }}</p>
                    </article>
                    <article class="admin-ops-info-card">
                        <p class="admin-info-label">Penerbangan Pilihan</p>
                        @if ($changeRequest->preferredFlight)
                            <p class="admin-info-value">{{ $changeRequest->preferredFlight->flight_number }}</p>
                            <p class="mt-2 text-sm text-slate-500">{{ $changeRequest->preferredFlight->departureAirport?->code }} - {{ $changeRequest->preferredFlight->arrivalAirport?->code }}</p>
                        @else
                            <p class="admin-info-value">-</p>
                        @endif
                    </article>
                </div>

                <aside class="space-y-4">
                    <article class="admin-ops-sidecard">
                        <p class="admin-section-kicker">Reason</p>
                        <h3 class="mt-2 font-heading text-lg font-bold text-slate-800">Alasan customer</h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">{{ $changeRequest->reason }}</p>
                    </article>

                    @if ($changeRequest->processedByUser)
                        <article class="admin-ops-sidecard">
                            <p class="admin-section-kicker">Pemrosesan terakhir</p>
                            <p class="mt-2 text-sm text-slate-700">Diproses oleh <strong>{{ $changeRequest->processedByUser->name }}</strong> pada {{ $changeRequest->processed_at?->format('d M Y H:i') ?: '-' }}.</p>
                        </article>
                    @endif
                </aside>
            </div>
        </article>

        <article class="admin-ops-table-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Penyelesaian</p>
                    <h2 class="admin-section-title">Proses permintaan</h2>
                    <p class="admin-section-copy">Gunakan form ini untuk memperbarui status, nilai penyelesaian, catatan admin, dan hasil keputusan akhir.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.change-requests.update', $changeRequest) }}" class="mt-4 space-y-4">
                @csrf
                @method('PATCH')
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="admin-label" for="status">Status</label>
                        <select id="status" name="status" class="admin-field">
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected(old('status', $changeRequest->status) === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="admin-label" for="resolution_amount">Nominal penyelesaian</label>
                        <input id="resolution_amount" name="resolution_amount" type="number" min="0" step="0.01" class="admin-field" value="{{ old('resolution_amount', $changeRequest->resolution_amount) }}">
                    </div>
                </div>

                <div>
                    <label class="admin-label" for="admin_notes">Catatan admin</label>
                    <textarea id="admin_notes" name="admin_notes" rows="4" class="admin-field">{{ old('admin_notes', $changeRequest->admin_notes) }}</textarea>
                </div>

                <div>
                    <label class="admin-label" for="resolution_details">Detail penyelesaian</label>
                    <textarea id="resolution_details" name="resolution_details" rows="4" class="admin-field">{{ old('resolution_details', $changeRequest->resolution_details) }}</textarea>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="submit" class="admin-btn-primary">Perbarui Permintaan</button>
                    <a href="{{ route('admin.change-requests.index') }}" class="admin-btn-secondary">Kembali ke Daftar</a>
                </div>
            </form>
        </article>
    </section>
@endsection
