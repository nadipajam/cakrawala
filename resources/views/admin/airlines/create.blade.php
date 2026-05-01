@extends('layouts.admin')

@section('title', 'Create Airline | Cakrawala')
@section('page-title', 'Create Airline')

@section('content')
    <section class="admin-form-shell">
        <article class="admin-form-card max-w-3xl">
        <form method="POST" action="{{ route('admin.airlines.store') }}" enctype="multipart/form-data" class="grid gap-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="admin-label" for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name') }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="code">Code</label>
                    <input id="code" name="code" value="{{ old('code') }}" class="admin-field" required>
                </div>
                <div class="sm:col-span-2">
                    <label class="admin-label" for="logo">Logo (optional)</label>
                    <input id="logo" name="logo" type="file" accept="image/*" class="admin-field">
                </div>
                <div class="sm:col-span-2">
                    <label class="admin-label" for="description">Description</label>
                    <textarea id="description" name="description" class="admin-field" rows="4">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <button class="admin-btn-primary" type="submit">Save Airline</button>
                <a href="{{ route('admin.airlines.index') }}" class="admin-btn-secondary">Cancel</a>
            </div>
        </form>
        </article>
    </section>
@endsection
