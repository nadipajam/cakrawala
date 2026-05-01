<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="customer-shell font-sans antialiased text-slate-700">
        <div class="app-shell">
            @include('layouts.navigation')

            <div class="app-main-wrap">
                @isset($header)
                    <header class="app-header-panel">
                        {{ $header }}
                    </header>
                @endisset

                <main class="@isset($header) mt-6 @else mt-2 @endisset">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
