@extends('layouts.portal')

@section('title', 'Cakrawala | Profile')
@section('active', 'profile')

@section('content')
    <section class="space-y-6">
        <article class="portal-card">
            <div class="portal-section-head">
                <div>
                    <p class="portal-kicker">Identity center</p>
                    <h1 class="portal-section-title">Profile & Security</h1>
                    <p class="portal-section-copy">Kelola identitas akun, email, nomor kontak, dan keamanan login dari satu area yang lebih rapi.</p>
                </div>
                <span class="portal-inline-note">{{ $user->roleLabel() }}</span>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <article class="portal-card">
                <p class="portal-kicker">Profile editor</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Update Profile</h2>
                <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="portal-label">Name</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="portal-input" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="portal-label">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="portal-input">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="portal-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="portal-input" required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="portal-btn-gold">Save Profile</button>
                </form>
            </article>

            <aside class="portal-side-panel">
                <p class="portal-kicker">Account snapshot</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Account Snapshot</h2>
                <div class="mt-5 space-y-3">
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Name</p>
                        <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Email</p>
                        <p class="font-semibold text-slate-800">{{ $user->email }}</p>
                    </div>
                    <div class="portal-card-soft">
                        <p class="text-sm text-slate-500">Phone</p>
                        <p class="font-semibold text-slate-800">{{ $user->phone ?: '-' }}</p>
                    </div>
                </div>
            </aside>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <article class="portal-card">
                <p class="portal-kicker">Security controls</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#0f3f78]">Change Password</h2>
                <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="portal-label">Current Password</label>
                        <input type="password" name="current_password" class="portal-input" required>
                        @error('current_password', 'updatePassword')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="portal-label">New Password</label>
                            <input type="password" name="password" class="portal-input" required>
                            @error('password', 'updatePassword')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="portal-label">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="portal-input" required>
                        </div>
                    </div>
                    <button type="submit" class="portal-btn-gold">Update Password</button>
                </form>
            </article>

            <article class="portal-card">
                <p class="portal-kicker">Account removal</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-red-700">Delete Account</h2>
                <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600">Tindakan ini permanen. Semua akses akun akan hilang dan Anda harus memasukkan password untuk konfirmasi terakhir.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" class="mt-6 space-y-4">
                    @csrf
                    @method('DELETE')
                    <div>
                        <label class="portal-label">Password</label>
                        <input type="password" name="password" class="portal-input" required>
                        @error('password', 'userDeletion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="portal-btn-blue border-red-200 bg-red-50 text-red-700 hover:bg-red-100">Delete Account</button>
                </form>
            </article>
        </div>
    </section>
@endsection
