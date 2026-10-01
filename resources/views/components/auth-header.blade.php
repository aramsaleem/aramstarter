@props(['title', 'description' => null, 'icon' => null])

<div class="mb-8">
    @if ($icon)
        <span class="mb-5 flex size-12 items-center justify-center rounded-2xl bg-linear-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30 ring-1 ring-white/20">
            <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-6" />
        </span>
    @endif

    <h1 class="text-2xl font-semibold tracking-tight text-zinc-900 sm:text-3xl dark:text-white">{{ $title }}</h1>

    @if ($description)
        <p class="mt-2 text-sm leading-6 text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif
</div>
