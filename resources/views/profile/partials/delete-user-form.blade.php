<section class="space-y-6">
    <header class="space-y-2">
        <p class="portal-kicker">Account removal</p>
        <h2 class="font-heading text-3xl font-bold text-red-700">
            {{ __('Delete Account') }}
        </h2>

        <p class="text-sm leading-7 text-slate-600">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}" class="space-y-4 rounded-[24px] border border-red-200 bg-red-50/70 p-5">
        @csrf
        @method('delete')

        <p class="text-sm font-medium text-red-800">
            {{ __('Please enter your password to permanently delete this account.') }}
        </p>

        <div>
            <label for="password" class="portal-label text-red-700">{{ __('Password') }}</label>
            <input id="password" name="password" type="password" placeholder="{{ __('Password') }}" class="portal-input border-red-200 bg-white">
            <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2 text-sm" />
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="portal-btn-blue border-red-300 bg-red-100 text-red-700 hover:bg-red-200">
                {{ __('Delete Account') }}
            </button>
            <span class="text-xs font-medium uppercase tracking-[0.18em] text-red-600">Permanent action</span>
        </div>
    </form>
</section>
