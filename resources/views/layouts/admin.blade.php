<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Booking Ziarah') }} - Admin</title>
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
        <div class="min-h-screen md:flex" x-data="{ sidebarOpen: false }">
            <div class="fixed inset-0 z-40 bg-black/40 md:hidden" x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" x-cloak></div>

            <aside
                class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full border-r bg-white transition-transform md:static md:z-auto md:w-64 md:translate-x-0"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                x-cloak
            >
                <div class="flex items-center justify-between px-4 py-4">
                    <div class="font-semibold">Admin</div>
                    <button type="button" class="rounded p-2 hover:bg-gray-100 md:hidden" @click="sidebarOpen = false" aria-label="Tutup menu">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <nav class="space-y-1 px-2 pb-4">
                    <a href="{{ route('admin.dashboard') }}" class="block rounded px-3 py-2 hover:bg-gray-100">Dashboard</a>
                    <a href="{{ route('admin.locations.index') }}" class="block rounded px-3 py-2 hover:bg-gray-100">Lokasi & Lot</a>
                    <a href="{{ route('admin.time-slots.index') }}" class="block rounded px-3 py-2 hover:bg-gray-100">Time Slots</a>
                    <a href="{{ route('admin.users.index') }}" class="block rounded px-3 py-2 hover:bg-gray-100">Users</a>
                    <a href="{{ route('admin.settings.index') }}" class="block rounded px-3 py-2 hover:bg-gray-100">Settings</a>

                    <form method="POST" action="{{ route('logout') }}" class="pt-2">
                        @csrf
                        <button type="submit" class="block w-full rounded px-3 py-2 text-left text-sm font-semibold text-red-700 hover:bg-red-50">
                            Logout
                        </button>
                    </form>
                </nav>
            </aside>

            <main class="flex-1 p-4 md:p-6">
                <div class="mb-4 flex items-center gap-3 md:hidden">
                    <button type="button" class="rounded bg-white p-2 ring-1 ring-black/5 hover:bg-gray-50" @click="sidebarOpen = true" aria-label="Buka menu">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 5h14a1 1 0 110 2H3a1 1 0 110-2zm0 6h14a1 1 0 110 2H3a1 1 0 110-2zm0 6h14a1 1 0 110 2H3a1 1 0 110-2z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div class="text-sm font-semibold text-gray-900">Menu</div>
                </div>

                {{ $slot }}
            </main>
        </div>

        <x-toast />
        <x-confirm-modal />

        @livewireScripts
    </body>
</html>
