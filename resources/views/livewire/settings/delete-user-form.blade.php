<section class="mt-10 rounded-2xl border border-red-500/20 bg-red-500/[0.03] p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Delete account') }}</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Permanently delete your account and all of its data.') }}</p>
        </div>

        <x-ui.button variant="danger" icon="trash" wire:click="confirmUserDeletion" class="shrink-0">{{ __('Delete account') }}</x-ui.button>
    </div>

    <x-ui.modal
        wire:model="confirmingDeletion"
        icon="exclamation-triangle"
        :title="__('Are you sure you want to delete your account?')"
        :description="__('All of your data will be permanently deleted. This cannot be undone.')"
    >
        @if (auth()->user()->hasPassword())
            <form wire:submit="deleteUser" class="space-y-5">
                <x-ui.input wire:model="password" icon="lock-closed" :label="__('Enter your password to confirm')" autocomplete="current-password" viewable />

                <div class="flex justify-end gap-2">
                    <x-ui.button variant="secondary" x-on:click="show = false">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="danger" loading="deleteUser">{{ __('Delete account') }}</x-ui.button>
                </div>
            </form>
        @else
            <x-ui.callout variant="warning">
                {{ __('Your account has no password yet. Set one first so you can confirm this action.') }}
                <a href="{{ route('settings.password') }}" wire:navigate>{{ __('Set a password') }}</a>
            </x-ui.callout>
        @endif
    </x-ui.modal>
</section>
