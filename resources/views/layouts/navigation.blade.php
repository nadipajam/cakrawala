<nav x-data="{ open: false }" class="portal-print-hide">
    <div class="app-topbar">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ route('dashboard') }}" class="portal-brand">
                <span class="portal-brand-mark" aria-hidden="true">
                    <x-application-logo class="h-5 w-5 shrink-0 rounded object-contain" />
                </span>
                <span>
                    <strong class="portal-brand-title">{{ strtoupper(config('app.name', 'Cakrawala')) }}</strong>
                    <small class="portal-brand-subtitle">Area pribadi</small>
                </span>
            </a>

            <div class="app-nav-cluster">
                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="app-nav-link">
                    {{ __('Dashboard') }}
                </x-nav-link>
                <x-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')" class="app-nav-link">
                    {{ __('Profil') }}
                </x-nav-link>
            </div>
        </div>

        <div class="hidden items-center gap-2 sm:flex">
            <div class="app-user-chip">
                <span class="truncate">{{ Auth::user()->name }}</span>
            </div>

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="portal-action-btn" type="button">
                        <span>{{ __('Account') }}</span>
                        <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Profil') }}
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>

        <button @click="open = ! open" class="portal-mobile-toggle sm:hidden" type="button" aria-label="Toggle account navigation">
            <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="app-main-wrap hidden sm:hidden">
        <div class="portal-mobile-panel">
            <div class="border-b border-orange-100 px-4 pb-3">
                <div class="font-semibold text-slate-800">{{ Auth::user()->name }}</div>
                <div class="text-sm text-slate-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 grid gap-1">
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="portal-mobile-link">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')" class="portal-mobile-link">
                    {{ __('Profil') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            class="portal-mobile-link"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
