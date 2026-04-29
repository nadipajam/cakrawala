@extends('layouts.admin')

@section('title', 'Admin Profile | Cakrawala')
@section('page-title', 'Profile')

@section('content')
    <section class="grid gap-6 xl:grid-cols-2">
        <article class="admin-card">
            <h2 class="font-heading text-xl font-bold text-[#0f3f78]">Update Profile</h2>
            <form method="POST" action="{{ route('admin.profile.update') }}" class="mt-4 grid gap-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="admin-label" for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name', $admin->name) }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="phone">Phone</label>
                    <input id="phone" name="phone" value="{{ old('phone', $admin->phone) }}" class="admin-field">
                </div>
                <button class="admin-btn-primary" type="submit">Save Profile</button>
            </form>
        </article>

        <article class="admin-card">
            <h2 class="font-heading text-xl font-bold text-[#0f3f78]">Change Password</h2>
            <form method="POST" action="{{ route('admin.profile.password') }}" class="mt-4 grid gap-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="admin-label" for="current_password">Current Password</label>
                    <input id="current_password" name="current_password" type="password" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="password">New Password</label>
                    <input id="password" name="password" type="password" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="password_confirmation">Confirm New Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="admin-field" required>
                </div>
                <button class="admin-btn-primary" type="submit">Update Password</button>
            </form>
        </article>
    </section>
@endsection
