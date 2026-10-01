{{--
    <x-ui.button>Save</x-ui.button>
    <x-ui.button variant="secondary" :href="route('home')" icon="arrow-left">Back</x-ui.button>
    <x-ui.button type="submit" loading="save">Save</x-ui.button>  // spinner while the "save" action runs
--}}
@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'button',
    'icon' => null,
    'loading' => null,
])

@php
    $classes = [
        'relative inline-flex items-center justify-center gap-2 font-medium whitespace-nowrap transition-all duration-150 select-none focus-visible:outline-2 focus-visible:outline-offset-2 active:translate-y-px disabled:pointer-events-none disabled:opacity-55',
        match ($size) {
            'sm' => 'h-8 rounded-lg px-3 text-xs',
            'lg' => 'h-12 rounded-xl px-5 text-sm',
            default => 'h-10 rounded-xl px-4 text-sm',
        },
        match ($variant) {
            'secondary' => 'bg-white text-zinc-800 shadow-xs ring-1 ring-zinc-200 hover:bg-zinc-50 hover:ring-zinc-300 focus-visible:outline-zinc-400 dark:bg-white/[0.04] dark:text-zinc-100 dark:ring-white/10 dark:hover:bg-white/[0.08] dark:hover:ring-white/15',
            'danger' => 'bg-linear-to-b from-rose-500 to-red-600 text-white shadow-[inset_0_1px_0_rgb(255_255_255/0.2),0_8px_22px_-10px_rgb(220_38_38/0.8)] ring-1 ring-red-700/40 hover:from-rose-400 hover:to-red-500 focus-visible:outline-red-600',
            'ghost' => 'text-zinc-600 hover:bg-zinc-900/5 hover:text-zinc-900 focus-visible:outline-zinc-400 dark:text-zinc-400 dark:hover:bg-white/[0.06] dark:hover:text-white',
            'link' => 'h-auto! px-0! text-primary-600 hover:text-primary-500 hover:underline active:translate-y-0 dark:text-primary-400',
            default => 'bg-linear-to-b from-primary-500 to-primary-600 text-white shadow-[inset_0_1px_0_rgb(255_255_255/0.25),0_10px_24px_-12px_var(--color-primary-600)] ring-1 ring-primary-700/40 hover:from-primary-400 hover:to-primary-500 focus-visible:outline-primary-500',
        },
    ];

    $iconClasses = ($size === 'sm' ? 'size-4' : 'size-4.5').' shrink-0'.(str_contains((string) $icon, 'arrow') ? ' rtl:-scale-x-100' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-dynamic-component :component="'heroicon-o-'.$icon" :class="$iconClasses" />
        @endif
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        @if ($loading) wire:loading.attr="disabled" wire:target="{{ $loading }}" @endif
        {{ $attributes->class($classes) }}
    >
        @if ($loading)
            <x-ui.spinner :class="$iconClasses" wire:loading wire:target="{{ $loading }}" />
        @endif

        @if ($icon && $loading)
            <x-dynamic-component :component="'heroicon-o-'.$icon" :class="$iconClasses" wire:loading.remove wire:target="{{ $loading }}" />
        @elseif ($icon)
            <x-dynamic-component :component="'heroicon-o-'.$icon" :class="$iconClasses" />
        @endif

        {{ $slot }}
    </button>
@endif
