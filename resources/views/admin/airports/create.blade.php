@extends('layouts.admin')

@section('title', 'Tambah Bandara | Cakrawala')
@section('page-title', 'Tambah Bandara')

@section('content')
    <section class="admin-form-shell">
        <article class="admin-form-card max-w-3xl">
        <form method="POST" action="{{ route('admin.airports.store') }}" class="grid gap-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="admin-label" for="code">Code</label>
                    <input id="code" name="code" value="{{ old('code') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="city">City</label>
                    <input id="city" name="city" value="{{ old('city') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="country">Country</label>
                    <input id="country" name="country" value="{{ old('country') }}" class="admin-field" required>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button class="admin-btn-primary" type="submit">Save Airport</button>
                <a href="{{ route('admin.airports.index') }}" class="admin-btn-secondary">Cancel</a>
            </div>
        </form>
        </article>
    </section>
@endsection
