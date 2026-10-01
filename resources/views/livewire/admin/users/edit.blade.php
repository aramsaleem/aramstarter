<div>
    <x-ui.page-header :title="$user->name" :description="$user->email" :back="route('admin.users.index')" :back-label="__('Users')">
        <x-slot:actions>
            @can('impersonate', $user)
                <x-ui.button variant="secondary" icon="eye" wire:click="confirmImpersonate({{ $user->id }})">{{ __('Sign in as user') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            @include('livewire.admin.users.partials.form', ['editing' => true])
        </div>

        <div class="space-y-6">
            <x-ui.card>
                <div class="flex items-center gap-4">
                    <x-ui.avatar :user="$user" size="lg" />

                    <div class="min-w-0">
                        <p class="truncate font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Joined :date', ['date' => $user->created_at->translatedFormat('j M Y')]) }}
                        </p>
                    </div>
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</dt>
                        <dd>
                            @if ($user->hasVerifiedEmail())
                                <x-ui.badge color="green">{{ __('Verified') }}</x-ui.badge>
                            @else
                                <x-ui.badge color="amber">{{ __('Unverified') }}</x-ui.badge>
                            @endif
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Password') }}</dt>
                        <dd>
                            @if ($user->hasPassword())
                                <x-ui.badge color="green">{{ __('Set') }}</x-ui.badge>
                            @else
                                <x-ui.badge>{{ __('Social login only') }}</x-ui.badge>
                            @endif
                        </dd>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Two-factor') }}</dt>
                        <dd>
                            @if ($user->hasEnabledTwoFactorAuthentication())
                                <x-ui.badge color="sky">{{ __('Enabled') }}</x-ui.badge>
                            @else
                                <x-ui.badge>{{ __('Disabled') }}</x-ui.badge>
                            @endif
                        </dd>
                    </div>
                </dl>

                @if ($user->hasEnabledTwoFactorAuthentication())
                    <div class="mt-6 border-t border-zinc-200 pt-4 dark:border-white/10">
                        <x-ui.button variant="secondary" size="sm" icon="arrow-path" wire:click="confirmResetTwoFactor">
                            {{ __('Reset two-factor authentication') }}
                        </x-ui.button>
                        <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">{{ __('For users who lost both their device and their recovery codes.') }}</p>
                    </div>
                @endif
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Connected accounts') }}</h2>

                @if ($user->socialAccounts->isEmpty())
                    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('No social accounts connected.') }}</p>
                @else
                    <ul class="mt-4 space-y-3">
                        @foreach ($user->socialAccounts as $account)
                            <li class="flex items-center gap-3 text-sm" wire:key="social-{{ $account->id }}">
                                <x-dynamic-component :component="'icons.'.$account->provider->value" class="size-4.5" />
                                <span class="font-medium text-zinc-900 dark:text-white">{{ $account->provider->label() }}</span>
                                <span class="truncate text-zinc-500 dark:text-zinc-400">{{ $account->provider_email }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-ui.card>

            @can('delete', $user)
                <x-ui.card class="border-red-200! dark:border-red-500/20!">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Delete user') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('The account and all of its data will be permanently deleted.') }}</p>

                    <x-ui.button variant="danger" size="sm" icon="trash" class="mt-4" wire:click="confirmDelete">
                        {{ __('Delete user') }}
                    </x-ui.button>
                </x-ui.card>
            @endcan
        </div>
    </div>
</div>
