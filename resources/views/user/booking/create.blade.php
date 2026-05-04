@extends('layouts.portal')

@section('title', 'Cakrawala | Booking')
@section('active', 'flights')

@section('content')
    @php
        $classKeys = $seatMap['class_keys'];
        $initialClass = in_array($selectedClass, $classKeys, true) ? $selectedClass : ($classKeys[0] ?? 'economy');
        $seatCatalog = $flight->airplane->seats
            ->map(fn ($seat) => ['id' => $seat->id, 'seat_number' => $seat->seat_number, 'class' => $seat->class])
            ->values();
    @endphp

    <section x-data="bookingWizard(
        {{ Js::from($passengers->map(fn ($p) => ['id' => $p->id, 'name' => $p->full_name])->values()) }},
        {{ Js::from($seatCatalog) }},
        {{ Js::from($seatMap['available_ids']) }},
        {{ Js::from($classPrices) }},
        '{{ $initialClass }}'
    )" class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_370px]">
        <div class="space-y-6">
            <article class="booking-composer-shell">
                <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
                    <div>
                        <p class="booking-shell-kicker">Pembuatan booking</p>
                        <h1 class="booking-shell-title">Susun booking untuk {{ $flight->flight_number }} dengan alur yang jelas.</h1>
                        <p class="booking-shell-copy">
                            {{ $flight->departureAirport->city }} ({{ $flight->departureAirport->code }}) ke
                            {{ $flight->arrivalAirport->city }} ({{ $flight->arrivalAirport->code }}) |
                            {{ $flight->departure_time->format('d M Y H:i') }} - {{ $flight->arrival_time->format('d M Y H:i') }}
                        </p>
                    </div>

                    <div class="booking-shell-summary">
                        <div class="booking-shell-summary-card">
                            <span>Tarif dasar</span>
                            <strong>Rp{{ number_format((float) $flight->price, 0, ',', '.') }}</strong>
                        </div>
                        <div class="booking-shell-summary-card">
                            <span>Kelas terpilih</span>
                            <strong x-text="classLabel(selectedClass)"></strong>
                        </div>
                        <div class="booking-shell-summary-card">
                            <span>Estimasi total</span>
                            <strong x-text="formattedTotal()"></strong>
                        </div>
                    </div>
                </div>
            </article>

            <article class="portal-card">
                <div class="grid gap-3 md:grid-cols-3">
                    <div class="booking-step-card" :class="step === 1 ? 'booking-step-card-active' : ''">
                        <span class="booking-step-index">01</span>
                        <div>
                            <p class="booking-step-title">Data penumpang</p>
                            <p class="booking-step-copy">Pilih penumpang yang akan masuk ke booking ini.</p>
                        </div>
                    </div>
                    <div class="booking-step-card" :class="step === 2 ? 'booking-step-card-active' : ''">
                        <span class="booking-step-index">02</span>
                        <div>
                            <p class="booking-step-title">Kabin dan kursi</p>
                            <p class="booking-step-copy">Tetapkan kelas dan kursi untuk setiap penumpang.</p>
                        </div>
                    </div>
                    <div class="booking-step-card" :class="step === 3 ? 'booking-step-card-active' : ''">
                        <span class="booking-step-index">03</span>
                        <div>
                            <p class="booking-step-title">Tinjau dan konfirmasi</p>
                            <p class="booking-step-copy">Periksa pasangan penumpang, kursi, dan total harga.</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('booking.store') }}" class="mt-6" @submit.prevent="submitForm($event)">
                    @csrf
                    <input type="hidden" name="flight_id" value="{{ $flight->id }}">
                    <input type="hidden" name="seat_class" :value="selectedClass">
                    <div x-ref="hiddenInputs"></div>

                    <div x-show="step === 1" x-cloak>
                        <div class="portal-section-head">
                            <div>
                                <p class="portal-kicker">Step 1</p>
                                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Data penumpang</h2>
                                <p class="portal-section-copy">Susun manifest penumpang booking dari traveler yang sudah tersimpan. Urutan yang kamu buat di sini akan dipakai ke tahap kursi dan ringkasan.</p>
                            </div>
                        </div>

                        @if ($passengers->isEmpty())
                            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-white/70 p-5 text-slate-600">
                                Belum ada passenger. Tambahkan passenger dulu.
                                <div class="mt-4">
                                    <a href="{{ route('passengers.index') }}" class="portal-btn-blue">Tambah Passenger</a>
                                </div>
                            </div>
                        @else
                            <div class="mt-5 grid gap-4 xl:grid-cols-[1.05fr_.95fr]">
                                <div class="space-y-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Traveler library</p>
                                            <p class="mt-1 text-sm text-slate-600">Pilih traveler yang ingin dimasukkan ke booking ini.</p>
                                        </div>
                                        <span class="portal-inline-note">{{ $passengers->count() }} traveler tersimpan</span>
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        @foreach ($passengers as $passenger)
                                            <article
                                                class="portal-card-soft flex flex-col gap-4 transition duration-200"
                                                :class="isSelectedPassenger({{ $passenger->id }}) ? 'ring-2 ring-orange-300 border-orange-200 bg-orange-50/40' : ''"
                                            >
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-slate-800">{{ $passenger->full_name }}</p>
                                                    <p class="mt-1 text-sm text-slate-500">{{ optional($passenger->birth_date)->format('d M Y') }}</p>
                                                    <p class="mt-2 text-xs uppercase tracking-[0.18em] text-slate-400">
                                                        {{ $passenger->identity_number ?: ($passenger->passport_number ?: 'Dokumen belum diisi') }}
                                                    </p>
                                                </div>

                                                <button
                                                    type="button"
                                                    class="portal-btn-blue w-full justify-center"
                                                    @click="togglePassenger({{ $passenger->id }})"
                                                    x-text="isSelectedPassenger({{ $passenger->id }}) ? 'Keluarkan dari Manifest' : 'Masukkan ke Manifest'"
                                                ></button>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="portal-surface-muted space-y-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Manifest booking</p>
                                            <h3 class="mt-1 text-2xl font-bold text-slate-800">Penumpang terpilih</h3>
                                        </div>
                                        <span class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">
                                            <span x-text="selectedPassengers.length"></span> penumpang
                                        </span>
                                    </div>

                                    <template x-if="selectedPassengers.length === 0">
                                        <div class="rounded-[20px] border border-dashed border-slate-300 bg-white/80 px-4 py-5 text-sm leading-7 text-slate-600">
                                            Belum ada traveler di manifest. Tambahkan minimal satu traveler dari library di kiri untuk lanjut ke tahap kursi.
                                        </div>
                                    </template>

                                    <div class="space-y-3" x-show="selectedPassengers.length > 0" x-cloak>
                                        <template x-for="(passengerId, index) in selectedPassengers" :key="passengerId">
                                            <div class="rounded-[20px] border border-slate-200 bg-white px-4 py-4 shadow-[0_8px_20px_rgba(15,23,42,.04)]">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Penumpang <span x-text="index + 1"></span></p>
                                                        <p class="mt-2 font-semibold text-slate-800" x-text="passengerName(passengerId)"></p>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        class="text-sm font-semibold text-[#c2410c]"
                                                        @click="removePassenger(passengerId)"
                                                    >
                                                        Hapus
                                                    </button>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div x-show="step === 2" x-cloak class="space-y-5">
                        <div>
                            <p class="portal-kicker">Step 2</p>
                            <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Pilihan kabin dan peta kursi</h2>
                            <p class="portal-section-copy">Jumlah kursi yang dipilih harus sama dengan jumlah penumpang. Saat ganti kelas kabin, pilihan kursi akan direset agar tetap valid.</p>
                        </div>

                        <div class="grid gap-3 lg:grid-cols-3">
                            @foreach ($seatMap['classes'] as $classKey => $seatClass)
                                <button
                                    type="button"
                                    @click="setClass('{{ $classKey }}')"
                                    class="booking-class-card"
                                    :class="selectedClass === '{{ $classKey }}' ? 'booking-class-card-active' : ''"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-lg font-semibold text-slate-800">{{ $seatClass['label'] }}</p>
                                            <p class="mt-1 text-sm text-slate-500">{{ $seatClass['description'] }}</p>
                                        </div>
                                        <span class="portal-status-default">{{ $availableSeatCounts[$classKey] ?? 0 }}</span>
                                    </div>
                                    <p class="mt-4 text-2xl font-bold text-[#c2410c]">Rp{{ number_format((float) ($classPrices[$classKey] ?? 0), 0, ',', '.') }}</p>
                                </button>
                            @endforeach
                        </div>

                        <div class="space-y-4">
                            @foreach ($seatMap['classes'] as $classKey => $seatClass)
                                <div x-show="selectedClass === '{{ $classKey }}'" x-cloak class="booking-seat-stage">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <p class="portal-kicker">{{ $seatClass['short_label'] }}</p>
                                            <h3 class="mt-2 text-2xl font-bold text-slate-800">{{ $seatClass['label'] }}</h3>
                                        </div>
                                        <div class="grid gap-1 text-sm text-slate-600 sm:text-right">
                                            <span>{{ $seatClass['available_count'] }} kursi tersedia</span>
                                            <span>Harga per penumpang <strong class="text-[#c2410c]">Rp{{ number_format((float) ($classPrices[$classKey] ?? 0), 0, ',', '.') }}</strong></span>
                                        </div>
                                    </div>

                                    @include('partials.seat-map', ['seatClass' => $seatClass, 'interactive' => true, 'classKey' => $classKey])
                                </div>
                            @endforeach
                        </div>

                        <div class="booking-seat-selection">
                            <p class="text-sm text-slate-600">
                                Terpilih <strong x-text="selectedSeats.length"></strong> kursi dari <strong x-text="selectedPassengers.length"></strong> penumpang
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2" x-show="selectedSeats.length > 0" x-cloak>
                                <template x-for="seatLabel in selectedSeatLabels()" :key="seatLabel">
                                    <span class="rounded-full border border-[#c2410c]/20 bg-[#c2410c] px-3 py-1 text-xs font-semibold text-white" x-text="seatLabel"></span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div x-show="step === 3" x-cloak>
                        <p class="portal-kicker">Step 3</p>
                        <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Tinjau ringkasan booking</h2>
                        <p class="portal-section-copy">Pastikan setiap penumpang sudah dipasangkan dengan kursi yang benar sebelum booking dikirim.</p>
                        <div class="mt-4 space-y-3">
                            <template x-for="(item, index) in summaryItems()" :key="index">
                                <div class="booking-review-card">
                                    <div>
                                        <p class="font-semibold text-slate-800" x-text="item.passenger_name"></p>
                                        <p class="text-sm text-slate-500">
                                            Kursi <span x-text="item.seat_number"></span> | <span x-text="item.class_label"></span>
                                        </p>
                                    </div>
                                    <p class="font-semibold text-[#c2410c]" x-text="item.formatted_price"></p>
                                </div>
                            </template>
                        </div>
                    </div>

                    @error('passengers')
                        <p class="mt-4 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('seat_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('seat_class')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
                        <button type="button" class="portal-btn-blue" @click="prevStep()" x-show="step > 1">Kembali</button>
                        <div class="flex items-center gap-3">
                            <button type="button" class="portal-btn-blue" @click="nextStep()" x-show="step < 3">Lanjut</button>
                            <button type="submit" class="portal-btn-gold" x-show="step === 3">Konfirmasi Booking</button>
                        </div>
                    </div>
                </form>
            </article>
        </div>

        <aside class="portal-side-panel booking-live-panel">
            <p class="portal-kicker">Ringkasan langsung</p>
            <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Susunan saat ini</h2>
            <div class="mt-5 space-y-3">
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Kelas terpilih</p>
                    <p class="text-xl font-semibold text-slate-800" x-text="classLabel(selectedClass)"></p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Jumlah penumpang</p>
                    <p class="text-xl font-semibold text-slate-800" x-text="selectedPassengers.length"></p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Jumlah kursi</p>
                    <p class="text-xl font-semibold text-slate-800" x-text="selectedSeats.length"></p>
                </div>
                <div class="portal-card-soft">
                    <p class="text-sm text-slate-500">Estimasi total</p>
                    <p class="text-3xl font-bold text-[#c2410c]" x-text="formattedTotal()"></p>
                </div>
            </div>
        </aside>
    </section>

    <script>
        function bookingWizard(passengers, seats, availableSeatIds, classPrices, initialClass) {
            return {
                step: 1,
                passengers,
                seats,
                availableSeatIds,
                classPrices,
                selectedClass: initialClass,
                selectedPassengers: [],
                selectedSeats: [],

                togglePassenger(passengerId) {
                    const normalizedId = Number(passengerId);
                    const existingIndex = this.selectedPassengers.findIndex((id) => Number(id) === normalizedId);

                    if (existingIndex >= 0) {
                        this.selectedPassengers.splice(existingIndex, 1);
                        if (this.selectedSeats.length > this.selectedPassengers.length) {
                            this.selectedSeats = this.selectedSeats.slice(0, this.selectedPassengers.length);
                        }
                        return;
                    }

                    this.selectedPassengers.push(normalizedId);
                },

                removePassenger(passengerId) {
                    this.togglePassenger(passengerId);
                },

                isSelectedPassenger(passengerId) {
                    return this.selectedPassengers.includes(Number(passengerId));
                },

                passengerName(passengerId) {
                    return this.passengers.find((item) => item.id === Number(passengerId))?.name || '-';
                },

                setClass(classKey) {
                    if (this.selectedClass === classKey) return;
                    this.selectedClass = classKey;
                    this.selectedSeats = [];
                },

                isSeatAvailable(seatId) {
                    const seat = this.seats.find(item => item.id === Number(seatId));
                    return this.availableSeatIds.includes(Number(seatId)) && seat?.class === this.selectedClass;
                },

                seatClass(seatId, available) {
                    if (!available) {
                        return 'border-red-300 bg-red-100 text-red-700 cursor-not-allowed';
                    }

                    if (this.selectedSeats.includes(seatId)) {
                        return 'border-[#c2410c] bg-[#c2410c] text-white ring-2 ring-[#c2410c]/35 shadow-[0_8px_18px_rgba(154,52,18,.35)] scale-[1.03]';
                    }

                    return 'border-emerald-300 bg-emerald-100 text-emerald-700 hover:bg-emerald-200';
                },

                toggleSeat(seatId) {
                    if (!this.isSeatAvailable(seatId)) return;

                    const index = this.selectedSeats.indexOf(seatId);

                    if (index >= 0) {
                        this.selectedSeats.splice(index, 1);
                        return;
                    }

                    if (this.selectedSeats.length >= this.selectedPassengers.length) {
                        alert('Jumlah kursi harus sama dengan jumlah penumpang.');
                        return;
                    }

                    this.selectedSeats.push(seatId);
                },

                classLabel(classKey) {
                    return {
                        first: 'First Class',
                        business: 'Business Class',
                        economy: 'Economy Class',
                    }[classKey] || classKey;
                },

                seatPrice(seatId) {
                    const seat = this.seats.find(item => item.id === Number(seatId));
                    return Number(this.classPrices[seat?.class || this.selectedClass] || 0);
                },

                formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0,
                    }).format(amount);
                },

                nextStep() {
                    if (this.step === 1 && this.selectedPassengers.length === 0) {
                        alert('Pilih minimal satu penumpang.');
                        return;
                    }

                    if (this.step === 2 && this.selectedSeats.length !== this.selectedPassengers.length) {
                        alert('Jumlah kursi harus sama dengan jumlah penumpang.');
                        return;
                    }

                    this.step = Math.min(this.step + 1, 3);
                },

                prevStep() {
                    this.step = Math.max(this.step - 1, 1);
                },

                summaryItems() {
                    return this.selectedPassengers.map((passengerId, index) => {
                        const passenger = this.passengers.find(item => item.id === Number(passengerId));
                        const seat = this.seats.find(item => item.id === Number(this.selectedSeats[index]));
                        const price = this.seatPrice(seat?.id);

                        return {
                            passenger_id: Number(passengerId),
                            passenger_name: passenger?.name || '-',
                            seat_id: seat?.id || null,
                            seat_number: seat?.seat_number || '-',
                            class_label: this.classLabel(seat?.class || this.selectedClass),
                            formatted_price: this.formatCurrency(price),
                            price,
                        };
                    });
                },

                selectedSeatLabels() {
                    return this.selectedSeats
                        .map((seatId) => this.seats.find(item => item.id === Number(seatId))?.seat_number)
                        .filter(Boolean);
                },

                formattedTotal() {
                    const total = this.summaryItems().reduce((sum, item) => sum + item.price, 0);
                    return this.formatCurrency(total);
                },

                submitForm(event) {
                    if (this.selectedSeats.length !== this.selectedPassengers.length) {
                        alert('Jumlah kursi harus sama dengan jumlah penumpang.');
                        return;
                    }

                    const container = this.$refs.hiddenInputs;
                    container.innerHTML = '';

                    this.summaryItems().forEach((item, index) => {
                        const passengerInput = document.createElement('input');
                        passengerInput.type = 'hidden';
                        passengerInput.name = `passengers[${index}][passenger_id]`;
                        passengerInput.value = item.passenger_id;
                        container.appendChild(passengerInput);

                        const seatInput = document.createElement('input');
                        seatInput.type = 'hidden';
                        seatInput.name = `passengers[${index}][seat_id]`;
                        seatInput.value = item.seat_id;
                        container.appendChild(seatInput);
                    });

                    event.target.submit();
                }
            };
        }
    </script>
@endsection
