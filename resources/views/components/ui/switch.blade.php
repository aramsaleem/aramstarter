{{--
    An on/off switch backed by a checkbox:
    <x-ui.switch wire:model="is_active" :label="__('Visible on the website')" />
--}}
@props(['label', 'description' => null])

@php
    $id = $attributes->get('id', 'switch-'.md5($attributes->wire('model')->value().$label));
@endphp

<label for="{{ $id }}" {{ $attributes->only('class')->class('flex cursor-pointer items-start justify-between gap-4') }}>
    <span class="min-w-0">
        <span class="block text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $label }}</span>

        @if ($description)
            <span class="mt-0.5 block text-xs text-zinc-500 dark:text-zinc-400">{{ $description }}</span>
        @endif
    </span>

    <span class="relative inline-flex shrink-0">
        <input type="checkbox" role="switch" id="{{ $id }}" {{ $attributes->except(['class', 'id'])->class('peer sr-only') }}>
        <span aria-hidden="true" class="h-6 w-11 rounded-full bg-zinc-200 shadow-inner transition-colors peer-checked:bg-primary-600 peer-focus-visible:ring-2 peer-focus-visible:ring-primary-500 peer-focus-visible:ring-offset-2 peer-disabled:opacity-50 dark:bg-white/10 dark:peer-checked:bg-primary-500 dark:peer-focus-visible:ring-offset-zinc-900"></span>
        <span aria-hidden="true" class="absolute start-0.5 top-0.5 size-5 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5 rtl:peer-checked:-translate-x-5"></span>
    </span>
</label>
