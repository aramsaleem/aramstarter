<div>
    <x-auth-header icon="envelope" :title="__('Verify your email')" :description="__('Please verify your email address by clicking on the link we just emailed to you.')" />

    @if (session('status') === 'verification-link-sent')
        <x-ui.callout variant="success" class="mb-6">
            {{ __('A new verification link has been sent to your email address.') }}
        </x-ui.callout>
    @endif

    <x-ui.error for="verification" class="mb-4" />

    <div class="space-y-3">
        <x-ui.button size="lg" class="w-full" wire:click="sendVerification" loading="sendVerification">
            {{ __('Resend verification email') }}
        </x-ui.button>

        <x-ui.button variant="ghost" class="w-full" wire:click="logout">
            {{ __('Log out') }}
        </x-ui.button>
    </div>
</div>
