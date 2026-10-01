@php
    $gradientId = 'logo-gradient-'.\Illuminate\Support\Str::random(6);
@endphp

<svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" {{ $attributes }}>
    <defs>
        <linearGradient id="{{ $gradientId }}" x1="4" y1="2" x2="38" y2="40" gradientUnits="userSpaceOnUse">
            <stop stop-color="#8b5cf6" />
            <stop offset="0.55" stop-color="#6d28d9" />
            <stop offset="1" stop-color="#06b6d4" />
        </linearGradient>
    </defs>
    <rect width="40" height="40" rx="11" fill="url(#{{ $gradientId }})" />
    <rect x="0.5" y="0.5" width="39" height="39" rx="10.5" stroke="white" stroke-opacity="0.18" />
    <path d="M13.5 28.5h13.25a6.25 6.25 0 0 0 .9-12.44A8 8 0 0 0 12.4 18.3a5.1 5.1 0 0 0 1.1 10.2Z" fill="white" />
    <path d="M17 23.25 19.25 25.5 24 20.75" stroke="#6d28d9" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" />
</svg>
