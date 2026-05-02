@extends('layouts.admin')

@section('title', 'Admin Profile | Cakrawala')
@section('page-title', 'Profil')

@section('content')
    <section class="space-y-6">
        <article class="admin-ops-hero">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">Profil admin</p>
                    <h2 class="admin-section-title">Kelola identitas backoffice dan keamanan akses dari satu halaman.</h2>
                    <p class="admin-section-copy">Bagian ini memusatkan pengaturan akun operator tanpa mencampur data transaksi.</p>
                </div>
                <span class="admin-chip">{{ $admin->roleLabel() }}</span>
            </div>
        </article>

        <div class="grid gap-6 xl:grid-cols-2">
        <article class="admin-form-card">
            <h2 class="font-heading text-xl font-bold text-[#c2410c]">Perbarui Profil</h2>
            <form method="POST" action="{{ route('admin.profile.update') }}" class="mt-4 grid gap-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="admin-label" for="name">Nama</label>
                    <input id="name" name="name" value="{{ old('name', $admin->name) }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $admin->email) }}" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="phone">Telepon</label>
                    <input id="phone" name="phone" value="{{ old('phone', $admin->phone) }}" class="admin-field">
                </div>
                <button class="admin-btn-primary" type="submit">Simpan Profil</button>
            </form>
        </article>

        <article class="admin-form-card">
            <h2 class="font-heading text-xl font-bold text-[#c2410c]">Ubah Kata Sandi</h2>
            <form method="POST" action="{{ route('admin.profile.password') }}" class="mt-4 grid gap-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="admin-label" for="current_password">Kata Sandi Saat Ini</label>
                    <input id="current_password" name="current_password" type="password" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="password">Kata Sandi Baru</label>
                    <input id="password" name="password" type="password" class="admin-field" required>
                </div>
                <div>
                    <label class="admin-label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" class="admin-field" required>
                </div>
                <button class="admin-btn-primary" type="submit">Perbarui Kata Sandi</button>
            </form>
        </article>
        </div>
    </section>
@endsection
