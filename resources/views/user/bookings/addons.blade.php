@extends('layouts.portal')

@section('title', 'Cakrawala | Add-Ons')
@section('active', 'bookings')

@section('content')
    <section class="journey-shell" x-data="addonForm({{ Js::from($catalog) }})">
        <article class="journey-hero">
            <div class="journey-hero-grid">
                <div>
                    <span class="journey-chip">
                        <span class="journey-dot"></span>
                        Add-On Desk
                    </span>
                    <h1 class="journey-title">{{ $booking->booking_code }}</h1>
                    <p class="journey-copy">
                        Tambahkan layanan ekstra untuk perjalanan {{ $booking->flight->airline->name }}.
                        Semua item tetap masuk ke alur invoice booking yang sama.
                    </p>
                    <div class="journey-action-row mt-5">
                        <a href="{{ route('my-bookings.show', $booking) }}" class="portal-btn-blue">Back to Booking</a>
                        @if ($latestPayment?->payment_status === 'pending')
                            <a href="{{ route('payments.create', ['booking' => $booking->id]) }}" class="portal-btn-gold">Pay Pending Invoice</a>
                        @endif
                    </div>
                </div>
                <div class="journey-meta-grid">
                    <div class="journey-code">
                        <span class="journey-code-label">Route</span>
                        <span class="journey-code-value">{{ $booking->flight->departureAirport->code }} -> {{ $booking->flight->arrivalAirport->code }}</span>
                    </div>
                    <div class="journey-code">
                        <span class="journey-code-label">Current Payment</span>
                        <span class="journey-code-value">{{ ucfirst($latestPayment?->payment_status ?? 'pending') }}</span>
                    </div>
                </div>
            </div>
        </article>

        <div class="journey-grid">
            <div class="space-y-6">
                <article class="portal-card">
                    <p class="portal-kicker">Add-On Composer</p>
                    <h2 class="portal-section-title">Tambah Add-On</h2>
                    <p class="portal-section-copy">Pilih layanan per passenger atau untuk seluruh booking tanpa mengubah alur pembayaran yang sudah ada.</p>

                    <form method="POST" action="{{ route('my-bookings.addons.store', $booking) }}" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <label class="portal-label" for="addon_code">Addon</label>
                            <select id="addon_code" name="addon_code" class="portal-select" x-model="selectedCode">
                                @foreach ($catalog as $code => $item)
                                    <option value="{{ $code }}">{{ $item['name'] }} - Rp{{ number_format((float) $item['unit_price'], 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                            @error('addon_code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="portal-label" for="booking_detail_id">Passenger</label>
                            <select id="booking_detail_id" name="booking_detail_id" class="portal-select" :disabled="currentScope() === 'booking'">
                                <option value="">Pilih Passenger</option>
                                @foreach ($booking->details as $detail)
                                    <option value="{{ $detail->id }}" @selected((int) old('booking_detail_id') === (int) $detail->id)>
                                        {{ $detail->passenger?->full_name }} - Seat {{ $detail->seat?->seat_number }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500" x-text="scopeDescription()"></p>
                            @error('booking_detail_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="portal-label" for="quantity">Quantity</label>
                                <input id="quantity" type="number" min="1" name="quantity" value="{{ old('quantity', 1) }}" class="portal-input">
                                @error('quantity')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="portal-label" for="notes">Notes</label>
                                <input id="notes" name="notes" class="portal-input" placeholder="Contoh: vegetarian meal" value="{{ old('notes') }}">
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="portal-surface-muted">
                            <p class="text-sm text-slate-500" x-text="currentAddon()?.description || '-'"></p>
                            <p class="mt-2 text-lg font-semibold text-[#0f3f78]" x-text="formattedUnitPrice()"></p>
                        </div>

                        <button type="submit" class="portal-btn-gold">Tambah Add-On</button>
                    </form>
                </article>

                <article class="portal-card">
                    <p class="portal-kicker">Selected Services</p>
                    <h2 class="portal-section-title">Daftar Add-On</h2>
                    <div class="mt-5 space-y-3">
                        @forelse ($addons as $addon)
                            <div class="journey-manifest-card">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <p class="text-lg font-semibold text-slate-800">{{ $addon->addon_name }}</p>
                                        <p class="mt-1 text-sm text-slate-600">{{ ucfirst($addon->addon_type) }} | Qty {{ $addon->quantity }}</p>
                                        @if ($addon->bookingDetail?->passenger)
                                            <p class="mt-1 text-sm text-slate-500">Passenger: {{ $addon->bookingDetail->passenger->full_name }}</p>
                                        @else
                                            <p class="mt-1 text-sm text-slate-500">Berlaku untuk seluruh booking</p>
                                        @endif
                                        @if ($addon->notes)
                                            <p class="mt-1 text-xs text-slate-500">Notes: {{ $addon->notes }}</p>
                                        @endif
                                    </div>
                                    <div class="text-right">
                                        <p class="font-semibold text-[#0f3f78]">Rp{{ number_format((float) $addon->total_price, 0, ',', '.') }}</p>
                                        <div class="mt-2">@include('admin.partials.status-badge', ['status' => $addon->status])</div>
                                        @if ($addon->status === 'selected')
                                            <form method="POST" action="{{ route('my-bookings.addons.destroy', [$booking, $addon]) }}" class="mt-3">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="portal-btn-blue">Batalkan</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="journey-rail-card text-slate-500">Belum ada add-on pada booking ini.</div>
                        @endforelse
                    </div>
                </article>
            </div>

            <aside class="journey-rail">
                <article class="journey-rail-card journey-rail-card-dark">
                    <p class="portal-kicker">Invoice Panel</p>
                    <h2 class="mt-2 text-2xl font-bold">Current Invoice</h2>
                    <div class="mt-5 space-y-3">
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Booking Total</p>
                            <p class="text-2xl font-bold text-slate-800">Rp{{ number_format((float) $booking->total_price, 0, ',', '.') }}</p>
                        </div>
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Current Payment Status</p>
                            <p class="font-semibold text-slate-800">{{ ucfirst($latestPayment?->payment_status ?? 'pending') }}</p>
                        </div>
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Pending Amount</p>
                            <p class="font-semibold text-slate-800">
                                @if ($latestPayment?->payment_status === 'pending')
                                    Rp{{ number_format((float) $latestPayment->amount, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </article>
            </aside>
        </div>
    </section>

    <script>
        function addonForm(catalog) {
            const keys = Object.keys(catalog);
            const initial = keys[0] || null;

            return {
                catalog,
                selectedCode: initial,
                currentAddon() {
                    return this.catalog[this.selectedCode] || null;
                },
                currentScope() {
                    return this.currentAddon()?.scope || 'passenger';
                },
                scopeDescription() {
                    if (this.currentScope() === 'booking') {
                        return 'Addon ini berlaku untuk seluruh booking, tidak perlu pilih passenger.';
                    }

                    return 'Addon ini bersifat per-passenger. Pilih passenger tujuan add-on.';
                },
                formattedUnitPrice() {
                    const price = Number(this.currentAddon()?.unit_price || 0);

                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(price) + ' / unit';
                },
            };
        }
    </script>
@endsection
