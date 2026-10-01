{{--
    <x-ui.select wire:model.live="role" :label="__('Role')">
        <option value="">{{ __('All roles') }}</option>
    </x-ui.select>
--}}
@props(['label' => null, 'description' => null])

@php
    $name = $attributes->get('name', $attributes->wire('model')->value());
    $id = $attributes->get('id', $name ? 'select-'.str_replace('.', '-', $name) : null);
    $invalid = $name && $errors->has($name);
@endphp

<div {{ $attributes->only('class')->class('space-y-2') }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</label>
    @endif

    <select
        id="{{ $id }}"
        @if ($name) name="{{ $name }}" @endif
        {{ $attributes->except(['class', 'id', 'name'])->class([
            'block h-11 w-full rounded-xl border-0 bg-white py-0 ps-3.5 pe-10 text-sm text-zinc-900 shadow-xs ring-1 ring-inset focus:ring-2 focus:ring-inset dark:bg-white/[0.03] dark:text-white dark:[&>option]:bg-zinc-900',
            'ring-zinc-200 focus:ring-primary-500 dark:ring-white/10 dark:focus:ring-primary-400' => ! $invalid,
            'ring-red-400 focus:ring-red-500' => $invalid,
        ]) }}
    >
        {{ $slot }}
    </select>

    @if ($description)
        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif

    @if ($name)
        <x-ui.error :for="$name" />
    @endif
</div>
