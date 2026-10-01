@props(['label', 'value', 'icon' => null, 'hint' => null])

<x-ui.card {{ $attributes->class('group overflow-hidden') }}>
    <div aria-hidden="true" class="pointer-events-none absolute -end-10 -top-10 size-32 rounded-full bg-primary-500/10 opacity-0 blur-2xl transition-opacity duration-500 group-hover:opacity-100"></div>

    <div class="relative flex items-start justify-between gap-4">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</p>

        @if ($icon)
            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-primary-500/15 to-accent-400/10 text-primary-600 ring-1 ring-primary-500/15 dark:text-primary-300">
                <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-4.5" />
            </span>
        @endif
    </div>

    <p class="relative mt-3 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $value }}</p>

    @if ($hint)
        <p class="relative mt-1.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $hint }}</p>
    @endif
</x-ui.card>
