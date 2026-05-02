@extends('layouts.admin')

@section('title', 'Tambah Pengguna | Cakrawala')
@section('page-title', 'Tambah Pengguna')

@section('content')
    <section class="space-y-6">
        <article class="admin-form-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">User Management</p>
                    <h2 class="admin-section-title">Tambah akun baru</h2>
                    <p class="admin-section-copy">Buat akun customer atau backoffice baru tanpa keluar dari panel operasional.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="mt-5">
                @php($method = 'POST')
                @php($submitLabel = 'Tambah Pengguna')
                @include('admin.users._form')
            </form>
        </article>
    </section>
@endsection
