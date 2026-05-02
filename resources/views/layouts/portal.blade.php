<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Cakrawala Airline')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    @php
        $currentUser = auth()->user();
        $routeName = optional(request()->route())->getName();
        $customerWorkspace = $currentUser && $currentUser->isCustomer() && (
            request()->routeIs('dashboard') ||
            request()->routeIs('booking.*') ||
            request()->routeIs('my-bookings.*') ||
            request()->routeIs('payments.*') ||
            request()->routeIs('passengers.*') ||
            request()->routeIs('notifications.*') ||
            request()->routeIs('profile.*')
        );

        $workspaceTitle = 'Area Pelanggan';
        $workspaceEyebrow = 'Layanan perjalanan';
        $workspaceCopy = 'Kelola booking, penumpang, pembayaran, check-in, dan tiket dari satu halaman yang terpusat.';

        if (request()->routeIs('dashboard')) {
            $workspaceTitle = 'Ringkasan Perjalanan';
            $workspaceEyebrow = 'Ringkasan';
            $workspaceCopy = 'Lihat ringkasan booking aktif, notifikasi, dan langkah berikutnya untuk perjalanan Anda.';
        } elseif (request()->routeIs('my-bookings.index')) {
            $workspaceTitle = 'Daftar Booking';
            $workspaceEyebrow = 'Booking';
            $workspaceCopy = 'Seluruh booking Anda disusun rapi agar status, nilai, dan tindakan berikutnya mudah dipantau.';
        } elseif (request()->routeIs('my-bookings.show')) {
            $workspaceTitle = 'Detail Booking';
            $workspaceEyebrow = 'Rincian';
            $workspaceCopy = 'Buka satu booking, cek penumpang, add-on, pembayaran, check-in, dan tiket dari satu halaman.';
        } elseif (request()->routeIs('booking.*')) {
            $workspaceTitle = 'Buat Booking';
            $workspaceEyebrow = 'Pemesanan';
            $workspaceCopy = 'Pilih penumpang, kabin, dan kursi dengan alur yang terstruktur sebelum konfirmasi.';
        } elseif (request()->routeIs('payments.*')) {
            $workspaceTitle = 'Pembayaran';
            $workspaceEyebrow = 'Transaksi';
            $workspaceCopy = 'Pantau pengajuan pembayaran, QRIS, dan status verifikasi untuk setiap booking.';
        } elseif (request()->routeIs('passengers.*')) {
            $workspaceTitle = 'Data Penumpang';
            $workspaceEyebrow = 'Penumpang';
            $workspaceCopy = 'Simpan dan perbarui profil penumpang agar proses booking berikutnya lebih cepat.';
        } elseif (request()->routeIs('notifications.*')) {
            $workspaceTitle = 'Pusat Notifikasi';
            $workspaceEyebrow = 'Pembaruan';
            $workspaceCopy = 'Semua update booking, pembayaran, dan layanan purna jual terkumpul di sini.';
        } elseif (request()->routeIs('profile.*')) {
            $workspaceTitle = 'Profil Akun';
            $workspaceEyebrow = 'Profil';
            $workspaceCopy = 'Atur identitas akun, kontak, dan keamanan login dari tampilan yang lebih fokus.';
        } elseif (request()->routeIs('my-bookings.change-requests.*')) {
            $workspaceTitle = 'Permintaan Layanan';
            $workspaceEyebrow = 'Perubahan';
            $workspaceCopy = 'Ajukan refund, reschedule, dan perubahan data perjalanan langsung dari akun pelanggan Anda.';
        }

        $customerMenu = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'match' => 'dashboard', 'hint' => 'Ringkasan perjalanan'],
            ['label' => 'Booking Saya', 'route' => 'my-bookings.index', 'match' => 'my-bookings', 'hint' => 'Booking dan status'],
            ['label' => 'Penumpang', 'route' => 'passengers.index', 'match' => 'passengers', 'hint' => 'Data penumpang'],
            ['label' => 'Notifikasi', 'route' => 'notifications.index', 'match' => 'notifications', 'hint' => 'Pembaruan akun'],
            ['label' => 'Profil', 'route' => 'profile.edit', 'match' => 'profile', 'hint' => 'Akun dan keamanan'],
        ];
    @endphp
    <body class="{{ $customerWorkspace ? 'customer-shell' : 'portal-shell' }} font-sans antialiased text-slate-700">
        @php
            $active = trim($__env->yieldContent('active'));
            $unreadPortalNotifications = $currentUser && $currentUser->isCustomer()
                ? $currentUser->unreadNotifications()->count()
                : 0;
            $portalTone = $customerWorkspace ? 'Layanan Perjalanan' : 'Portal Utama';
        @endphp

        <header class="portal-container relative z-40 py-4 portal-print-hide" x-data="{ mobileOpen: false }">
            <div class="portal-topbar portal-topbar-v2">
                <div class="portal-topbar-main">
                    <a href="{{ route('home') }}" class="portal-brand">
                        <span class="portal-brand-mark" aria-hidden="true">
                            <x-application-logo class="h-5 w-5 shrink-0 rounded object-contain" />
                        </span>
                        <span>
                            <strong class="portal-brand-title">CAKRAWALA</strong>
                            <small class="portal-brand-subtitle">Portal penerbangan</small>
                        </span>
                    </a>

                    <div class="hidden min-w-0 items-center gap-3 xl:flex">
                        <span class="portal-topbar-note">{{ $portalTone }}</span>
                        <span class="portal-topbar-divider"></span>
                        <p class="truncate text-sm text-slate-500">
                            {{ $customerWorkspace ? $workspaceCopy : 'Cari jadwal, lakukan pemesanan, dan lanjutkan ke akun Anda dari satu portal.' }}
                        </p>
                    </div>
                </div>

                <div class="portal-topbar-side">
                    <nav class="portal-nav hidden xl:flex">
                        <a href="{{ route('home') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'home'])>Home</a>
                        <a href="{{ route('flights.index') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'flights'])>Flights</a>
                        @auth
                            @if ($currentUser->isCustomer())
                                <a href="{{ route('my-bookings.index') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'bookings'])>Booking Saya</a>
                                <a href="{{ route('my-bookings.change-requests.index') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'change-requests'])>Permintaan Layanan</a>
                                <a href="{{ route('passengers.index') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'passengers'])>Penumpang</a>
                                <a href="{{ route('profile.edit') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'profile'])>Profil</a>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="portal-nav-link">{{ $currentUser->roleLabel() }}</a>
                            @endif
                        @endauth
                        <a href="{{ route('about') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'about'])>About</a>
                        <a href="{{ route('contact') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'contact'])>Contact</a>
                    </nav>

                    <div class="hidden items-center gap-2 md:flex">
                        <span class="hidden rounded-full border border-slate-200 bg-white/70 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-600 2xl:inline-flex">
                            Jadwal . Booking . Perjalanan
                        </span>
                        @guest
                            <a href="{{ route('login') }}" class="landing-nav-outline">Masuk</a>
                            <a href="{{ route('register') }}" class="landing-nav-solid">Daftar</a>
                        @else
                            @if ($currentUser->isCustomer())
                                <a href="{{ route('notifications.index') }}" class="portal-action-btn">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V10a6 6 0 1 0-12 0v4.2a2 2 0 0 1-.6 1.4L4 17h5" />
                                        <path d="M9 17a3 3 0 0 0 6 0" />
                                    </svg>
                                    <span>Notifikasi</span>
                                    @if ($unreadPortalNotifications > 0)
                                        <span class="rounded-full bg-amber-300 px-2 py-0.5 text-[11px] font-bold text-slate-900">{{ $unreadPortalNotifications }}</span>
                                    @endif
                                </a>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="portal-action-btn">
                                    <span>{{ $currentUser->roleLabel() }}</span>
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="portal-action-btn portal-action-btn-ghost">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                        <path d="M16 17l5-5-5-5" />
                                        <path d="M21 12H9" />
                                    </svg>
                                    <span>Logout</span>
                                </button>
                            </form>
                        @endguest
                    </div>

                    <button
                        type="button"
                        class="portal-mobile-toggle xl:hidden"
                        @click="mobileOpen = !mobileOpen"
                        :aria-expanded="mobileOpen.toString()"
                        aria-label="Toggle navigation menu"
                    >
                        <svg x-show="!mobileOpen" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                        <svg x-show="mobileOpen" x-cloak viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="portal-mobile-panel xl:hidden" x-show="mobileOpen" x-cloak x-transition.opacity.scale.origin.top>
                <nav class="grid gap-1">
                    <a href="{{ route('home') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'home'])>Home</a>
                    <a href="{{ route('flights.index') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'flights'])>Flights</a>
                    @auth
                        @if ($currentUser->isCustomer())
                            <a href="{{ route('my-bookings.index') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'bookings'])>Booking Saya</a>
                            <a href="{{ route('my-bookings.change-requests.index') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'change-requests'])>Permintaan Layanan</a>
                            <a href="{{ route('passengers.index') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'passengers'])>Penumpang</a>
                            <a href="{{ route('profile.edit') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'profile'])>Profil</a>
                            <a href="{{ route('notifications.index') }}" class="portal-mobile-link">Notifikasi @if ($unreadPortalNotifications > 0)<span class="ml-1 rounded-full bg-amber-300 px-2 py-0.5 text-[11px] font-bold text-slate-900">{{ $unreadPortalNotifications }}</span>@endif</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="portal-mobile-link">{{ $currentUser->roleLabel() }}</a>
                        @endif
                    @endauth
                    <a href="{{ route('about') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'about'])>About</a>
                    <a href="{{ route('contact') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'contact'])>Contact</a>
                </nav>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-200 pt-3">
                    @guest
                        <a href="{{ route('login') }}" class="landing-nav-outline">Masuk</a>
                        <a href="{{ route('register') }}" class="landing-nav-solid">Daftar</a>
                    @else
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="portal-action-btn portal-action-btn-ghost">Logout</button>
                        </form>
                    @endguest
                </div>
            </div>
        </header>

        <main class="portal-container pb-16 pt-2">
            @if (session('status'))
                @php
                    $statusType = session('status_type', 'success');
                    $statusStyles = match ($statusType) {
                        'error' => 'border-red-300 bg-red-50 text-red-700',
                        'warning' => 'border-amber-300 bg-amber-50 text-amber-700',
                        'info' => 'border-sky-300 bg-sky-50 text-sky-700',
                        default => 'border-emerald-300 bg-emerald-50 text-emerald-700',
                    };
                @endphp
                <div class="mb-6 rounded-xl border px-4 py-3 text-sm {{ $statusStyles }}">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold">Ada kesalahan validasi:</p>
                    <ul class="mt-2 list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($customerWorkspace)
                <div class="customer-workspace">
                    <aside class="customer-rail portal-print-hide">
                        <section class="customer-hero customer-hero-compact">
                            <div class="customer-hero-copy">
                                <p class="customer-hero-kicker">{{ $workspaceEyebrow }}</p>
                                <h1 class="customer-hero-title">{{ $workspaceTitle }}</h1>
                                <p class="customer-hero-text">{{ $workspaceCopy }}</p>
                            </div>
                            <div class="customer-hero-aside">
                                <div class="customer-hero-chip">
                                    <span class="customer-hero-dot"></span>
                                    {{ $currentUser->name }}
                                </div>
                                <div class="customer-hero-meta">
                                    <span>{{ $currentUser->email }}</span>
                                    <span>{{ $unreadPortalNotifications }} notifikasi belum dibaca</span>
                                </div>
                            </div>
                        </section>

                        <nav class="customer-menu">
                            @foreach ($customerMenu as $item)
                                <a
                                    href="{{ route($item['route']) }}"
                                    @class([
                                        'customer-menu-link',
                                        'customer-menu-link-active' => $routeName && str_starts_with($routeName, $item['match']),
                                    ])
                                >
                                    <span class="customer-menu-label">{{ $item['label'] }}</span>
                                    <span class="customer-menu-hint">{{ $item['hint'] }}</span>
                                </a>
                            @endforeach
                        </nav>

                        <div class="customer-rail-note">
                            <p class="customer-rail-note-kicker">Akses cepat</p>
                            <div class="customer-rail-note-list">
                                <a href="{{ route('flights.index') }}" class="customer-rail-note-link">Cari penerbangan baru</a>
                                <a href="{{ route('my-bookings.change-requests.index') }}" class="customer-rail-note-link">Buka permintaan layanan</a>
                            </div>
                        </div>
                    </aside>

                    <section class="customer-stage">
                        @yield('content')
                    </section>
                </div>
            @else
                @yield('content')
            @endif
        </main>

        <footer class="relative z-10 mt-14 overflow-hidden border-t border-slate-200 bg-[linear-gradient(180deg,rgba(255,255,255,1),rgba(248,250,252,.96))] portal-print-hide">
            <div class="portal-container grid gap-6 py-8 md:grid-cols-[1.15fr_0.85fr] md:items-end">
                <div>
                    <div class="inline-flex items-center gap-3 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700">
                        <span class="h-2.5 w-2.5 rounded-full bg-orange-400"></span>
                        Layanan pencarian dan booking penerbangan
                    </div>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                        Cakrawala menghadirkan alur pencarian, booking, pembayaran, dan tiket dalam tampilan yang ringkas,
                        bersih, dan mudah dipahami di berbagai ukuran layar.
                    </p>
                </div>
                <div class="flex flex-col gap-3 text-sm text-slate-600 md:items-end">
                    <p>&copy; {{ now()->year }} Cakrawala Airline. All rights reserved.</p>
                    <div class="flex flex-wrap items-center gap-4">
                        <a href="{{ route('about') }}" class="auth-link-light">About</a>
                        <a href="{{ route('contact') }}" class="auth-link-light">Contact</a>
                        <span class="auth-link-light opacity-70">Privacy Policy</span>
                        <span class="auth-link-light opacity-70">Terms</span>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
