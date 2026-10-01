<div>
    <x-auth-header icon="shield-check" :title="__('Confirm your password')" :description="__('This is a secure area of the application. Please confirm your password before continuing.')" />

    @if (auth()->user()->hasPassword())
        <form wire:submit="confirmPassword" class="space-y-5">
            <x-ui.input wire:model="password" icon="lock-closed" :label="__('Password')" autocomplete="current-password" viewable required autofocus />

            <x-ui.button type="submit" size="lg" class="w-full" loading="confirmPassword">{{ __('Confirm') }}</x-ui.button>
        </form>
    @else
        <x-ui.callout variant="warning" :heading="__('Set a password first')">
            {{ __('You signed up with a social account, so your account has no password yet.') }}
            <a href="{{ route('settings.password') }}" wire:navigate>{{ __('Set a password') }}</a>
        </x-ui.callout>
    @endif
</div>
