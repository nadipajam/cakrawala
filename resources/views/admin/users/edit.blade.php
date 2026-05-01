@extends('layouts.admin')

@section('title', 'Edit User | Cakrawala')
@section('page-title', 'Edit User')

@section('content')
    <section class="space-y-6">
        <article class="admin-form-card">
            <div class="admin-section-head">
                <div>
                    <p class="admin-section-kicker">User Management</p>
                    <h2 class="admin-section-title">Edit {{ $user->name }}</h2>
                    <p class="admin-section-copy">Perbarui role, data kontak, dan metadata backoffice sesuai struktur organisasi Cakrawala.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="mt-5">
                @php($method = 'PUT')
                @php($submitLabel = 'Save Changes')
                @include('admin.users._form')
            </form>
        </article>
    </section>
@endsection
