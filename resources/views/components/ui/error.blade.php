@props(['for', 'id' => null])

@error($for)
    <p @if ($id) id="{{ $id }}" @endif {{ $attributes->class('flex items-center gap-1.5 text-sm text-red-600 dark:text-red-400') }}>
        <x-heroicon-o-exclamation-circle class="size-4 shrink-0" />
        {{ $message }}
    </p>
@enderror
