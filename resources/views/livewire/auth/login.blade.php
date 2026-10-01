<div>
    <x-auth-header :title="__('Log in to your account')" :description="__('Welcome back! Enter your details to continue.')" />

    <x-auth-session-status class="mb-6" :status="session('status')" />

    @session('error')
        <x-ui.callout variant="danger" class="mb-6">{{ $value }}</x-ui.callout>
    @endsession

    <x-social-login class="mb-6" />

    <form wire:submit="login" class="space-y-5">
        <x-ui.input
            wire:model="email"
            type="email"
            icon="envelope"
            :label="__('Email address')"
            placeholder="email@example.com"
            autocomplete="email"
            required
            autofocus
        />

        <div class="relative">
            <x-ui.input
                wire:model="password"
                icon="lock-closed"
                :label="__('Password')"
                autocomplete="current-password"
                viewable
                required
            />

            <x-ui.link :href="route('password.request')" class="absolute end-0 top-0 text-sm" wire:navigate>
                {{ __('Forgot password?') }}
            </x-ui.link>
        </div>

        <x-ui.checkbox wire:model="remember" :label="__('Remember me')" />

        <x-ui.button type="submit" size="lg" class="w-full" loading="login">
            {{ __('Log in') }}
            <x-heroicon-o-arrow-right class="size-4 rtl:-scale-x-100" wire:loading.remove wire:target="login" />
        </x-ui.button>
    </form>

    <p class="mt-8 text-sm text-zinc-500 dark:text-zinc-400">
        {{ __("Don't have an account?") }}
        <x-ui.link :href="route('register')" wire:navigate>{{ __('Sign up') }}</x-ui.link>
    </p>
</div>
