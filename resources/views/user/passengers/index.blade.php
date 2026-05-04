@extends('layouts.portal')

@section('title', 'Cakrawala | Penumpang')
@section('active', 'passengers')

@section('content')
    @php
        $passportReadyCount = $passengers->filter(fn ($passenger) => filled($passenger->passport_number))->count();
        $identityReadyCount = $passengers->filter(fn ($passenger) => filled($passenger->identity_number))->count();
        $editingPassengerId = old('editing_passenger_id') ? (int) old('editing_passenger_id') : ($passengers->first()?->id);
    @endphp

    <section
        x-data="{ selectedPassengerId: {{ \Illuminate\Support\Js::from($editingPassengerId) }} }"
        class="space-y-6"
    >
        <article class="support-hero-panel">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
                <div>
                    <p class="booking-shell-kicker">Traveler vault</p>
                    <h1 class="booking-shell-title">Bangun daftar traveler tetap, lalu kelola dari satu katalog.</h1>
                    <p class="booking-shell-copy">Alurnya sekarang dipisah jelas: tambah traveler baru dari builder di atas, lalu pilih traveler yang sudah ada untuk dikelola di panel kanan. Jadi tidak terasa seperti form daftar biasa.</p>
                </div>
                <div class="support-summary-grid">
                    <div class="support-summary-card">
                        <span>Total traveler</span>
                        <strong>{{ $passengers->count() }}</strong>
                    </div>
                    <div class="support-summary-card">
                        <span>Paspor terisi</span>
                        <strong>{{ $passportReadyCount }}</strong>
                    </div>
                    <div class="support-summary-card">
                        <span>Identitas terisi</span>
                        <strong>{{ $identityReadyCount }}</strong>
                    </div>
                    <div class="support-summary-card">
                        <span>Siap booking</span>
                        <strong>{{ $passengers->count() }}</strong>
                    </div>
                </div>
            </div>
        </article>

        <article class="portal-card">
            <div class="portal-section-head">
                <div>
                    <p class="portal-kicker">Traveler builder</p>
                    <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Tambah traveler baru</h2>
                    <p class="portal-section-copy">Isi identitas inti lebih dulu, lalu lengkapi dokumen opsional. Pola ini memisahkan proses tambah data dari proses edit data existing.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <span class="portal-inline-note">1. Identitas inti</span>
                    <span class="portal-inline-note">2. Dokumen opsional</span>
                    <span class="portal-inline-note">3. Simpan ke vault</span>
                </div>
            </div>

            @if ($errors->any() && ! old('editing_passenger_id'))
                <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('passengers.store') }}" class="mt-5 space-y-5">
                @csrf

                <div class="grid gap-4 xl:grid-cols-[1.1fr_.9fr]">
                    <div class="portal-card-soft space-y-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Langkah 1</p>
                            <h3 class="mt-2 text-xl font-semibold text-slate-800">Identitas inti traveler</h3>
                        </div>

                        <div>
                            <label class="portal-label">Nama Lengkap</label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" class="portal-input" placeholder="Contoh: Budi Santoso" required>
                            @error('full_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="portal-label">Jenis Kelamin</label>
                                <select name="gender" class="portal-select" required>
                                    <option value="male" @selected(old('gender') === 'male')>Laki-laki</option>
                                    <option value="female" @selected(old('gender') === 'female')>Perempuan</option>
                                </select>
                                @error('gender')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="portal-label">Tanggal Lahir</label>
                                <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="portal-input" required>
                                @error('birth_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="portal-card-soft space-y-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Langkah 2</p>
                            <h3 class="mt-2 text-xl font-semibold text-slate-800">Dokumen perjalanan</h3>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="portal-label">Nomor Identitas</label>
                                <input type="text" name="identity_number" value="{{ old('identity_number') }}" class="portal-input" placeholder="KTP / NIK">
                                @error('identity_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="portal-label">Nomor Paspor</label>
                                <input type="text" name="passport_number" value="{{ old('passport_number') }}" class="portal-input" placeholder="Opsional untuk international trip">
                                @error('passport_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="portal-label">Kewarganegaraan</label>
                            <input type="text" name="nationality" value="{{ old('nationality') }}" class="portal-input" placeholder="Contoh: Indonesia">
                            @error('nationality')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="rounded-[20px] border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm leading-7 text-slate-600">
                            Traveler bisa disimpan meski dokumen opsional belum lengkap. Kamu bisa pilih traveler itu nanti dari katalog untuk melengkapi paspor atau identitas.
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="portal-btn-gold">Simpan ke Traveler Vault</button>
                    <a href="{{ route('flights.index') }}" class="portal-btn-blue">Cari Flight untuk Booking</a>
                </div>
            </form>
        </article>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <div class="space-y-4">
                <div class="portal-section-head">
                    <div>
                        <p class="portal-kicker">Traveler library</p>
                        <h2 class="portal-section-title">Pilih traveler yang ingin dikelola</h2>
                        <p class="portal-section-copy">Klik salah satu kartu untuk membuka panel kelola. Hapus traveler hanya jika benar-benar tidak lagi dipakai.</p>
                    </div>
                </div>

                @if ($passengers->isEmpty())
                    <div class="portal-card text-center text-slate-600">
                        Belum ada traveler di vault. Tambahkan traveler pertama dari builder di atas.
                    </div>
                @else
                    <div class="grid gap-4 lg:grid-cols-2">
                        @foreach ($passengers as $passenger)
                            @php
                                $profileReady = filled($passenger->identity_number) || filled($passenger->passport_number);
                            @endphp
                            <article
                                class="passenger-vault-card cursor-pointer transition duration-200"
                                :class="selectedPassengerId === {{ $passenger->id }} ? 'ring-2 ring-orange-300' : ''"
                                @click="selectedPassengerId = {{ $passenger->id }}"
                            >
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h2 class="text-xl font-semibold text-slate-800">{{ $passenger->full_name }}</h2>
                                        <p class="mt-1 text-sm text-slate-600">
                                            {{ ucfirst($passenger->gender) }} | {{ optional($passenger->birth_date)->format('d M Y') }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold {{ $profileReady ? 'border border-emerald-200 bg-emerald-50 text-emerald-700' : 'border border-amber-200 bg-amber-50 text-amber-700' }}">
                                        {{ $profileReady ? 'Dokumen siap' : 'Perlu dilengkapi' }}
                                    </span>
                                </div>

                                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div class="portal-card-soft">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Identitas</p>
                                        <p class="mt-2 font-semibold text-slate-800">{{ $passenger->identity_number ?: '-' }}</p>
                                    </div>
                                    <div class="portal-card-soft">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Paspor</p>
                                        <p class="mt-2 font-semibold text-slate-800">{{ $passenger->passport_number ?: '-' }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                    <p class="text-sm text-slate-500">Kewarganegaraan: <span class="font-semibold text-slate-700">{{ $passenger->nationality ?: '-' }}</span></p>
                                    <button type="button" class="text-sm font-semibold text-[#c2410c]">
                                        Kelola traveler
                                    </button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>

            <aside class="support-rail">
                <div class="support-rail-card space-y-4">
                    <div>
                        <p class="portal-kicker">Panel kelola</p>
                        <h2 class="font-heading text-2xl font-bold text-[#c2410c]">Traveler terpilih</h2>
                    </div>

                    @if ($passengers->isEmpty())
                        <div class="rounded-[20px] border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm leading-7 text-slate-600">
                            Belum ada traveler untuk dikelola. Tambahkan traveler baru dari builder di atas.
                        </div>
                    @else
                        @foreach ($passengers as $passenger)
                            <div x-show="selectedPassengerId === {{ $passenger->id }}" x-cloak class="space-y-4">
                                @if ($errors->any() && (int) old('editing_passenger_id') === (int) $passenger->id)
                                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                        {{ $errors->first() }}
                                    </div>
                                @endif

                                <div class="portal-card-soft">
                                    <p class="text-sm text-slate-500">Traveler aktif</p>
                                    <p class="mt-2 text-xl font-semibold text-slate-800">{{ $passenger->full_name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ ucfirst($passenger->gender) }} | {{ optional($passenger->birth_date)->format('d M Y') }}
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('passengers.update', $passenger) }}" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="editing_passenger_id" value="{{ $passenger->id }}">

                                    <div>
                                        <label class="portal-label">Nama Lengkap</label>
                                        <input
                                            type="text"
                                            name="full_name"
                                            value="{{ (int) old('editing_passenger_id') === (int) $passenger->id ? old('full_name') : $passenger->full_name }}"
                                            class="portal-input"
                                            required
                                        >
                                        @if ((int) old('editing_passenger_id') === (int) $passenger->id)
                                            @error('full_name')
                                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                            @enderror
                                        @endif
                                    </div>

                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="portal-label">Jenis Kelamin</label>
                                            @php($selectedGender = (int) old('editing_passenger_id') === (int) $passenger->id ? old('gender') : $passenger->gender)
                                            <select name="gender" class="portal-select" required>
                                                <option value="male" @selected($selectedGender === 'male')>Laki-laki</option>
                                                <option value="female" @selected($selectedGender === 'female')>Perempuan</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="portal-label">Tanggal Lahir</label>
                                            <input
                                                type="date"
                                                name="birth_date"
                                                value="{{ (int) old('editing_passenger_id') === (int) $passenger->id ? old('birth_date') : optional($passenger->birth_date)->toDateString() }}"
                                                class="portal-input"
                                                required
                                            >
                                        </div>
                                    </div>

                                    <div>
                                        <label class="portal-label">Nomor Identitas</label>
                                        <input
                                            type="text"
                                            name="identity_number"
                                            value="{{ (int) old('editing_passenger_id') === (int) $passenger->id ? old('identity_number') : $passenger->identity_number }}"
                                            class="portal-input"
                                        >
                                    </div>

                                    <div>
                                        <label class="portal-label">Nomor Paspor</label>
                                        <input
                                            type="text"
                                            name="passport_number"
                                            value="{{ (int) old('editing_passenger_id') === (int) $passenger->id ? old('passport_number') : $passenger->passport_number }}"
                                            class="portal-input"
                                        >
                                    </div>

                                    <div>
                                        <label class="portal-label">Kewarganegaraan</label>
                                        <input
                                            type="text"
                                            name="nationality"
                                            value="{{ (int) old('editing_passenger_id') === (int) $passenger->id ? old('nationality') : $passenger->nationality }}"
                                            class="portal-input"
                                        >
                                    </div>

                                    <div class="flex flex-wrap gap-3">
                                        <button type="submit" class="portal-btn-gold">Simpan Perubahan</button>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('passengers.destroy', $passenger) }}" onsubmit="return confirm('Hapus traveler ini dari vault?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="portal-btn-blue w-full justify-center">Hapus Traveler</button>
                                </form>
                            </div>
                        @endforeach
                    @endif
                </div>
            </aside>
        </div>
    </section>
@endsection
