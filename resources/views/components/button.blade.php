@props([
    'type' => 'button',
    'color' => 'primary', // primary | danger | secondary
    'loading' => false,
])

@php
    $colors = [
        'primary' => 'bg-blue-700 hover:bg-blue-800 text-white',
        'danger' => 'bg-red-600 hover:bg-red-700 text-white',
        'secondary' => 'bg-gray-200 hover:bg-gray-300 text-gray-900',
    ];

    $colorClasses = $colors[$color] ?? $colors['primary'];
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-60 '.$colorClasses]) }}
    @disabled($loading)
>
    @if ($loading)
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v3a5 5 0 00-5 5H4z"></path>
        </svg>
    @endif

    <span>{{ $slot }}</span>
</button>

