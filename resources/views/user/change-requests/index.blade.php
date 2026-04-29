@extends('layouts.portal')

@section('title', 'Cakrawala | Service Requests')
@section('active', 'change-requests')

@section('content')
    <section class="space-y-6">
        <article class="portal-card">
            <p class="portal-kicker">Service desk</p>
            <h1 class="portal-section-title">Refund & Change Request</h1>
            <p class="portal-section-copy">Ajukan refund, reschedule, perbaikan nama, atau cancel request dari booking yang memenuhi syarat.</p>
        </article>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <article class="portal-card">
                <p class="portal-kicker">New case</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Buat Permintaan Baru</h2>
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
                        <label class="portal-label" for="request_type">Request Type</label>
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
                        <label class="portal-label" for="preferred_flight_id">Preferred Flight (Opsional untuk reschedule)</label>
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
                        <label class="portal-label" for="reason">Reason</label>
                        <textarea id="reason" name="reason" rows="5" class="portal-input" placeholder="Jelaskan detail permintaan Anda...">{{ old('reason') }}</textarea>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="portal-btn-gold">Kirim Request</button>
                </form>
            </article>

            <aside class="portal-side-panel">
                <p class="portal-kicker">Request guide</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Jenis Request</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($requestTypes as $type)
                        <article class="portal-card-soft">
                            <p class="font-semibold text-slate-800">{{ $type['label'] }}</p>
                            <p class="mt-1 text-sm text-slate-600">{{ $type['description'] }}</p>
                        </article>
                    @endforeach
                </div>
            </aside>
        </div>

        <article class="portal-card">
            <p class="portal-kicker">Case history</p>
            <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Riwayat Permintaan</h2>
            <div class="mt-4 space-y-3">
                @forelse ($changeRequests as $item)
                    <article class="portal-route-card">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-semibold text-slate-800">{{ \App\Support\BookingChangeRequestCatalog::label($item->request_type) }}</p>
                                <p class="text-sm text-slate-600">Booking {{ $item->booking?->booking_code }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $item->reason }}</p>
                                @if ($item->preferredFlight)
                                    <p class="mt-1 text-xs text-slate-500">
                                        Preferred flight: {{ $item->preferredFlight->flight_number }} - {{ $item->preferredFlight->departureAirport?->code }} -> {{ $item->preferredFlight->arrivalAirport?->code }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <div>@include('admin.partials.status-badge', ['status' => $item->status])</div>
                                <p class="mt-2 text-xs text-slate-500">{{ $item->created_at?->format('d M Y H:i') }}</p>
                                @if ($item->processedByUser)
                                    <p class="mt-1 text-xs text-slate-500">Processed by {{ $item->processedByUser->name }}</p>
                                @endif
                            </div>
                        </div>

                        @if ($item->admin_notes || $item->resolution_details || $item->resolution_amount)
                            <div class="mt-4 portal-surface-muted text-sm text-slate-600">
                                @if ($item->admin_notes)
                                    <p><strong>Admin Notes:</strong> {{ $item->admin_notes }}</p>
                                @endif
                                @if ($item->resolution_details)
                                    <p class="mt-1"><strong>Resolution:</strong> {{ $item->resolution_details }}</p>
                                @endif
                                @if ($item->resolution_amount)
                                    <p class="mt-1"><strong>Amount:</strong> Rp{{ number_format((float) $item->resolution_amount, 0, ',', '.') }}</p>
                                @endif
                            </div>
                        @endif
                    </article>
                @empty
                    <p class="portal-card-soft text-slate-500">Belum ada request perubahan.</p>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $changeRequests->links() }}
            </div>
        </article>
    </section>
@endsection
