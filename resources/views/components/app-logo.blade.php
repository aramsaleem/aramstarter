@props(['collapsible' => false])

<span {{ $attributes->class('flex min-w-0 items-center gap-2.5') }}>
    <x-app-logo-icon class="size-8 shrink-0 drop-shadow-[0_4px_12px_rgba(124,58,237,0.35)]" />
    <span @class([
        'truncate text-[0.95rem] font-semibold tracking-tight text-zinc-900 dark:text-white',
        'sidebar-collapsed:lg:hidden' => $collapsible,
    ])>{{ config('app.name') }}</span>
</span>
