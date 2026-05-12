@props([
    'steps' => [],
    'currentStep' => 1, // 1-based
])

@php
    $current = max(1, (int) $currentStep);
@endphp

<div class="w-full">
    <ol class="flex flex-wrap items-center gap-2">
        @foreach ($steps as $index => $label)
            @php
                $stepNumber = $index + 1;
                $isDone = $stepNumber < $current;
                $isActive = $stepNumber === $current;
            @endphp

            <li class="flex items-center gap-2">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold"
                    @class([
                        'bg-blue-700 text-white' => $isActive,
                        'bg-green-600 text-white' => $isDone,
                        'bg-gray-200 text-gray-800' => !$isActive && !$isDone,
                    ])
                    aria-current="{{ $isActive ? 'step' : 'false' }}"
                >
                    @if ($isDone)
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-7.5 7.5a1 1 0 01-1.42 0l-3.5-3.5a1 1 0 011.42-1.42l2.79 2.79 6.79-6.79a1 1 0 011.42 0z" clip-rule="evenodd" />
                        </svg>
                    @else
                        {{ $stepNumber }}
                    @endif
                </div>

                <div class="text-sm font-medium" @class([
                    'text-blue-700' => $isActive,
                    'text-gray-900' => !$isActive,
                ])>
                    {{ $label }}
                </div>

                @if ($stepNumber !== count($steps))
                    <div class="mx-1 h-px w-6 bg-gray-300"></div>
                @endif
            </li>
        @endforeach
    </ol>
</div>

