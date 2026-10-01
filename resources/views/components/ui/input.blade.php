{{--
    <x-ui.input wire:model="email" type="email" :label="__('Email address')" icon="envelope" required />
    <x-ui.input wire:model="password" :label="__('Password')" viewable />
    The "class" attribute styles the wrapper; every other attribute goes to the <input>.
--}}
@props([
    'label' => null,
    'description' => null,
    'type' => 'text',
    'viewable' => false,
    'icon' => null,
    'inputClass' => null,
])

@php
    $name = $attributes->get('name', $attributes->wire('model')->value());
    $id = $attributes->get('id', $name ? 'input-'.str_replace('.', '-', $name) : null);
    $invalid = $name && $errors->has($name);
@endphp

<div {{ $attributes->only('class')->class('space-y-2') }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $label }}</label>
    @endif

    <div class="group/input relative" @if ($viewable) x-data="{ visible: false }" @endif>
        @if ($icon)
            <x-dynamic-component
                :component="'heroicon-o-'.$icon"
                class="pointer-events-none absolute start-3.5 top-1/2 size-4.5 -translate-y-1/2 text-zinc-400 transition-colors group-focus-within/input:text-primary-500"
            />
        @endif

        <input
            id="{{ $id }}"
            @if ($name) name="{{ $name }}" @endif
            @if ($viewable) type="password" x-bind:type="visible ? 'text' : 'password'" @else type="{{ $type }}" @endif
            @if ($invalid) aria-invalid="true" aria-describedby="{{ $id }}-error" @endif
            {{ $attributes->except(['class', 'id', 'name'])->class([
                'block h-11 w-full rounded-xl border-0 bg-white px-3.5 text-sm text-zinc-900 shadow-xs ring-1 transition-shadow ring-inset placeholder:text-zinc-400 focus:ring-2 focus:ring-inset disabled:cursor-not-allowed disabled:opacity-60 dark:bg-white/[0.03] dark:text-white dark:placeholder:text-zinc-500',
                'ps-10' => $icon,
                'pe-11' => $viewable,
                $inputClass,
                'ring-zinc-200 focus:shadow-[0_0_0_4px_rgb(139_92_246/0.12)] focus:ring-primary-500 dark:ring-white/10 dark:focus:ring-primary-400' => ! $invalid,
                'ring-red-400 focus:shadow-[0_0_0_4px_rgb(239_68_68/0.12)] focus:ring-red-500 dark:ring-red-500/60' => $invalid,
            ]) }}
        >

        @if ($viewable)
            <button
                type="button"
                x-on:click="visible = ! visible"
                class="absolute inset-y-0 end-0 flex items-center px-3.5 text-zinc-400 transition-colors hover:text-zinc-700 dark:hover:text-zinc-200"
                x-bind:aria-label="visible ? @js(__('Hide password')) : @js(__('Show password'))"
            >
                <x-heroicon-o-eye class="size-4.5" x-show="! visible" />
                <x-heroicon-o-eye-slash class="size-4.5" x-show="visible" x-cloak />
            </button>
        @endif
    </div>

    @if ($description)
        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif

    @if ($name)
        <x-ui.error :for="$name" :id="$id.'-error'" />
    @endif
</div>
