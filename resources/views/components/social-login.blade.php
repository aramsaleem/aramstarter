{{-- "Continue with ..." buttons for every social provider that has credentials configured. --}}
@php
    $providers = \App\Enums\SocialProvider::enabled();
@endphp

@if ($providers !== [])
    <div {{ $attributes->class('space-y-5') }}>
        <div @class(['grid gap-2.5', 'sm:grid-cols-3' => count($providers) === 3, 'sm:grid-cols-2' => count($providers) === 2])>
            @foreach ($providers as $provider)
                <a
                    href="{{ route('social.redirect', $provider) }}"
                    title="{{ __('Continue with :provider', ['provider' => $provider->label()]) }}"
                    class="inline-flex h-11 items-center justify-center gap-2.5 rounded-xl bg-white px-4 text-sm font-medium text-zinc-800 shadow-xs ring-1 ring-zinc-200 transition hover:bg-zinc-50 hover:ring-zinc-300 dark:bg-white/[0.04] dark:text-zinc-100 dark:ring-white/10 dark:hover:bg-white/[0.08]"
                >
                    <x-dynamic-component :component="'icons.'.$provider->value" class="size-4.5" />
                    <span @class(['sm:sr-only' => count($providers) > 1])>{{ __('Continue with :provider', ['provider' => $provider->label()]) }}</span>
                    @if (count($providers) > 1)
                        <span class="hidden sm:inline" aria-hidden="true">{{ $provider->label() }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-3 text-xs font-medium text-zinc-400 uppercase dark:text-zinc-500">
            <span class="h-px flex-1 bg-linear-to-r from-transparent to-zinc-200 rtl:bg-linear-to-l dark:to-white/10"></span>
            {{ __('or') }}
            <span class="h-px flex-1 bg-linear-to-l from-transparent to-zinc-200 rtl:bg-linear-to-r dark:to-white/10"></span>
        </div>
    </div>
@endif
