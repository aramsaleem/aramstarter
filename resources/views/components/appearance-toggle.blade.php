@props(['align' => 'end', 'position' => 'bottom'])

@php
    $modes = [
        'light' => [__('Light'), 'sun'],
        'dark' => [__('Dark'), 'moon'],
        'system' => [__('System'), 'computer-desktop'],
    ];
@endphp

<x-ui.dropdown :align="$align" :position="$position" width="w-44">
    <x-slot:trigger
        aria-label="{{ __('Theme') }}"
        class="inline-flex size-9 items-center justify-center rounded-xl text-zinc-600 transition-colors hover:bg-zinc-900/5 hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-white/[0.07] dark:hover:text-white"
    >
        @foreach ($modes as $mode => [$label, $icon])
            <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-4.5" x-cloak x-show="$store.appearance.mode === '{{ $mode }}'" />
        @endforeach
    </x-slot:trigger>

    @foreach ($modes as $mode => [$label, $icon])
        <x-ui.dropdown-item :icon="$icon" x-on:click="$store.appearance.set('{{ $mode }}')">
            <span class="flex-1">{{ $label }}</span>
            <x-heroicon-s-check-circle class="size-4 text-primary-500" x-cloak x-show="$store.appearance.mode === '{{ $mode }}'" />
        </x-ui.dropdown-item>
    @endforeach
</x-ui.dropdown>
