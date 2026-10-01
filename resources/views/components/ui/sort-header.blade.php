{{-- A sortable table header cell that calls sort('column') on the Livewire component. --}}
@props(['column', 'sortBy', 'sortDirection'])

@php
    $active = $sortBy === $column;
@endphp

<th scope="col" {{ $attributes->class('px-5 py-3 text-start font-medium') }} @if ($active) aria-sort="{{ $sortDirection === 'asc' ? 'ascending' : 'descending' }}" @endif>
    <button type="button" wire:click="sort('{{ $column }}')" @class(['group inline-flex items-center gap-1 uppercase transition-colors hover:text-zinc-900 dark:hover:text-white', 'text-zinc-900 dark:text-white' => $active])>
        {{ $slot }}

        <span @class(['flex size-4 items-center justify-center rounded transition', 'bg-primary-500/15 text-primary-600 dark:text-primary-300' => $active, 'invisible text-zinc-400 group-hover:visible' => ! $active])>
            @if ($active && $sortDirection === 'asc')
                <x-heroicon-o-chevron-up class="size-3" />
            @else
                <x-heroicon-o-chevron-down class="size-3" />
            @endif
        </span>
    </button>
</th>
