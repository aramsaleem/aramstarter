{{--
    <x-ui.checkbox wire:model="remember" :label="__('Remember me')" />
    <x-ui.checkbox wire:model="form.roles" value="Admin" label="Admin" />   // array binding
--}}
@props(['label' => null, 'description' => null])

@php
    $model = $attributes->wire('model')->value();
    $id = $attributes->get('id', 'checkbox-'.md5($model.$attributes->get('value')));
@endphp

<div {{ $attributes->only('class')->class('flex items-start gap-3') }}>
    <div class="flex h-5 shrink-0 items-center">
        <input
            type="checkbox"
            id="{{ $id }}"
            {{ $attributes->except(['class', 'id'])->class('size-4.5 rounded-md border-zinc-300 text-primary-600 shadow-xs transition focus:ring-primary-500 focus:ring-offset-0 dark:border-white/20 dark:bg-white/[0.04] dark:checked:bg-primary-500') }}
        >
    </div>

    @if ($label || $description || $slot->isNotEmpty())
        <div class="min-w-0 text-sm leading-5">
            <label for="{{ $id }}" class="font-medium text-zinc-800 select-none dark:text-zinc-200">{{ $label ?? $slot }}</label>

            @if ($description)
                <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
            @endif
        </div>
    @endif
</div>
