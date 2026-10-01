{{-- "collapsible" links (the main sidebar) shrink to an icon when the sidebar is collapsed. --}}
@props(['href', 'active' => false, 'icon' => null, 'collapsible' => false])

@php
    $label = trim(strip_tags((string) $slot));
@endphp

<a
    href="{{ $href }}"
    wire:navigate
    @if ($active) aria-current="page" @endif
    @if ($collapsible) title="{{ $label }}" @endif
    {{ $attributes->class([
        'group relative flex h-10 items-center gap-3 rounded-xl px-3 text-sm font-medium transition-all duration-150',
        'sidebar-collapsed:lg:justify-center sidebar-collapsed:lg:px-0' => $collapsible,
        'bg-white text-zinc-900 shadow-[0_1px_2px_rgb(0_0_0/0.05)] ring-1 ring-zinc-200/80 dark:bg-white/[0.07] dark:text-white dark:shadow-none dark:ring-white/10' => $active,
        'text-zinc-600 hover:bg-zinc-900/[0.04] hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/[0.04] dark:hover:text-white' => ! $active,
    ]) }}
>
    @if ($active)
        <span @class(['absolute inset-y-2.5 start-0 w-[3px] rounded-e-full bg-linear-to-b from-primary-400 to-accent-400', 'sidebar-collapsed:lg:hidden' => $collapsible])></span>
    @endif

    @if ($icon)
        <x-dynamic-component
            :component="'heroicon-o-'.$icon"
            @class([
                'size-5 shrink-0 transition-colors',
                'rtl:-scale-x-100' => str_contains($icon, 'arrow'),
                'text-primary-600 dark:text-primary-400' => $active,
                'text-zinc-400 group-hover:text-zinc-600 dark:text-zinc-500 dark:group-hover:text-zinc-300' => ! $active,
            ])
        />
    @endif

    <span @class(['truncate', 'sidebar-collapsed:lg:hidden' => $collapsible])>{{ $slot }}</span>
</a>
