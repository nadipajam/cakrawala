@extends('layouts.admin')

@section('title', 'Tambah Penerbangan | Cakrawala')
@section('page-title', 'Tambah Penerbangan')

@section('content')
    <section class="admin-card">
        <form method="POST" action="{{ route('admin.flights.store') }}" class="grid gap-4">
            @csrf
            <div class="grid gap-4 lg:grid-cols-3">
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
                    <label class="admin-label" for="airplane_id">Airplane</label>
                    <select id="airplane_id" name="airplane_id" class="admin-field" required>
                        <option value="">Pilih airplane</option>
                        @foreach ($airplanes as $airplane)
                            <option value="{{ $airplane->id }}" @selected((int) old('airplane_id') === (int) $airplane->id)>{{ $airplane->model }} ({{ $airplane->registration_number }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="flight_number">Flight Number</label>
                    <input id="flight_number" name="flight_number" value="{{ old('flight_number') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="departure_airport_id">Departure Airport</label>
                    <select id="departure_airport_id" name="departure_airport_id" class="admin-field" required>
                        <option value="">Pilih airport</option>
                        @foreach ($airports as $airport)
                            <option value="{{ $airport->id }}" @selected((int) old('departure_airport_id') === (int) $airport->id)>{{ $airport->code }} - {{ $airport->city }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="arrival_airport_id">Arrival Airport</label>
                    <select id="arrival_airport_id" name="arrival_airport_id" class="admin-field" required>
                        <option value="">Pilih airport</option>
                        @foreach ($airports as $airport)
                            <option value="{{ $airport->id }}" @selected((int) old('arrival_airport_id') === (int) $airport->id)>{{ $airport->code }} - {{ $airport->city }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="price">Price</label>
                    <input id="price" name="price" type="number" min="0" step="0.01" value="{{ old('price') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="departure_time">Departure Time</label>
                    <input id="departure_time" name="departure_time" type="datetime-local" value="{{ old('departure_time') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="arrival_time">Arrival Time</label>
                    <input id="arrival_time" name="arrival_time" type="datetime-local" value="{{ old('arrival_time') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="status">Status</label>
                    <select id="status" name="status" class="admin-field" required>
                        <option value="scheduled" @selected(old('status') === 'scheduled')>Scheduled</option>
                        <option value="delayed" @selected(old('status') === 'delayed')>Delayed</option>
                        <option value="cancelled" @selected(old('status') === 'cancelled')>Cancelled</option>
                        <option value="completed" @selected(old('status') === 'completed')>Completed</option>
                    </select>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button class="admin-btn-primary" type="submit">Save Flight</button>
                <a href="{{ route('admin.flights.index') }}" class="admin-btn-secondary">Cancel</a>
            </div>
        </form>
    </section>
@endsection
