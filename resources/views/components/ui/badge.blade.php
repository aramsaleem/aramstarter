@props(['color' => 'zinc', 'dot' => false])

@php
    [$classes, $dotClass] = match ($color) {
        'primary' => ['bg-primary-500/10 text-primary-700 ring-primary-500/20 dark:bg-primary-400/10 dark:text-primary-300 dark:ring-primary-400/25', 'bg-primary-500'],
        'green' => ['bg-emerald-500/10 text-emerald-700 ring-emerald-500/20 dark:text-emerald-300 dark:ring-emerald-400/20', 'bg-emerald-500'],
        'red' => ['bg-red-500/10 text-red-700 ring-red-500/20 dark:text-red-300 dark:ring-red-400/20', 'bg-red-500'],
        'amber' => ['bg-amber-500/10 text-amber-800 ring-amber-500/25 dark:text-amber-300 dark:ring-amber-400/20', 'bg-amber-500'],
        'sky' => ['bg-sky-500/10 text-sky-700 ring-sky-500/20 dark:text-sky-300 dark:ring-sky-400/20', 'bg-sky-500'],
        default => ['bg-zinc-900/[0.04] text-zinc-700 ring-zinc-900/10 dark:bg-white/[0.05] dark:text-zinc-300 dark:ring-white/10', 'bg-zinc-400'],
    };
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium whitespace-nowrap ring-1 ring-inset',
    $classes,
]) }}>
    @if ($dot)
        <span class="size-1.5 rounded-full {{ $dotClass }}"></span>
    @endif
    {{ $slot }}
</span>
