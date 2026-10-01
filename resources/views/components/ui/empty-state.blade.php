@props(['icon' => 'inbox', 'title', 'description' => null])

<div {{ $attributes->class('flex flex-col items-center justify-center px-6 py-16 text-center') }}>
    <div class="relative">
        <div aria-hidden="true" class="absolute inset-0 rounded-2xl bg-primary-500/20 blur-xl"></div>
        <div class="relative flex size-14 items-center justify-center rounded-2xl border border-zinc-200 bg-white text-primary-500 shadow-sm dark:border-white/10 dark:bg-zinc-900">
            <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-6" />
        </div>
    </div>

    <p class="mt-5 text-sm font-semibold text-zinc-900 dark:text-white">{{ $title }}</p>

    @if ($description)
        <p class="mt-1.5 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
