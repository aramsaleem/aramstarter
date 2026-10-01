{{-- Soft, slowly drifting gradient light with a faint grid - purely decorative. --}}
@props(['subtle' => false, 'grid' => true])

<div aria-hidden="true" {{ $attributes->class('pointer-events-none overflow-hidden') }}>
    <div @class([
        'animate-aurora absolute -top-72 start-[-8%] size-[44rem] rounded-full blur-[120px]',
        'bg-primary-500/25 dark:bg-primary-600/25' => ! $subtle,
        'bg-primary-500/10 dark:bg-primary-600/15' => $subtle,
    ])></div>

    <div @class([
        'animate-aurora absolute -top-48 end-[-12%] size-[38rem] rounded-full blur-[120px] [animation-delay:-9s]',
        'bg-accent-400/20 dark:bg-accent-500/15' => ! $subtle,
        'bg-accent-400/10 dark:bg-accent-500/10' => $subtle,
    ])></div>

    @unless ($subtle)
        <div class="animate-aurora absolute -bottom-72 start-1/3 size-[32rem] rounded-full bg-fuchsia-400/15 blur-[120px] [animation-delay:-15s] dark:bg-fuchsia-500/10"></div>
    @endunless

    @if ($grid)
        <div class="bg-grid absolute inset-0"></div>
    @endif
</div>
