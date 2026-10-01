@php
    $user = $this->user;
@endphp

<x-settings.layout
    :heading="__('Two-factor authentication')"
    :subheading="__('Add an extra layer of security by asking for a code from your phone when you log in.')"
>
    <div class="space-y-6">
        @if ($user->hasEnabledTwoFactorAuthentication())
            <x-ui.callout variant="success" icon="shield-check" :heading="__('Two-factor authentication is enabled')">
                {{ __('You will be asked for a code from your authenticator app when you log in.') }}
            </x-ui.callout>

            @if ($showingRecoveryCodes)
                <div class="space-y-4 rounded-2xl border border-zinc-200/80 p-5 dark:border-white/[0.08]">
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Recovery codes') }}</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                            {{ __('Store these codes in a password manager. Each code can be used once to log in if you lose access to your authenticator app.') }}
                        </p>
                    </div>

                    @if ($user->recoveryCodes() !== [])
                        <ul class="grid grid-cols-2 gap-2 rounded-xl bg-zinc-950 p-4 font-mono text-sm text-emerald-300 shadow-inner" dir="ltr">
                            @foreach ($user->recoveryCodes() as $code)
                                <li wire:key="recovery-code-{{ $loop->index }}" class="rounded-md px-2 py-1 hover:bg-white/5">{{ $code }}</li>
                            @endforeach
                        </ul>
                    @else
                        <x-ui.callout variant="warning">{{ __('You have used all of your recovery codes. Generate new ones.') }}</x-ui.callout>
                    @endif

                    <div class="flex flex-wrap gap-2">
                        @if ($user->recoveryCodes() !== [])
                            <x-ui.button
                                variant="secondary"
                                size="sm"
                                icon="clipboard-document"
                                x-data="{ copied: false }"
                                x-on:click="window.copyToClipboard({{ Js::from(implode(PHP_EOL, $user->recoveryCodes())) }}).then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                            >
                                <span x-text="copied ? {{ Js::from(__('Copied!')) }} : {{ Js::from(__('Copy')) }}">{{ __('Copy') }}</span>
                            </x-ui.button>
                        @endif

                        <x-ui.button variant="secondary" size="sm" icon="arrow-path" wire:click="regenerateRecoveryCodes" loading="regenerateRecoveryCodes">
                            {{ __('Regenerate codes') }}
                        </x-ui.button>
                    </div>
                </div>
            @else
                <x-ui.button variant="secondary" icon="eye" wire:click="showRecoveryCodes">
                    {{ __('Show recovery codes') }}
                    <x-ui.badge>{{ count($user->recoveryCodes()) }}</x-ui.badge>
                </x-ui.button>
            @endif

            <div class="border-t border-zinc-200/80 pt-6 dark:border-white/[0.07]">
                <x-ui.button variant="danger" icon="shield-exclamation" wire:click="confirmDisable">
                    {{ __('Disable two-factor authentication') }}
                </x-ui.button>
            </div>
        @elseif ($user->two_factor_secret)
            <ol class="space-y-8">
                <li class="flex gap-4">
                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary-500 text-xs font-semibold text-white shadow-md shadow-primary-500/30">1</span>

                    <div class="min-w-0 flex-1 space-y-4">
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">
                            {{ __('Scan the QR code with your authenticator app, or enter the setup key manually.') }}
                        </p>

                        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                            <div class="relative w-fit shrink-0">
                                <div aria-hidden="true" class="absolute -inset-2 rounded-3xl bg-linear-to-br from-primary-500/30 to-accent-400/30 blur-lg"></div>
                                <div class="relative rounded-2xl bg-white p-3 shadow-lg ring-1 ring-zinc-200 [&_svg]:size-44">
                                    {!! $user->twoFactorQrCodeSvg() !!}
                                </div>
                            </div>

                            <div class="min-w-0 space-y-2">
                                <p class="text-xs font-semibold tracking-[0.12em] text-zinc-500 uppercase dark:text-zinc-400">{{ __('Setup key') }}</p>
                                <code class="block rounded-xl bg-zinc-950 px-4 py-3 font-mono text-sm break-all text-emerald-300" dir="ltr">{{ trim(chunk_split($user->two_factor_secret, 4, ' ')) }}</code>
                            </div>
                        </div>
                    </div>
                </li>

                <li class="flex gap-4">
                    <span class="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary-500 text-xs font-semibold text-white shadow-md shadow-primary-500/30">2</span>

                    <form wire:submit="confirmSetup" class="min-w-0 flex-1 space-y-4">
                        <x-ui.input
                            wire:model="code"
                            :label="__('Enter the 6-digit code from the app to finish.')"
                            placeholder="000000"
                            inputmode="numeric"
                            maxlength="7"
                            autocomplete="one-time-code"
                            input-class="font-mono text-lg! tracking-[0.4em]"
                            class="max-w-xs"
                            dir="ltr"
                            required
                        />

                        <div class="flex gap-2">
                            <x-ui.button type="submit" loading="confirmSetup">{{ __('Confirm') }}</x-ui.button>
                            <x-ui.button variant="ghost" wire:click="cancelSetup">{{ __('Cancel') }}</x-ui.button>
                        </div>
                    </form>
                </li>
            </ol>
        @else
            <div class="relative overflow-hidden rounded-2xl border border-zinc-200/80 p-6 dark:border-white/[0.08]">
                <div aria-hidden="true" class="absolute -end-10 -top-10 size-40 rounded-full bg-primary-500/15 blur-3xl"></div>

                <span class="relative flex size-12 items-center justify-center rounded-2xl bg-linear-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30 ring-1 ring-white/20">
                    <x-heroicon-o-finger-print class="size-6" />
                </span>

                <h3 class="relative mt-5 font-semibold text-zinc-900 dark:text-white">{{ __('Two-factor authentication is not enabled') }}</h3>
                <p class="relative mt-2 max-w-lg text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                    {{ __('When it is enabled, you will be asked for a secure, random code during login. Get the code from an app such as Google Authenticator, Authy or 1Password.') }}
                </p>

                <x-ui.button icon="shield-check" wire:click="enable" loading="enable" class="relative mt-6">
                    {{ __('Enable two-factor authentication') }}
                </x-ui.button>
            </div>
        @endif
    </div>
</x-settings.layout>
