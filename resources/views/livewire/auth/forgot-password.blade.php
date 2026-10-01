<div>
    <x-auth-header icon="key" :title="__('Forgot password')" :description="__('Enter your email address and we will send you a link to reset your password.')" />

    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="space-y-5">
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

        <x-ui.button type="submit" size="lg" class="w-full" loading="sendPasswordResetLink">{{ __('Email password reset link') }}</x-ui.button>
    </form>

    <a href="{{ route('login') }}" wire:navigate class="group mt-8 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
        <x-heroicon-o-arrow-left class="size-4 transition-transform group-hover:-translate-x-0.5 rtl:-scale-x-100" />
        {{ __('Back to log in') }}
    </a>
</div>
