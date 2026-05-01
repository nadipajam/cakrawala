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

        $workspaceTitle = 'Customer Workspace';
        $workspaceEyebrow = 'Trip desk';
        $workspaceCopy = 'Kelola booking, penumpang, pembayaran, check-in, dan tiket dari satu area kerja yang lebih fokus.';

        if (request()->routeIs('dashboard')) {
            $workspaceTitle = 'Travel Dashboard';
            $workspaceEyebrow = 'Overview';
            $workspaceCopy = 'Lihat ringkasan booking aktif, notifikasi, dan langkah berikutnya untuk perjalanan Anda.';
        } elseif (request()->routeIs('my-bookings.index')) {
            $workspaceTitle = 'Booking Board';
            $workspaceEyebrow = 'Bookings';
            $workspaceCopy = 'Seluruh booking Anda disusun seperti board kerja agar status, nilai, dan tindakan berikutnya mudah dipindai.';
        } elseif (request()->routeIs('my-bookings.show')) {
            $workspaceTitle = 'Booking Control';
            $workspaceEyebrow = 'Booking detail';
            $workspaceCopy = 'Buka satu booking, cek passenger, add-on, pembayaran, check-in, dan tiket dari satu halaman.';
        } elseif (request()->routeIs('booking.*')) {
            $workspaceTitle = 'Booking Composer';
            $workspaceEyebrow = 'Create booking';
            $workspaceCopy = 'Pilih passenger, kabin, dan seat dengan alur yang lebih terstruktur sebelum konfirmasi.';
        } elseif (request()->routeIs('payments.*')) {
            $workspaceTitle = 'Payment Desk';
            $workspaceEyebrow = 'Payments';
            $workspaceCopy = 'Pantau submission pembayaran, QRIS, dan status verifikasi untuk setiap booking.';
        } elseif (request()->routeIs('passengers.*')) {
            $workspaceTitle = 'Passenger Vault';
            $workspaceEyebrow = 'Passengers';
            $workspaceCopy = 'Simpan dan perbarui profil penumpang agar proses booking berikutnya lebih cepat.';
        } elseif (request()->routeIs('notifications.*')) {
            $workspaceTitle = 'Notification Inbox';
            $workspaceEyebrow = 'Updates';
            $workspaceCopy = 'Semua update booking, pembayaran, dan layanan purna jual terkumpul di sini.';
        } elseif (request()->routeIs('profile.*')) {
            $workspaceTitle = 'Account Studio';
            $workspaceEyebrow = 'Profile';
            $workspaceCopy = 'Atur identitas akun, kontak, dan keamanan login dari tampilan yang lebih fokus.';
        } elseif (request()->routeIs('my-bookings.change-requests.*')) {
            $workspaceTitle = 'Service Desk';
            $workspaceEyebrow = 'Change requests';
            $workspaceCopy = 'Ajukan refund, reschedule, dan perubahan data perjalanan tanpa keluar dari workspace customer.';
        }

        $customerMenu = [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'match' => 'dashboard', 'hint' => 'Overview perjalanan'],
            ['label' => 'My Bookings', 'route' => 'my-bookings.index', 'match' => 'my-bookings', 'hint' => 'Booking dan status'],
            ['label' => 'Passengers', 'route' => 'passengers.index', 'match' => 'passengers', 'hint' => 'Data penumpang'],
            ['label' => 'Notifications', 'route' => 'notifications.index', 'match' => 'notifications', 'hint' => 'Update sistem'],
            ['label' => 'Profile', 'route' => 'profile.edit', 'match' => 'profile', 'hint' => 'Akun dan keamanan'],
        ];
    @endphp
    <body class="{{ $customerWorkspace ? 'customer-shell' : 'portal-shell' }} font-sans antialiased text-slate-700">
        @php($active = trim($__env->yieldContent('active')))
        @php($unreadPortalNotifications = $currentUser && $currentUser->isCustomer() ? $currentUser->unreadNotifications()->count() : 0)
        @php($portalTone = $customerWorkspace ? 'Trip workspace' : 'Editorial portal')

        <header class="portal-container relative z-40 py-4 portal-print-hide" x-data="{ mobileOpen: false }">
            <div class="portal-topbar portal-topbar-v2">
                <div class="portal-topbar-main">
                    <a href="{{ route('home') }}" class="portal-brand">
                        <span class="portal-brand-mark" aria-hidden="true">
                            <svg viewBox="0 0 72 72" class="h-5 w-5 shrink-0" fill="none">
                                <path d="M6 42c11-8 22-12 37-13-5 4-8 8-13 14 13-4 23-11 33-24-6 2-11 3-19 5 4-6 7-10 13-16-12 3-21 8-30 16-8-1-13-1-21-2 5 7 8 12 10 20z" fill="currentColor"/>
                                <path d="M26 46c16-8 27-19 38-36-3 12-6 21-12 31 6-1 10-2 16-4-7 9-14 14-24 18-6-3-11-5-18-9z" fill="#fff8f2"/>
                            </svg>
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
                            {{ $customerWorkspace ? $workspaceCopy : 'Cari jadwal, buka booking, dan pindah ke dashboard role Anda dari shell yang sama.' }}
                        </p>
                    </div>
                </div>

                <div class="portal-topbar-side">
                    <nav class="portal-nav hidden xl:flex">
                        <a href="{{ route('home') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'home'])>Home</a>
                        <a href="{{ route('flights.index') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'flights'])>Flights</a>
                        @auth
                            @if ($currentUser->isCustomer())
                                <a href="{{ route('my-bookings.index') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'bookings'])>My Bookings</a>
                                <a href="{{ route('my-bookings.change-requests.index') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'change-requests'])>Service Requests</a>
                                <a href="{{ route('passengers.index') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'passengers'])>Passengers</a>
                                <a href="{{ route('profile.edit') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'profile'])>Profile</a>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="portal-nav-link">{{ $currentUser->roleLabel() }}</a>
                            @endif
                        @endauth
                        <a href="{{ route('about') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'about'])>About</a>
                        <a href="{{ route('contact') }}" @class(['portal-nav-link', 'portal-nav-link-active' => $active === 'contact'])>Contact</a>
                    </nav>

                    <div class="hidden items-center gap-2 md:flex">
                        <span class="hidden rounded-full border border-slate-200 bg-white/70 px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.22em] text-slate-600 2xl:inline-flex">
                            Search . Manage . Fly
                        </span>
                        @guest
                            <a href="{{ route('login') }}" class="landing-nav-outline">Login</a>
                            <a href="{{ route('register') }}" class="landing-nav-solid">Register</a>
                        @else
                            @if ($currentUser->isCustomer())
                                <a href="{{ route('notifications.index') }}" class="portal-action-btn">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V10a6 6 0 1 0-12 0v4.2a2 2 0 0 1-.6 1.4L4 17h5" />
                                        <path d="M9 17a3 3 0 0 0 6 0" />
                                    </svg>
                                    <span>Notifications</span>
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
                            <a href="{{ route('my-bookings.index') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'bookings'])>My Bookings</a>
                            <a href="{{ route('my-bookings.change-requests.index') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'change-requests'])>Service Requests</a>
                            <a href="{{ route('passengers.index') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'passengers'])>Passengers</a>
                            <a href="{{ route('profile.edit') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'profile'])>Profile</a>
                            <a href="{{ route('notifications.index') }}" class="portal-mobile-link">Notifications @if ($unreadPortalNotifications > 0)<span class="ml-1 rounded-full bg-amber-300 px-2 py-0.5 text-[11px] font-bold text-slate-900">{{ $unreadPortalNotifications }}</span>@endif</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="portal-mobile-link">{{ $currentUser->roleLabel() }}</a>
                        @endif
                    @endauth
                    <a href="{{ route('about') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'about'])>About</a>
                    <a href="{{ route('contact') }}" @class(['portal-mobile-link', 'portal-mobile-link-active' => $active === 'contact'])>Contact</a>
                </nav>

                <div class="mt-3 flex flex-wrap items-center gap-2 border-t border-slate-200 pt-3">
                    @guest
                        <a href="{{ route('login') }}" class="landing-nav-outline">Login</a>
                        <a href="{{ route('register') }}" class="landing-nav-solid">Register</a>
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
                <div class="mb-6 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
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
                                    <span>{{ $unreadPortalNotifications }} unread update{{ $unreadPortalNotifications === 1 ? '' : 's' }}</span>
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
                            <p class="customer-rail-note-kicker">Quick access</p>
                            <div class="customer-rail-note-list">
                                <a href="{{ route('flights.index') }}" class="customer-rail-note-link">Cari penerbangan baru</a>
                                <a href="{{ route('my-bookings.change-requests.index') }}" class="customer-rail-note-link">Buka service request</a>
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
                        <span class="h-2.5 w-2.5 rounded-full bg-blue-400"></span>
                        Layanan pencarian dan booking penerbangan
                    </div>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-600">
                        Cakrawala menghadirkan alur pencarian, booking, pembayaran, dan ticketing dalam tampilan yang ringkas,
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
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.admin-table-wrap, .portal-table-wrap').forEach(function (wrap) {
                    wrap.style.display = 'block';
                    wrap.style.width = '100%';
                    wrap.style.maxWidth = '100%';
                    wrap.style.overflowX = 'auto';
                    wrap.style.overflowY = 'hidden';
                    wrap.style.webkitOverflowScrolling = 'touch';
                    wrap.style.touchAction = 'pan-x';
                });

                document.querySelectorAll('.admin-table, .portal-table').forEach(function (table) {
                    table.style.width = 'max-content';
                    table.style.minWidth = '860px';
                    table.style.tableLayout = 'auto';
                });
            });
        </script>
    </body>
</html>
