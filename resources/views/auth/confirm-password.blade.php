<x-guest-layout>
    <div class="space-y-6">
        <div class="text-center">
            <p class="text-xs font-semibold uppercase tracking-[0.3em] text-orange-700/80">Security checkpoint</p>
            <h1 class="mt-3 font-heading text-4xl font-extrabold tracking-tight text-slate-900">Confirm your password</h1>
            <p class="mt-3 text-sm leading-7 text-slate-600">
                Area ini membutuhkan verifikasi ulang sebelum Anda melanjutkan.
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5" x-data="{ showPassword: false }">
            @csrf

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-slate-700">Password</label>
                <div class="auth-field">
                    <span class="auth-field-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 fill-current"><path d="M17 9h-1V7a4 4 0 1 0-8 0v2H7a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2zm-6 7.73V18h2v-1.27a2 2 0 1 0-2 0zM10 9V7a2 2 0 1 1 4 0v2h-4z"/></svg>
                    </span>
                    <input id="password" name="password" :type="showPassword ? 'text' : 'password'" required autocomplete="current-password" placeholder="Enter your password" class="auth-input">
                    <button type="button" @click="showPassword = !showPassword" class="text-slate-500 transition hover:text-orange-700">
                        <svg x-show="!showPassword" viewBox="0 0 24 24" class="h-5 w-5 fill-current"><path d="M12 5c4.8 0 8.9 2.7 11 7-2.1 4.3-6.2 7-11 7s-8.9-2.7-11-7c2.1-4.3 6.2-7 11-7zm0 2C8.5 7 5.4 8.8 3.7 12 5.4 15.2 8.5 17 12 17s6.6-1.8 8.3-5C18.6 8.8 15.5 7 12 7zm0 2.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5z"/></svg>
                        <svg x-show="showPassword" x-cloak viewBox="0 0 24 24" class="h-5 w-5 fill-current"><path d="M2.3 1.3 1 2.6l4.1 4.1C3.3 8 1.9 9.8 1 12c2.1 4.3 6.2 7 11 7 2 0 3.9-.5 5.5-1.3l3.2 3.2 1.3-1.3L2.3 1.3zm9.7 15.7c-3.5 0-6.6-1.8-8.3-5 .7-1.4 1.7-2.6 2.9-3.4l1.8 1.8a3.5 3.5 0 0 0 4.2 4.2l1.5 1.5c-.7.2-1.4.4-2.1.4zm9.3-5c-.9-1.9-2.3-3.6-4.1-4.8l-1.5 1.5a8.8 8.8 0 0 1 2.6 3.3 9.2 9.2 0 0 1-1.7 2.4l1.4 1.4c1.3-1 2.4-2.4 3.3-3.8z"/></svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm" />
            </div>

            <button type="submit" class="auth-gold-btn text-xl">
                Confirm
            </button>
        </form>
    </div>
</x-guest-layout>
