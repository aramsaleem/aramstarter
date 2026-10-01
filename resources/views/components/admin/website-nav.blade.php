{{-- Tabs shared by the Admin > Website pages. --}}
@php
    $tabs = [
        ['admin.website.settings', __('Content'), 'document-text'],
        ['admin.website.features', __('Features'), 'squares-plus'],
        ['admin.website.plans', __('Pricing'), 'banknotes'],
        ['admin.website.faqs', __('FAQ'), 'question-mark-circle'],
    ];
@endphp

<div {{ $attributes->class('mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between') }}>
    <nav class="flex gap-1 overflow-x-auto rounded-2xl border border-zinc-200/80 bg-zinc-100/70 p-1 dark:border-white/[0.07] dark:bg-white/[0.03]" aria-label="{{ __('Website') }}">
        @foreach ($tabs as [$route, $label, $icon])
            @php $active = request()->routeIs($route); @endphp
            <a
                href="{{ route($route) }}"
                wire:navigate
                @if ($active) aria-current="page" @endif
                @class([
                    'inline-flex h-9 shrink-0 items-center gap-2 rounded-xl px-3.5 text-sm font-medium transition',
                    'bg-white text-zinc-900 shadow-xs ring-1 ring-zinc-200 dark:bg-white/10 dark:text-white dark:ring-white/10' => $active,
                    'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => ! $active,
                ])
            >
                <x-dynamic-component :component="'heroicon-o-'.$icon" @class(['size-4', 'text-primary-600 dark:text-primary-400' => $active]) />
                {{ $label }}
            </a>
        @endforeach
    </nav>

    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 self-start text-sm font-medium text-zinc-500 transition hover:text-zinc-900 sm:self-auto dark:text-zinc-400 dark:hover:text-white">
        {{ __('View website') }}
        <x-heroicon-o-arrow-top-right-on-square class="size-4 rtl:-scale-x-100" />
    </a>
</div>
