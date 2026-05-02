@extends('layouts.admin')

@section('title', 'Tambah Pesawat | Cakrawala')
@section('page-title', 'Tambah Pesawat')

@section('content')
    <section class="admin-form-shell">
        <article class="admin-form-card max-w-4xl">
        <form method="POST" action="{{ route('admin.airplanes.store') }}" enctype="multipart/form-data" class="grid gap-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-label" for="airline_id">Airline</label>
                    <select id="airline_id" name="airline_id" class="admin-field" required>
                        <option value="">Pilih airline</option>
                        @foreach ($airlines as $airline)
                            <option value="{{ $airline->id }}" @selected((int) old('airline_id') === (int) $airline->id)>{{ $airline->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="model">Model</label>
                    <input id="model" name="model" value="{{ old('model') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="registration_number">Registration Number</label>
                    <input id="registration_number" name="registration_number" value="{{ old('registration_number') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="capacity">Capacity</label>
                    <input id="capacity" name="capacity" type="number" min="1" value="{{ old('capacity') }}" class="admin-field" required>
                </div>
                <div class="md:col-span-2">
                    <label class="admin-label" for="photo">Photo (optional)</label>
                    <input id="photo" name="photo" type="file" accept="image/*" class="admin-field">
                </div>
                <div class="md:col-span-2">
                    <label class="admin-label" for="description">Description</label>
                    <textarea id="description" name="description" rows="4" class="admin-field">{{ old('description') }}</textarea>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button class="admin-btn-primary" type="submit">Save Airplane</button>
                <a href="{{ route('admin.airplanes.index') }}" class="admin-btn-secondary">Cancel</a>
            </div>
        </form>
        </article>
    </section>
@endsection
