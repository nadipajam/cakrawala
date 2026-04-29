@extends('layouts.portal')

@section('title', 'Cakrawala | Passengers')
@section('active', 'passengers')

@section('content')
    <section class="grid gap-6 lg:grid-cols-[1fr_380px]">
        <div>
            <article class="portal-card mb-4">
                <p class="portal-kicker">Passenger data</p>
                <h1 class="portal-section-title">Passenger Management</h1>
                <p class="portal-section-copy">Kelola profil penumpang yang tersimpan agar proses booking berikutnya lebih cepat dan konsisten.</p>
            </article>

            <div class="space-y-4">
                @forelse ($passengers as $passenger)
                    <article class="portal-card">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-semibold text-slate-800">{{ $passenger->full_name }}</h2>
                                <p class="text-sm text-slate-600">{{ ucfirst($passenger->gender) }} | {{ optional($passenger->birth_date)->format('d M Y') }}</p>
                                <p class="text-sm text-slate-600">NIK/Passport: {{ $passenger->identity_number ?: ($passenger->passport_number ?: '-') }}</p>
                            </div>
                            <form method="POST" action="{{ route('passengers.destroy', $passenger) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="portal-btn-blue px-4 py-2">Delete</button>
                            </form>
                        </div>

                        <details class="mt-4">
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

        <aside class="portal-side-panel">
            <p class="portal-kicker">New entry</p>
            <h2 class="font-heading text-2xl font-bold text-[#0f3f78]">Add Passenger</h2>
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
        </aside>
    </section>
@endsection
