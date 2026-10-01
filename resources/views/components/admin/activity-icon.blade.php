{{-- The icon of an audit event, coloured by severity: <x-admin.activity-icon :event="$log->event" /> --}}
@props(['event', 'size' => 'md'])

@php
    $tone = match ($event->severity()) {
        'danger' => 'bg-red-500/10 text-red-600 ring-red-500/25 dark:text-red-400',
        'warning' => 'bg-amber-500/10 text-amber-600 ring-amber-500/25 dark:text-amber-400',
        'success' => 'bg-emerald-500/10 text-emerald-600 ring-emerald-500/25 dark:text-emerald-400',
        default => 'bg-primary-500/10 text-primary-600 ring-primary-500/20 dark:text-primary-300',
    };
@endphp

<span {{ $attributes->class([
    'relative flex shrink-0 items-center justify-center ring-1 ring-inset',
    $tone,
    $size === 'sm' ? 'size-8 rounded-lg' : 'size-10 rounded-xl',
]) }}>
    <x-dynamic-component :component="'heroicon-o-'.$event->icon()" @class(['size-4' => $size === 'sm', 'size-5' => $size !== 'sm']) />
</span>
