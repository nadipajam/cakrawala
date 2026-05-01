@extends('layouts.portal')

@section('title', 'Cakrawala | Passengers')
@section('active', 'passengers')

@section('content')
    <section class="grid gap-6 lg:grid-cols-[1fr_390px]">
        <div class="space-y-6">
            <article class="support-hero-panel">
                <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
                    <div>
                        <p class="booking-shell-kicker">Passenger vault</p>
                        <h1 class="booking-shell-title">Simpan data traveler sebagai katalog siap pakai untuk booking berikutnya.</h1>
                        <p class="booking-shell-copy">Halaman ini tidak lagi terasa seperti daftar form biasa. Fokusnya sekarang ke penyimpanan identitas traveler yang rapi, cepat dibaca, dan mudah diedit saat dibutuhkan.</p>
                    </div>
                    <div class="support-summary-grid">
                        <div class="support-summary-card">
                            <span>Total profiles</span>
                            <strong>{{ $passengers->count() }}</strong>
                        </div>
                        <div class="support-summary-card">
                            <span>Ready for booking</span>
                            <strong>{{ $passengers->count() }}</strong>
                        </div>
                    </div>
                </div>
            </article>

            <div class="space-y-4">
                @forelse ($passengers as $passenger)
                    <article class="passenger-vault-card">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-semibold text-slate-800">{{ $passenger->full_name }}</h2>
                                <p class="mt-1 text-sm text-slate-600">{{ ucfirst($passenger->gender) }} | {{ optional($passenger->birth_date)->format('d M Y') }}</p>
                            </div>
                            <form method="POST" action="{{ route('passengers.destroy', $passenger) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="portal-btn-blue px-4 py-2">Delete</button>
                            </form>
                        </div>

                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                            <div class="portal-card-soft">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Identity</p>
                                <p class="mt-2 font-semibold text-slate-800">{{ $passenger->identity_number ?: '-' }}</p>
                            </div>
                            <div class="portal-card-soft">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Passport</p>
                                <p class="mt-2 font-semibold text-slate-800">{{ $passenger->passport_number ?: '-' }}</p>
                            </div>
                            <div class="portal-card-soft">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-500">Nationality</p>
                                <p class="mt-2 font-semibold text-slate-800">{{ $passenger->nationality ?: '-' }}</p>
                            </div>
                        </div>

                        <details class="mt-4 passenger-vault-edit">
                            <summary class="cursor-pointer text-sm font-semibold text-[#0f3f78]">Edit Passenger</summary>
                            <form method="POST" action="{{ route('passengers.update', $passenger) }}" class="mt-4 grid gap-3 sm:grid-cols-2">
                                @csrf
                                @method('PUT')
                                <div class="sm:col-span-2">
                                    <label class="portal-label">Full Name</label>
                                    <input type="text" name="full_name" value="{{ $passenger->full_name }}" class="portal-input" required>
                                </div>
                                <div>
                                    <label class="portal-label">Gender</label>
                                    <select name="gender" class="portal-select" required>
                                        <option value="male" @selected($passenger->gender === 'male')>Male</option>
                                        <option value="female" @selected($passenger->gender === 'female')>Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="portal-label">Birth Date</label>
                                    <input type="date" name="birth_date" value="{{ optional($passenger->birth_date)->toDateString() }}" class="portal-input" required>
                                </div>
                                <div>
                                    <label class="portal-label">Identity Number</label>
                                    <input type="text" name="identity_number" value="{{ $passenger->identity_number }}" class="portal-input">
                                </div>
                                <div>
                                    <label class="portal-label">Passport Number</label>
                                    <input type="text" name="passport_number" value="{{ $passenger->passport_number }}" class="portal-input">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="portal-label">Nationality</label>
                                    <input type="text" name="nationality" value="{{ $passenger->nationality }}" class="portal-input">
                                </div>
                                <div class="sm:col-span-2">
                                    <button type="submit" class="portal-btn-gold">Save Changes</button>
                                </div>
                            </form>
                        </details>
                    </article>
                @empty
                    <div class="portal-card text-center text-slate-600">
                        No passenger yet.
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="support-rail">
            <div class="support-rail-card">
                <p class="portal-kicker">New entry</p>
                <h2 class="font-heading text-2xl font-bold text-[#0f3f78]">Add passenger</h2>
                @if ($errors->any())
                    <div class="mt-3 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif
                <form method="POST" action="{{ route('passengers.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label class="portal-label">Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" class="portal-input" required>
                        @error('full_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="portal-label">Gender</label>
                        <select name="gender" class="portal-select" required>
                            <option value="male" @selected(old('gender') === 'male')>Male</option>
                            <option value="female" @selected(old('gender') === 'female')>Female</option>
                        </select>
                        @error('gender')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="portal-label">Birth Date</label>
                        <input type="date" name="birth_date" value="{{ old('birth_date') }}" class="portal-input" required>
                        @error('birth_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="portal-label">Identity Number</label>
                        <input type="text" name="identity_number" value="{{ old('identity_number') }}" class="portal-input">
                        @error('identity_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="portal-label">Passport Number</label>
                        <input type="text" name="passport_number" value="{{ old('passport_number') }}" class="portal-input">
                        @error('passport_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="portal-label">Nationality</label>
                        <input type="text" name="nationality" value="{{ old('nationality') }}" class="portal-input">
                        @error('nationality')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="portal-btn-gold">Add Passenger</button>
                </form>
            </div>
        </aside>
    </section>
@endsection
