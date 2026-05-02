@extends('layouts.admin')

@section('title', 'Tambah Kursi | Cakrawala')
@section('page-title', 'Tambah Kursi')

@section('content')
    <section class="admin-form-shell">
        <article class="admin-form-card max-w-3xl">
        <form method="POST" action="{{ route('admin.seats.store') }}" class="grid gap-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-3">
                <div class="sm:col-span-3">
                    <label class="admin-label" for="airplane_id">Airplane</label>
                    <select id="airplane_id" name="airplane_id" class="admin-field" required>
                        <option value="">Pilih airplane</option>
                        @foreach ($airplanes as $airplane)
                            <option value="{{ $airplane->id }}" @selected((int) old('airplane_id') === (int) $airplane->id)>{{ $airplane->airline?->name }} - {{ $airplane->model }} ({{ $airplane->registration_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="seat_number">Seat Number</label>
                    <input id="seat_number" name="seat_number" value="{{ old('seat_number') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="class">Class</label>
                    <select id="class" name="class" class="admin-field" required>
                        <option value="economy" @selected(old('class') === 'economy')>Economy</option>
                        <option value="business" @selected(old('class') === 'business')>Business</option>
                        <option value="first" @selected(old('class') === 'first')>First</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button class="admin-btn-primary" type="submit">Save Seat</button>
                <a href="{{ route('admin.seats.index') }}" class="admin-btn-secondary">Cancel</a>
            </div>
        </form>
        </article>
    </section>
@endsection
