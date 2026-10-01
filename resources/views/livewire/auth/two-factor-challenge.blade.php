<div>
    @if ($usingRecoveryCode)
        <x-auth-header icon="lifebuoy" :title="__('Use a recovery code')" :description="__('Enter one of the emergency recovery codes you saved when you set up two-factor authentication.')" />
    @else
        <x-auth-header icon="finger-print" :title="__('Two-factor authentication')" :description="__('Enter the 6-digit code from your authenticator app.')" />
    @endif

    <form wire:submit="authenticate" class="space-y-5">
        @if ($usingRecoveryCode)
            <x-ui.input
                wire:model="recovery_code"
                wire:key="recovery-code"
                :label="__('Recovery code')"
                placeholder="abcde-12345"
                autocomplete="one-time-code"
                input-class="font-mono"
                dir="ltr"
                required
                autofocus
            />
        @else
            <x-ui.input
                wire:model="code"
                wire:key="code"
                :label="__('Authentication code')"
                placeholder="000000"
                inputmode="numeric"
                pattern="[0-9 ]*"
                maxlength="7"
                autocomplete="one-time-code"
                input-class="h-14! text-center font-mono text-2xl! tracking-[0.6em]"
                dir="ltr"
                required
                autofocus
            />
        @endif

        <x-ui.button type="submit" size="lg" class="w-full" loading="authenticate">{{ __('Continue') }}</x-ui.button>
    </form>

    <button type="button" wire:click="toggleRecoveryCode" class="mt-8 text-sm font-medium text-primary-600 hover:text-primary-500 hover:underline dark:text-primary-400">
        {{ $usingRecoveryCode ? __('Use an authentication code instead') : __('Use a recovery code instead') }}
    </button>
</div>
