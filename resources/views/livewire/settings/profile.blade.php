<x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address.')">
    <form wire:submit="updateProfileInformation" class="space-y-6">
        <x-ui.input wire:model="name" :label="__('Name')" autocomplete="name" required />

        <div class="space-y-3">
            <x-ui.input wire:model="email" type="email" :label="__('Email address')" autocomplete="email" required />

            @unless (auth()->user()->hasVerifiedEmail())
                <x-ui.callout variant="warning">
                    {{ __('Your email address is unverified.') }}
                    <button type="button" wire:click="resendVerificationNotification" class="font-medium underline">
                        {{ __('Resend the verification email.') }}
                    </button>
                </x-ui.callout>
            @endunless
        </div>

        <x-ui.button type="submit" loading="updateProfileInformation">{{ __('Save') }}</x-ui.button>
    </form>

    @unless (\App\Support\Impersonation::active())
        <livewire:settings.delete-user-form />
    @endunless
</x-settings.layout>
