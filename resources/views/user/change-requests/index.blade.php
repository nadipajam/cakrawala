@extends('layouts.portal')

@section('title', 'Cakrawala | Permintaan Layanan')
@section('active', 'change-requests')

@section('content')
    <section class="space-y-6">
        <article class="support-hero-panel">
            <div class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
                <div>
                    <p class="booking-shell-kicker">Permintaan layanan</p>
                    <h1 class="booking-shell-title">Ajukan refund atau perubahan perjalanan dengan proses yang lebih jelas.</h1>
                    <p class="booking-shell-copy">Permintaan baru, panduan jenis layanan, dan riwayat penanganan disusun rapi agar mudah dipahami.</p>
                </div>
                <div class="support-summary-grid">
                    <div class="support-summary-card">
                        <span>Total permintaan</span>
                        <strong>{{ $changeRequests->total() }}</strong>
                    </div>
                    <div class="support-summary-card">
                        <span>Booking tersedia</span>
                        <strong>{{ $bookings->count() }}</strong>
                    </div>
                </div>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <article class="portal-card">
                <p class="portal-kicker">Permintaan baru</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Buat permintaan baru</h2>
                <form method="POST" action="{{ route('my-bookings.change-requests.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="portal-label" for="booking_id">Booking</label>
                        <select id="booking_id" name="booking_id" class="portal-select">
                            <option value="">Pilih booking</option>
                            @foreach ($bookings as $booking)
                                <option value="{{ $booking->id }}" @selected((int) old('booking_id', $preselectedBookingId ?? 0) === (int) $booking->id)>
                                    {{ $booking->booking_code }} - {{ $booking->flight->departureAirport->code }} -> {{ $booking->flight->arrivalAirport->code }}
                                </option>
                            @endforeach
                        </select>
                        @error('booking_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="portal-label" for="request_type">Jenis Permintaan</label>
                        <select id="request_type" name="request_type" class="portal-select">
                            @foreach ($requestTypes as $value => $type)
                                <option value="{{ $value }}" @selected(old('request_type') === $value)>{{ $type['label'] }}</option>
                            @endforeach
                        </select>
                        @error('request_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="portal-label" for="preferred_flight_id">Penerbangan Pilihan (opsional untuk reschedule)</label>
                        <select id="preferred_flight_id" name="preferred_flight_id" class="portal-select">
                            <option value="">Tidak memilih flight khusus</option>
                            @foreach ($upcomingFlights as $flight)
                                <option value="{{ $flight->id }}" @selected((int) old('preferred_flight_id') === (int) $flight->id)>
                                    {{ $flight->flight_number }} - {{ $flight->departureAirport->code }} -> {{ $flight->arrivalAirport->code }} ({{ $flight->departure_time->format('d M Y H:i') }})
                                </option>
                            @endforeach
                        </select>
                        @error('preferred_flight_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="portal-label" for="reason">Alasan</label>
                        <textarea id="reason" name="reason" rows="5" class="portal-input" placeholder="Jelaskan detail permintaan Anda...">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="portal-btn-gold">Kirim Permintaan</button>
                </form>
            </article>

            <aside class="support-rail">
                <div class="support-rail-card">
                    <p class="portal-kicker">Panduan layanan</p>
                    <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Jenis permintaan</h2>
                    <div class="mt-5 space-y-3">
                        @foreach ($requestTypes as $type)
                            <article class="portal-card-soft">
                                <p class="font-semibold text-slate-800">{{ $type['label'] }}</p>
                                <p class="mt-1 text-sm text-slate-600">{{ $type['description'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </aside>
        </div>

        <article class="portal-card">
            <p class="portal-kicker">Riwayat layanan</p>
            <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Riwayat permintaan</h2>
            <div class="mt-4 space-y-3">
                @forelse ($changeRequests as $item)
                    <article class="service-case-card">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-800">{{ \App\Support\BookingChangeRequestCatalog::label($item->request_type) }}</p>
                                <p class="text-sm text-slate-600">Booking {{ $item->booking?->booking_code }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $item->reason }}</p>
                                @if ($item->preferredFlight)
                                    <p class="mt-1 text-xs text-slate-500">
                                        Penerbangan pilihan: {{ $item->preferredFlight->flight_number }} - {{ $item->preferredFlight->departureAirport?->code }} -> {{ $item->preferredFlight->arrivalAirport?->code }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <div>@include('admin.partials.status-badge', ['status' => $item->status])</div>
                                <p class="mt-2 text-xs text-slate-500">{{ $item->created_at?->format('d M Y H:i') }}</p>
                                @if ($item->processedByUser)
                                    <p class="mt-1 text-xs text-slate-500">Diproses oleh {{ $item->processedByUser->name }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($item->admin_notes || $item->resolution_details || $item->resolution_amount)
                            <div class="mt-4 portal-surface-muted text-sm text-slate-600">
                                @if ($item->admin_notes)
                                    <p><strong>Catatan Admin:</strong> {{ $item->admin_notes }}</p>
                                @endif
                                @if ($item->resolution_details)
                                    <p class="mt-1"><strong>Penyelesaian:</strong> {{ $item->resolution_details }}</p>
                                @endif
                                @if ($item->resolution_amount)
                                    <p class="mt-1"><strong>Nominal:</strong> Rp{{ number_format((float) $item->resolution_amount, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        @endif
                    </article>
                @empty
                    <p class="portal-card-soft text-slate-500">Belum ada permintaan perubahan.</p>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $changeRequests->links() }}
            </div>
        </article>
    </section>
@endsection
