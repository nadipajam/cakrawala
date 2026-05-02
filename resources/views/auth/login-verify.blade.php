<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Verifikasi OTP - Cakrawala</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-sans antialiased text-slate-700">
        <main class="auth-page-shell">
            <section class="portal-auth-split">
                <div class="auth-card">
                    <div class="mb-6 flex items-center justify-between gap-3">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-orange-700">Ubah email/nomor</a>
                        <a href="{{ route('register') }}" class="text-sm font-semibold text-slate-600 hover:text-orange-700">Daftar</a>
                    </div>

                    <div class="mb-8 text-center">
                        <p class="text-xs font-semibold uppercase tracking-[0.3em] text-orange-700/80">Verifikasi</p>
                        <h1 class="mt-3 font-heading text-5xl font-extrabold tracking-tight text-slate-900">Masukkan kode OTP</h1>
                        <p class="mt-3 text-base leading-7 text-slate-600">
                            Kode telah dikirim ke email Anda.
                        </p>
                    </div>

                    @if (session('status'))
                        <div class="mb-5 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.verify') }}" class="space-y-5">
                        @csrf
                        <input type="hidden" name="identifier" value="{{ old('identifier', $identifier) }}">

                        <div>
                            <label for="code" class="mb-2 block text-sm font-medium text-slate-700">Kode OTP (6 digit)</label>
                            <div class="auth-field">
                                <span class="auth-field-icon">
                                    <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current"><path d="M17 9h-1V7a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2zm-6 7.73V18h2v-1.27a2 2 0 1 0-2 0zM10 9V7a2 2 0 1 1 4 0v2h-4z"/></svg>
                                </span>
                                <input id="code" name="code" type="text" inputmode="numeric" maxlength="6" required autofocus placeholder="123456" class="auth-input">
                            </div>
                            <x-input-error :messages="$errors->get('code')" class="mt-2 text-sm" />
                        </div>

                        <div class="flex items-center justify-between gap-3 text-sm">
                            <label for="remember_me" class="inline-flex items-center gap-2 text-slate-600">
                                <input id="remember_me" name="remember" type="checkbox" @checked(old('remember')) class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                                <span>Ingat Saya</span>
                            </label>
                            <a href="{{ route('login') }}" class="font-semibold text-orange-700 hover:text-orange-800">Kirim kode baru</a>
                        </div>

                        <button type="submit" class="auth-gold-btn text-2xl">
                            Verifikasi & Masuk
                        </button>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
