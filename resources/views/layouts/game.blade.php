<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ $title ?? 'The Matrix: Terminal' }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=fira-code:400,500,600,700" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="min-h-screen bg-black font-mono text-green-400 antialiased selection:bg-green-500/30">
        {{-- Scanline overlay --}}
        <div class="pointer-events-none fixed inset-0 z-50" style="background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0, 255, 65, 0.03) 2px, rgba(0, 255, 65, 0.03) 4px);"></div>

        {{-- Vignette --}}
        <div class="pointer-events-none fixed inset-0 z-40" style="background: radial-gradient(ellipse at center, transparent 50%, rgba(0,0,0,0.6) 100%);"></div>

        {{ $slot }}

        @livewireScripts
    </body>
</html>
