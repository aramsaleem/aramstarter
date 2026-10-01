{{--
    <x-ui.dropdown align="end">
        <x-slot:trigger class="...">Open</x-slot:trigger>
        <x-ui.dropdown-item href="/settings">Settings</x-ui.dropdown-item>
    </x-ui.dropdown>
--}}
@props([
    'align' => 'end',
    'position' => 'bottom',
    'width' => 'w-52',
])

<div class="relative" x-data="{ open: false }" x-on:click.outside="open = false" x-on:keydown.escape="open = false">
    <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open" aria-haspopup="menu" {{ $trigger->attributes }}>
        {{ $trigger }}
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        role="menu"
        @class([
            'glass absolute z-50 rounded-2xl border border-zinc-200/80 p-1.5 shadow-xl shadow-zinc-950/10 dark:border-white/10 dark:shadow-black/40',
            $width,
            $align === 'start' ? 'start-0' : 'end-0',
            $position === 'top' ? 'bottom-full mb-2 origin-bottom' : 'top-full mt-2 origin-top',
        ])
    >
        {{ $slot }}
    </div>
</div>
