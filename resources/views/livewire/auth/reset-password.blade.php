<div>
    <x-auth-header icon="lock-closed" :title="__('Reset password')" :description="__('Choose a new password for your account.')" />

    <form wire:submit="resetPassword" class="space-y-5">
        <x-ui.input wire:model="email" type="email" icon="envelope" :label="__('Email address')" autocomplete="email" required />

        <x-ui.input wire:model="password" :label="__('New password')" autocomplete="new-password" viewable required autofocus />

        <x-ui.input wire:model="password_confirmation" :label="__('Confirm password')" autocomplete="new-password" viewable required />

        <x-ui.button type="submit" size="lg" class="w-full" loading="resetPassword">{{ __('Reset password') }}</x-ui.button>
    </form>
</div>
