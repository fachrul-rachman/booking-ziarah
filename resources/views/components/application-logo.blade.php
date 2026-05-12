@php
    $base = 'inline-flex items-center justify-center rounded-full bg-emerald-800 text-white font-black leading-none select-none';
    $class = trim(($attributes->get('class') ?? '').' '.$base);
@endphp
<span {{ $attributes->merge(['class' => $class]) }} aria-label="Logo">Z</span>
