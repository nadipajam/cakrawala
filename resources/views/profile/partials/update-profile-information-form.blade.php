<section>
    <header class="space-y-2">
        <p class="portal-kicker">Profile editor</p>
        <h2 class="font-heading text-3xl font-bold text-[#c2410c]">
            {{ __('Profile Information') }}
        </h2>
        <p class="text-sm leading-7 text-slate-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="portal-label">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="portal-input">
            <x-input-error class="mt-2 text-sm" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="portal-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="portal-input">
            <x-input-error class="mt-2 text-sm" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="mt-2 text-sm text-slate-700">
                        {{ __('Your email address is unverified.') }}

                        <button type="submit" form="send-verification" class="font-semibold text-orange-700 underline decoration-orange-300 underline-offset-4 transition hover:text-orange-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm font-medium text-emerald-700">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <label for="phone" class="portal-label">{{ __('Phone') }}</label>
            <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" autocomplete="tel" class="portal-input">
            <x-input-error class="mt-2 text-sm" :messages="$errors->get('phone')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="portal-btn-gold">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-emerald-700"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
