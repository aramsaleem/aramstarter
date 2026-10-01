{{--
    <x-ui.page-header :title="__('Users')" :description="..." :back="route('admin.users.index')" :back-label="__('Users')">
        <x-slot:actions> ... </x-slot:actions>
    </x-ui.page-header>
--}}
@props(['title', 'description' => null, 'back' => null, 'backLabel' => null, 'eyebrow' => null])

<div {{ $attributes->class('mb-8') }}>
    @if ($back)
        <a href="{{ $back }}" wire:navigate class="group mb-4 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
            <x-heroicon-o-arrow-left class="size-4 transition-transform group-hover:-translate-x-0.5 rtl:-scale-x-100 rtl:group-hover:translate-x-0.5" />
            {{ $backLabel ?? __('Back') }}
        </a>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div class="min-w-0">
            @if ($eyebrow)
                <p class="mb-2 text-xs font-semibold tracking-[0.14em] text-primary-600 uppercase dark:text-primary-400">{{ $eyebrow }}</p>
            @endif

            <h1 class="text-2xl font-semibold tracking-tight text-balance text-zinc-900 sm:text-3xl dark:text-white">{{ $title }}</h1>

            @if ($description)
                <p class="mt-2 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
            @endif
        </div>

        @isset($actions)
            <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
        @endisset
    </div>
</div>
