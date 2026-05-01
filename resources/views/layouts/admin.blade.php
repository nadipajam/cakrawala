<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Admin Panel | Cakrawala')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="portal-shell admin-command-shell font-sans antialiased text-slate-700">
        @php
            $backofficeUser = auth()->user();
            $menuGroups = [
                'Workspace' => [
                    ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'active' => 'admin.dashboard'],
                    ['label' => 'Support Inbox', 'route' => 'admin.contact-messages.index', 'active' => 'admin.contact-messages'],
                ],
                'Operations' => [
                    ['label' => 'Passengers', 'route' => 'admin.passengers.index', 'active' => 'admin.passengers'],
                    ['label' => 'Bookings', 'route' => 'admin.bookings.index', 'active' => 'admin.bookings'],
                    ['label' => 'Add-Ons', 'route' => 'admin.addons.index', 'active' => 'admin.addons'],
                    ['label' => 'Change Requests', 'route' => 'admin.change-requests.index', 'active' => 'admin.change-requests'],
                    ['label' => 'Payments', 'route' => 'admin.payments.index', 'active' => 'admin.payments'],
                    ['label' => 'Tickets', 'route' => 'admin.tickets.index', 'active' => 'admin.tickets'],
                    ['label' => 'Flights', 'route' => 'admin.flights.index', 'active' => 'admin.flights'],
                ],
            ];

            if ($backofficeUser->canViewUsers()) {
                $menuGroups['Management'] = [
                    ['label' => 'Users', 'route' => 'admin.users.index', 'active' => 'admin.users'],
                ];
            }

            if ($backofficeUser->canViewReports()) {
                $menuGroups['Monitoring'] = [
                    ['label' => 'Reports', 'route' => 'admin.reports.index', 'active' => 'admin.reports'],
                ];
            }

            if ($backofficeUser->canManageMasterData()) {
                $menuGroups['Master Data'] = [
                    ['label' => 'Airports', 'route' => 'admin.airports.index', 'active' => 'admin.airports'],
                    ['label' => 'Airlines', 'route' => 'admin.airlines.index', 'active' => 'admin.airlines'],
                    ['label' => 'Airplanes', 'route' => 'admin.airplanes.index', 'active' => 'admin.airplanes'],
                    ['label' => 'Seats', 'route' => 'admin.seats.index', 'active' => 'admin.seats'],
                ];
            }

            $currentRoute = optional(request()->route())->getName();
        @endphp

        <div class="portal-container relative z-10 py-4 lg:py-6" x-data="{ sidebarOpen: false }">
            <div
                class="fixed inset-0 z-40 bg-slate-900/45 lg:hidden"
                x-show="sidebarOpen"
                x-cloak
                @click="sidebarOpen = false"
            ></div>

            <div class="admin-shell">
                <aside class="admin-sidebar" :class="{ 'admin-sidebar-open': sidebarOpen }">
                    <div class="admin-sidebar-brand">
                        <a href="{{ route('admin.dashboard') }}" class="portal-brand">
                            <span class="portal-brand-mark" aria-hidden="true">
                                <svg viewBox="0 0 72 72" class="h-5 w-5 shrink-0" fill="none">
                                    <path d="M6 42c11-8 22-12 37-13-5 4-8 8-13 14 13-4 23-11 33-24-6 2-11 3-19 5 4-6 7-10 13-16-12 3-21 8-30 16-8-1-13-1-21-2 5 7 8 12 10 20z" fill="currentColor"/>
                                    <path d="M26 46c16-8 27-19 38-36-3 12-6 21-12 31 6-1 10-2 16-4-7 9-14 14-24 18-6-3-11-5-18-9z" fill="#fff8f2"/>
                                </svg>
                            </span>
                            <span>
                                <strong class="portal-brand-title">CAKRAWALA</strong>
                                <small class="portal-brand-subtitle">{{ $backofficeUser->roleLabel() }} panel</small>
                            </span>
                        </a>

                        <div class="admin-sidebar-user">
                            <p class="admin-sidebar-user-label">Signed in</p>
                            <p class="admin-sidebar-user-name">{{ $backofficeUser->name }}</p>
                            <p class="admin-sidebar-user-role">{{ $backofficeUser->roleLabel() }} command access</p>
                        </div>
                    </div>

                    <div class="admin-sidebar-intro">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300/80">Workspace</p>
                        <p class="mt-2 text-sm leading-6 text-slate-300">Panel ini dipakai untuk operasi harian, monitoring, dan manajemen data inti.</p>
                    </div>

                    <nav class="mt-6 space-y-5">
                        @foreach ($menuGroups as $group => $menus)
                            <div>
                                <p class="admin-sidebar-group">{{ $group }}</p>
                                <div class="mt-2 grid gap-1">
                                    @foreach ($menus as $menu)
                                        <a
                                            href="{{ route($menu['route']) }}"
                                            @click="sidebarOpen = false"
                                            @class([
                                                'admin-sidebar-link',
                                                'admin-sidebar-link-active' => $currentRoute && str_starts_with($currentRoute, $menu['active']),
                                            ])
                                        >
                                            {{ $menu['label'] }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </nav>

                    <div class="mt-4 space-y-2 border-t border-slate-200 pt-4">
                        <a href="{{ route('admin.profile.index') }}" @click="sidebarOpen = false" @class(['admin-sidebar-link', 'admin-sidebar-link-active' => str_starts_with((string) $currentRoute, 'admin.profile')])>
                            Profile
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="admin-sidebar-link w-full text-left">Logout</button>
                        </form>
                    </div>
                </aside>

                <div class="admin-content">
                    <header class="admin-topbar">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-cyan-700">{{ $backofficeUser->roleLabel() }} Control</p>
                            <h1 class="font-heading text-2xl font-bold text-slate-800">@yield('page-title', 'Dashboard')</h1>
                        </div>
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="max-w-[230px] truncate rounded-full border border-slate-200 bg-slate-950 px-3 py-1 text-sm text-slate-100 sm:max-w-none">
                                {{ $backofficeUser->name }} | {{ $backofficeUser->roleLabel() }}
                            </span>
                            <button class="admin-mobile-toggle lg:hidden" @click="sidebarOpen = !sidebarOpen" type="button">
                                Menu
                            </button>
                        </div>
                    </header>

                    @if (session('status'))
                        <div class="mt-4 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-4 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-700">
                            <p class="font-semibold">Ada kesalahan validasi:</p>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <main class="mt-6">
                        @yield('content')
                    </main>
                </div>
            </div>
        </div>
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
