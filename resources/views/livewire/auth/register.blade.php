<div>
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account.')" />

    <x-social-login class="mb-6" />

    <form wire:submit="register" class="space-y-5">
        <x-ui.input wire:model="name" icon="user" :label="__('Name')" autocomplete="name" required autofocus />

        <x-ui.input
            wire:model="email"
            type="email"
            icon="envelope"
            :label="__('Email address')"
            placeholder="email@example.com"
            autocomplete="email"
            required
        />

        <div class="grid gap-5 sm:grid-cols-2">
            <x-ui.input wire:model="password" :label="__('Password')" autocomplete="new-password" viewable required />
            <x-ui.input wire:model="password_confirmation" :label="__('Confirm password')" autocomplete="new-password" viewable required />
        </div>

        <x-ui.button type="submit" size="lg" class="w-full" loading="register">
            {{ __('Create account') }}
            <x-heroicon-o-arrow-right class="size-4 rtl:-scale-x-100" wire:loading.remove wire:target="register" />
        </x-ui.button>
    </form>

    <p class="mt-8 text-sm text-zinc-500 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <x-ui.link :href="route('login')" wire:navigate>{{ __('Log in') }}</x-ui.link>
    </p>
</div>
