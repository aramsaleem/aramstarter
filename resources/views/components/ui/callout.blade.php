@props(['variant' => 'info', 'icon' => null, 'heading' => null])

@php
    [$classes, $iconClasses, $defaultIcon] = match ($variant) {
        'success' => ['border-emerald-500/20 bg-emerald-500/[0.06] text-emerald-900 dark:text-emerald-100', 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-300', 'check-circle'],
        'warning' => ['border-amber-500/25 bg-amber-500/[0.07] text-amber-900 dark:text-amber-100', 'bg-amber-500/15 text-amber-600 dark:text-amber-300', 'exclamation-triangle'],
        'danger' => ['border-red-500/20 bg-red-500/[0.06] text-red-900 dark:text-red-100', 'bg-red-500/15 text-red-600 dark:text-red-300', 'exclamation-triangle'],
        default => ['border-primary-500/20 bg-primary-500/[0.06] text-primary-950 dark:text-primary-100', 'bg-primary-500/15 text-primary-600 dark:text-primary-300', 'information-circle'],
    };
@endphp

<div {{ $attributes->class(['flex gap-3.5 rounded-2xl border p-4 text-sm', $classes]) }} role="status">
    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $iconClasses }}">
        <x-dynamic-component :component="'heroicon-o-'.($icon ?? $defaultIcon)" class="size-4.5" />
    </span>

    <div class="min-w-0 flex-1 space-y-0.5 self-center">
        @if ($heading)
            <p class="font-semibold">{{ $heading }}</p>
        @endif

        <div class="leading-6 opacity-90 [&_a]:font-semibold [&_a]:underline [&_a]:underline-offset-2">{{ $slot }}</div>
    </div>
</div>
