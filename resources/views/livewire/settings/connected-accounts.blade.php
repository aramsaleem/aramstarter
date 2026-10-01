<x-settings.layout
    :heading="__('Connected accounts')"
    :subheading="__('Connect your social accounts to log in with a single click.')"
>
    @if ($this->providers === [])
        <x-ui.empty-state
            icon="link"
            :title="__('Social login is not configured')"
            :description="__('Add the OAuth credentials for Google, Facebook or X to your .env file to turn it on.')"
            class="py-8!"
        />
    @else
        <ul role="list" class="space-y-3">
            @foreach ($this->providers as $provider)
                @php
                    $account = $this->accounts->get($provider->value);
                @endphp

                <li class="flex items-center gap-4 rounded-2xl border border-zinc-200/80 p-4 transition hover:border-zinc-300 dark:border-white/[0.08] dark:hover:border-white/15" wire:key="provider-{{ $provider->value }}">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-white shadow-xs ring-1 ring-zinc-200 dark:bg-white/[0.04] dark:ring-white/10">
                        <x-dynamic-component :component="'icons.'.$provider->value" class="size-5" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="flex items-center gap-2 text-sm font-medium text-zinc-900 dark:text-white">
                            {{ $provider->label() }}
                            @if ($account)
                                <x-ui.badge color="green" dot>{{ __('Connected') }}</x-ui.badge>
                            @endif
                        </p>
                        <p class="truncate text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $account ? ($account->provider_email ?? __('Connected')) : __('Not connected') }}
                        </p>
                    </div>

                    @if ($account)
                        <x-ui.button variant="secondary" size="sm" wire:click="confirmDisconnect('{{ $provider->value }}')">
                            {{ __('Disconnect') }}
                        </x-ui.button>
                    @elseif ($provider->isEnabled())
                        <x-ui.button size="sm" :href="route('social.redirect', $provider)" icon="plus">
                            {{ __('Connect') }}
                        </x-ui.button>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</x-settings.layout>
