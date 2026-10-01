{{-- Shared by the create and edit screens. Expects $renameLocked. --}}
@php
    use App\Support\PermissionLabel;
@endphp

<form wire:submit="save" class="space-y-6">
    <x-ui.card>
        <x-ui.input
            wire:model="form.name"
            :label="__('Name')"
            :description="$renameLocked ? __('This role is used by the application, so its name cannot change.') : null"
            :disabled="$renameLocked"
            autocomplete="off"
            required
        />
    </x-ui.card>

    <x-ui.card :padding="false">
        <div class="border-b border-zinc-200 px-6 py-4 dark:border-white/10">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">{{ __('Permissions') }}</h2>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Choose what users with this role are allowed to do.') }}</p>
        </div>

        <div class="divide-y divide-zinc-100 dark:divide-white/5">
            @forelse ($this->permissionGroups as $group => $permissions)
                @php
                    $allSelected = array_diff($permissions->all(), $form->permissions) === [];
                @endphp

                <fieldset class="px-6 py-5" wire:key="group-{{ $group }}">
                    <div class="mb-4 flex items-center justify-between gap-4">
                        <legend class="text-sm font-semibold text-zinc-900 dark:text-white">{{ PermissionLabel::groupLabel($group) }}</legend>

                        <button type="button" wire:click="toggleGroup('{{ $group }}')" class="text-xs font-medium text-primary-600 hover:underline dark:text-primary-400">
                            {{ $allSelected ? __('Deselect all') : __('Select all') }}
                        </button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($permissions as $permission)
                            <x-ui.checkbox
                                wire:model.live="form.permissions"
                                wire:key="permission-{{ $permission }}"
                                value="{{ $permission }}"
                                :label="PermissionLabel::actionLabel($permission)"
                                :description="$permission"
                            />
                        @endforeach
                    </div>
                </fieldset>
            @empty
                <x-ui.empty-state icon="key" :title="__('No permissions yet')" />
            @endforelse
        </div>
    </x-ui.card>

    <x-ui.error for="form.permissions" />
    <x-ui.error for="form.permissions.*" />

    <div class="flex items-center justify-end gap-2">
        <x-ui.button variant="secondary" :href="route('admin.roles.index')" wire:navigate>{{ __('Cancel') }}</x-ui.button>
        <x-ui.button type="submit" loading="save">{{ __('Save role') }}</x-ui.button>
    </div>
</form>
