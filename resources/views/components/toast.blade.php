@props([
    'type' => null, // success | error | warning | info
    'message' => null,
])

@php
    $toast = session('toast');
    $toastType = $type ?? ($toast['type'] ?? null);
    $toastMessage = $message ?? ($toast['message'] ?? null);

    $baseClasses = 'pointer-events-auto w-full max-w-sm rounded-lg px-4 py-3 shadow-lg ring-1 ring-black/5';
    $typeClasses = match ($toastType) {
        'success' => 'bg-green-600 text-white',
        'error' => 'bg-red-600 text-white',
        'warning' => 'bg-yellow-500 text-black',
        default => 'bg-blue-700 text-white',
    };
@endphp

@if ($toastMessage)
    <div
        class="pointer-events-none fixed right-4 top-4 z-50 flex w-full justify-end"
        x-data="{ open: true }"
        x-init="setTimeout(() => open = false, 4000)"
        x-show="open"
        x-transition.opacity.duration.200ms
        x-cloak
    >
        <div class="{{ $baseClasses }} {{ $typeClasses }}">
            <div class="flex items-start gap-3">
                <div class="flex-1 text-sm font-medium leading-5">
                    {{ $toastMessage }}
                </div>
                <button
                    type="button"
                    class="pointer-events-auto -mr-1 -mt-1 rounded p-1 opacity-90 hover:opacity-100"
                    aria-label="Tutup"
                    @click="open = false"
                >
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif

