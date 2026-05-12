<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    {{-- Brand Header --}}
    <div class="text-center mb-8">
        <div class="w-13 h-13 rounded-full bg-gray-800 flex items-center justify-center mx-auto mb-3"
             style="width:52px;height:52px;">
            <svg width="26" height="26" viewBox="0 0 26 26" fill="none">
                <path d="M13 3C13 3 7 7 7 13C7 16.3 9.7 19 13 19C16.3 19 19 16.3 19 13C19 7 13 3 13 3Z" fill="#9CA3AF"/>
                <rect x="12" y="18" width="2" height="5" rx="1" fill="#6B7280"/>
                <rect x="8" y="22" width="10" height="1.5" rx="0.75" fill="#6B7280"/>
            </svg>
        </div>
        <h1 class="text-base font-medium text-gray-800 tracking-wide">Lestari Memorial Park</h1>
        <p class="text-xs text-gray-400 tracking-widest uppercase mt-0.5">Dashboard Administrasi</p>
    </div>

    <hr class="border-gray-200 mb-7">

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-5">
            <x-input-label for="email" :value="__('Email')" class="text-xs font-medium text-gray-500 uppercase tracking-wide" />
            <x-text-input id="email"
                class="block mt-1.5 w-full bg-gray-50 border-gray-200 text-sm focus:border-gray-500 focus:ring-gray-200"
                type="email" name="email" :value="old('email')"
                required autofocus autocomplete="username"
                placeholder="contoh@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        {{-- Password --}}
        <div class="mb-5">
            <x-input-label for="password" :value="__('Password')" class="text-xs font-medium text-gray-500 uppercase tracking-wide" />
            <x-text-input id="password"
                class="block mt-1.5 w-full bg-gray-50 border-gray-200 text-sm focus:border-gray-500 focus:ring-gray-200"
                type="password" name="password"
                required autocomplete="current-password"
                placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        {{-- Remember Me --}}
        <div class="mb-6">
            <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer">
                <input id="remember_me" type="checkbox"
                    class="rounded border-gray-300 text-gray-700 shadow-sm focus:ring-gray-300"
                    name="remember">
                <span class="text-sm text-gray-500">Ingat saya</span>
            </label>
        </div>

        {{-- Submit --}}
        <x-primary-button class="w-full justify-center bg-gray-800 hover:bg-gray-700 border-gray-800 text-sm tracking-wide py-2.5">
            {{ __('Masuk') }}
        </x-primary-button>

        {{-- Forgot Password --}}
        @if (Route::has('password.request'))
            <div class="text-center mt-4">
                <a class="text-xs text-gray-400 hover:text-gray-600 underline underline-offset-2"
                   href="{{ route('password.request') }}">
                    {{ __('Lupa password?') }}
                </a>
            </div>
        @endif
    </form>
</x-guest-layout>