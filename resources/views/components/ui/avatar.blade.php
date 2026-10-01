@props(['user' => null, 'name' => null, 'size' => 'md'])

@php
    $name ??= $user?->name ?? '';
    $initials = $user?->initials()
        ?? \Illuminate\Support\Str::of($name)->explode(' ')->filter()->take(2)
            ->map(fn (string $word) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($word, 0, 1)))
            ->implode('');

    $palette = [
        'bg-primary-100 text-primary-700 dark:bg-primary-500/20 dark:text-primary-200',
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-200',
        'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-200',
        'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
        'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-200',
        'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-200',
    ];

    $sizes = [
        'xs' => 'size-6 text-[0.625rem]',
        'sm' => 'size-8 text-xs',
        'md' => 'size-10 text-sm',
        'lg' => 'size-14 text-lg',
    ];
@endphp

<span {{ $attributes->class([
    'inline-flex shrink-0 items-center justify-center rounded-full font-semibold select-none',
    $sizes[$size] ?? $sizes['md'],
    $palette[crc32($name) % count($palette)],
]) }} aria-hidden="true">{{ $initials }}</span>
