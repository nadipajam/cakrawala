<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-orange-700/80">Pemulihan akun</p>
            <h1 class="mt-3 font-heading text-4xl font-extrabold tracking-tight text-slate-900">Atur ulang password</h1>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">Email</label>
                <div class="auth-field">
                    <span class="auth-field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current"><path d="M3 6.75A2.75 2.75 0 0 1 5.75 4h12.5A2.75 2.75 0 0 1 21 6.75v10.5A2.75 2.75 0 0 1 18.25 20H5.75A2.75 2.75 0 0 1 3 17.25V6.75zm2.54-.5 6.25 5 6.26-5H5.54z"/></svg>
                    </span>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@example.com" class="auth-input">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm" />
            </div>

            <button type="submit" class="auth-gold-btn text-xl">
                Kirim Tautan Reset
            </button>
        </form>

        <p class="text-center text-sm text-slate-500">
            Sudah ingat password?
            <a href="{{ route('login') }}" class="font-semibold text-orange-700 hover:text-orange-800">Kembali ke login</a>
        </p>
    </div>
</x-guest-layout>
