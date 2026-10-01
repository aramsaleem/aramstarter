@props(['padding' => true])

<div {{ $attributes->class([
    'relative rounded-2xl border border-zinc-200/80 bg-white shadow-[0_1px_2px_rgb(0_0_0/0.04),0_8px_24px_-12px_rgb(0_0_0/0.08)] dark:border-white/[0.07] dark:bg-white/[0.025] dark:shadow-[inset_0_1px_0_rgb(255_255_255/0.04)]',
    'p-6' => $padding,
]) }}>
    {{ $slot }}
</div>
