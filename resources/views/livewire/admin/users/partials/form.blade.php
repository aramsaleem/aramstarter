{{-- Shared by the create and edit screens. Expects $editing and $assignableRoles. --}}
<form wire:submit="save" class="space-y-6">
    <x-ui.card class="space-y-6">
        <div>
            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">{{ __('Account') }}</h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Basic details and login credentials.') }}</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <x-ui.input wire:model="form.name" :label="__('Name')" autocomplete="off" required />

            <x-ui.input wire:model="form.email" type="email" :label="__('Email address')" autocomplete="off" required />

            <x-ui.input
                wire:model="form.password"
                :label="$editing ? __('New password') : __('Password')"
                :description="$editing ? __('Leave blank to keep the current password.') : null"
                autocomplete="new-password"
                viewable
                :required="! $editing"
            />

            <x-ui.input wire:model="form.password_confirmation" :label="__('Confirm password')" autocomplete="new-password" viewable />

            <x-ui.select wire:model="form.locale" :label="__('Language')">
                <option value="">{{ __('Automatic (browser language)') }}</option>
                @foreach (\App\Support\Localization::supported() as $code => $locale)
                    <option value="{{ $code }}">{{ $locale['native'] }}</option>
                @endforeach
            </x-ui.select>

            <div class="flex items-end pb-2">
                <x-ui.checkbox
                    wire:model="form.verified"
                    :label="__('Email address verified')"
                    :description="__('Unverified users cannot reach the dashboard until they confirm their email.')"
                />
            </div>
        </div>
    </x-ui.card>

    <x-ui.card class="space-y-6">
        <div>
            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">{{ __('Roles') }}</h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Roles decide what the user can see and do.') }}</p>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ($assignableRoles as $roleName)
                <label
                    wire:key="role-option-{{ $loop->index }}"
                    class="flex cursor-pointer items-center gap-3 rounded-xl border border-zinc-200 p-3.5 transition hover:border-zinc-300 has-checked:border-primary-500 has-checked:bg-primary-500/[0.05] has-checked:ring-4 has-checked:ring-primary-500/10 dark:border-white/10 dark:hover:border-white/20 dark:has-checked:border-primary-400"
                >
                    <input type="checkbox" wire:model="form.roles" value="{{ $roleName }}" class="size-4.5 rounded-md border-zinc-300 text-primary-600 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5">
                    <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $roleName }}</span>

                    @if ($roleName === \App\Enums\SystemRole::SuperAdmin->value)
                        <x-ui.badge color="primary" class="ms-auto">{{ __('Every permission') }}</x-ui.badge>
                    @endif
                </label>
            @endforeach
        </div>

        <x-ui.error for="form.roles" />
        <x-ui.error for="form.roles.*" />
    </x-ui.card>

    <div class="flex items-center justify-end gap-2">
        <x-ui.button variant="secondary" :href="route('admin.users.index')" wire:navigate>{{ __('Cancel') }}</x-ui.button>
        <x-ui.button type="submit" loading="save">{{ $editing ? __('Save changes') : __('Create user') }}</x-ui.button>
    </div>
</form>
