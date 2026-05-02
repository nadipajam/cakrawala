<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="customer-shell font-sans text-slate-700 antialiased">
        <div class="guest-shell">
            <div class="guest-card">
                <a href="/" class="mx-auto mb-6 flex w-fit items-center gap-3">
                    <span class="portal-brand-mark">
                        <x-application-logo class="h-6 w-6 shrink-0 rounded object-contain" />
                    </span>
                    <span>
                        <strong class="portal-brand-title">CAKRAWALA</strong>
                        <small class="portal-brand-subtitle">Akses akun</small>
                    </span>
                </a>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
