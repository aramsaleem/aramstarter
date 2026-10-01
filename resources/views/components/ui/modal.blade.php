{{--
    Opened and closed through a boolean property on the Livewire component:
    <x-ui.modal wire:model="showModal" :title="__('Edit permission')"> ... </x-ui.modal>
--}}
@props(['title' => null, 'description' => null, 'maxWidth' => 'md', 'icon' => null])

@php
    $property = $attributes->wire('model')->value();
    $titleId = 'modal-title-'.str_replace('.', '-', $property);
    $width = match ($maxWidth) {
        'sm' => 'sm:max-w-sm',
        'lg' => 'sm:max-w-lg',
        'xl' => 'sm:max-w-xl',
        '2xl' => 'sm:max-w-2xl',
        default => 'sm:max-w-md',
    };
@endphp

<div
    x-data="{ show: $wire.entangle(@js($property)) }"
    x-show="show"
    x-cloak
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-[60] flex justify-center overflow-y-auto p-4 sm:py-10"
    role="dialog"
    aria-modal="true"
    @if ($title) aria-labelledby="{{ $titleId }}" @endif
>
    <div x-show="show" x-transition.opacity.duration.200ms class="fixed inset-0 bg-zinc-950/40 backdrop-blur-md" x-on:click="show = false"></div>

    <div
        x-show="show"
        x-trap.inert.noscroll="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative mt-auto w-full {{ $width }} overflow-hidden sm:my-auto rounded-3xl border border-zinc-200/80 bg-white p-6 shadow-2xl shadow-zinc-950/10 sm:p-7 dark:border-white/10 dark:bg-zinc-900/95"
    >
        <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 -top-24 h-40 bg-radial from-primary-500/20 to-transparent to-70%"></div>

        <div class="relative">
            @if ($icon)
                <span class="mb-4 flex size-11 items-center justify-center rounded-xl bg-primary-500/10 text-primary-600 ring-1 ring-primary-500/20 dark:text-primary-300">
                    <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5" />
                </span>
            @endif

            @if ($title)
                <h2 id="{{ $titleId }}" class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $title }}</h2>
            @endif

            @if ($description)
                <p class="mt-1.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
            @endif

            <div @class(['mt-6' => $title || $description])>
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
