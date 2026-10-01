{{--
    Visibility switch, reorder, edit and delete buttons for a website content item.
    <x-admin.content-controls :id="$feature->id" :active="$feature->is_active" :first="$loop->first" :last="$loop->last" :name="$feature->title" />
--}}
@props(['id', 'active', 'first' => false, 'last' => false, 'name'])

<div {{ $attributes->class('flex items-center justify-between gap-3') }}>
    <button
        type="button"
        role="switch"
        aria-checked="{{ $active ? 'true' : 'false' }}"
        wire:click="toggle({{ $id }})"
        wire:loading.attr="disabled"
        class="group/switch inline-flex items-center gap-2 text-xs font-medium text-zinc-600 dark:text-zinc-300"
    >
        <span @class([
            'relative inline-flex h-5 w-9 shrink-0 rounded-full transition-colors group-focus-visible/switch:ring-2 group-focus-visible/switch:ring-primary-500 group-focus-visible/switch:ring-offset-2 dark:group-focus-visible/switch:ring-offset-zinc-900',
            'bg-emerald-500' => $active,
            'bg-zinc-300 dark:bg-white/15' => ! $active,
        ])>
            <span @class(['absolute top-0.5 start-0.5 size-4 rounded-full bg-white shadow-sm transition-transform', 'translate-x-4 rtl:-translate-x-4' => $active])></span>
        </span>
        {{ $active ? __('Visible') : __('Hidden') }}
        <span class="sr-only">{{ $name }}</span>
    </button>

    <div class="flex items-center gap-0.5">
        <x-ui.button variant="ghost" size="sm" icon="arrow-up" wire:click="move({{ $id }}, -1)" :disabled="$first" :title="__('Move up')" class="px-2!">
            <span class="sr-only">{{ __('Move up') }}</span>
        </x-ui.button>
        <x-ui.button variant="ghost" size="sm" icon="arrow-down" wire:click="move({{ $id }}, 1)" :disabled="$last" :title="__('Move down')" class="px-2!">
            <span class="sr-only">{{ __('Move down') }}</span>
        </x-ui.button>
        <x-ui.button variant="ghost" size="sm" icon="pencil-square" wire:click="edit({{ $id }})" :title="__('Edit')" class="px-2!">
            <span class="sr-only">{{ __('Edit :name', ['name' => $name]) }}</span>
        </x-ui.button>
        <x-ui.button variant="ghost" size="sm" icon="trash" wire:click="confirmDelete({{ $id }})" :title="__('Delete')" class="px-2! text-red-600! dark:text-red-400!">
            <span class="sr-only">{{ __('Delete :name', ['name' => $name]) }}</span>
        </x-ui.button>
    </div>
</div>
