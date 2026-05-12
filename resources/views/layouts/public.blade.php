<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Booking Ziarah') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
        <main class="mx-auto w-full max-w-5xl px-4 py-6">
            {{ $slot }}
        </main>

        <x-toast />
        <x-confirm-modal />

        @livewireScripts
    </body>
</html>
