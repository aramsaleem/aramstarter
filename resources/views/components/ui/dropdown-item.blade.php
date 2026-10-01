@props(['href' => null, 'icon' => null])

@php
    $classes = 'group/item flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-start text-sm text-zinc-700 transition-colors hover:bg-zinc-900/5 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-white/[0.07] dark:hover:text-white';
    $iconClasses = 'size-4 shrink-0 text-zinc-400 transition-colors group-hover/item:text-primary-500'.(str_contains((string) $icon, 'arrow') ? ' rtl:-scale-x-100' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" role="menuitem" {{ $attributes->class($classes) }}>
        @if ($icon)
            <x-dynamic-component :component="'heroicon-o-'.$icon" :class="$iconClasses" />
        @endif
        {{ $slot }}
    </a>
@else
    <button role="menuitem" {{ $attributes->merge(['type' => 'button'])->class($classes) }}>
        @if ($icon)
            <x-dynamic-component :component="'heroicon-o-'.$icon" :class="$iconClasses" />
        @endif
        {{ $slot }}
    </button>
@endif
