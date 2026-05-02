@extends('layouts.portal')

@section('title', 'Cakrawala | Profile')
@section('active', 'profile')

@section('content')
    <section class="space-y-6">
        <article class="support-hero-panel">
            <div class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
                <div>
                    <p class="booking-shell-kicker">Profil akun</p>
                    <h1 class="booking-shell-title">Kelola identitas akun, kontak, dan keamanan dari satu halaman.</h1>
                    <p class="booking-shell-copy">Profil, kata sandi, dan penghapusan akun dipisahkan dengan jelas agar pengaturan lebih aman dan mudah dipahami.</p>
                </div>
                <div class="support-summary-grid">
                    <div class="support-summary-card">
                        <span>Role</span>
                        <strong>{{ $user->roleLabel() }}</strong>
                    </div>
                    <div class="support-summary-card">
                        <span>Primary email</span>
                        <strong class="text-base sm:text-lg">{{ $user->email }}</strong>
                    </div>
                </div>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
            <article class="portal-card">
                <p class="portal-kicker">Data profil</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Perbarui Profil</h2>
                <form method="POST" action="{{ route('profile.update') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="portal-label">Nama</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" class="portal-input" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="portal-label">Telepon</label>
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
                    <button type="submit" class="portal-btn-gold">Simpan Profil</button>
                </form>
            </article>

            <aside class="support-rail">
                <div class="support-rail-card">
                    <p class="portal-kicker">Ringkasan akun</p>
                    <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Data Saat Ini</h2>
                    <div class="mt-5 space-y-3">
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Nama</p>
                            <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                        </div>
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Email</p>
                            <p class="font-semibold text-slate-800">{{ $user->email }}</p>
                        </div>
                        <div class="portal-card-soft">
                            <p class="text-sm text-slate-500">Telepon</p>
                            <p class="font-semibold text-slate-800">{{ $user->phone ?: '-' }}</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <article class="portal-card">
                <p class="portal-kicker">Keamanan akun</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-[#c2410c]">Ubah Kata Sandi</h2>
                <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="portal-label">Kata Sandi Saat Ini</label>
                        <input type="password" name="current_password" class="portal-input" required>
                        @error('current_password', 'updatePassword')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="portal-label">Kata Sandi Baru</label>
                            <input type="password" name="password" class="portal-input" required>
                            @error('password', 'updatePassword')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="portal-label">Konfirmasi Kata Sandi</label>
                            <input type="password" name="password_confirmation" class="portal-input" required>
                        </div>
                    </div>
                    <button type="submit" class="portal-btn-gold">Perbarui Kata Sandi</button>
                </form>
            </article>

            <article class="portal-card">
                <p class="portal-kicker">Penutupan akun</p>
                <h2 class="mt-2 font-heading text-3xl font-bold text-red-700">Hapus Akun</h2>
                <p class="mt-2 max-w-2xl text-sm leading-7 text-slate-600">Tindakan ini permanen. Semua akses akun akan hilang dan Anda harus memasukkan password untuk konfirmasi terakhir.</p>
                <form method="POST" action="{{ route('profile.destroy') }}" class="mt-6 space-y-4">
                    @csrf
                    @method('DELETE')
                    <div>
                        <label class="portal-label">Kata Sandi</label>
                        <input type="password" name="password" class="portal-input" required>
                        @error('password', 'userDeletion')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="portal-btn-blue border-red-200 bg-red-50 text-red-700 hover:bg-red-100">Hapus Akun</button>
                </form>
            </article>
        </div>
    </section>
@endsection
