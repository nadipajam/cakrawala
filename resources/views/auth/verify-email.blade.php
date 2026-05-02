<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-orange-700/80">Verifikasi email</p>
            <h1 class="mt-3 font-heading text-4xl font-extrabold tracking-tight text-slate-900">Verifikasi email Anda</h1>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                Cek inbox email Anda dan klik tautan verifikasi untuk mengaktifkan akun sepenuhnya.
            </p>
        </div>

        @if (session('status') === 'verification-link-sent')
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                Tautan verifikasi baru sudah dikirim ke email Anda.
            </div>
        @endif

        <div class="grid gap-3 sm:grid-cols-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="auth-gold-btn text-lg">
                    Kirim Ulang Email Verifikasi
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="portal-btn-blue w-full justify-center py-3 text-base">
                    Logout
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
