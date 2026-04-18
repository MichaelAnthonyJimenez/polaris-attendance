<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Polaris') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full bg-slate-950 text-slate-100">
        <div class="min-h-screen">
            <nav class="border-b border-white/10 bg-slate-950/60 backdrop-blur">
                <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <a href="{{ route('home') }}" class="font-semibold tracking-tight text-white">
                            {{ config('app.name', 'Polaris') }}
                        </a>

                        <div class="flex items-center gap-3">
                            @auth
                                <a href="{{ route('dashboard') }}" class="text-sm text-slate-200 hover:text-white">Dashboard</a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm text-slate-200 hover:text-white">Log in</a>
                                <a href="{{ route('register') }}" class="btn-primary">Register</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>

            <main class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-10">
                @yield('content')
            </main>
        </div>
    </body>
</html>

