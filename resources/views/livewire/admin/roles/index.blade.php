@php
    use App\Enums\SystemRole;
@endphp

<div>
    <x-ui.page-header :eyebrow="__('Administration')" :title="__('Roles')" :description="__('Bundle permissions into roles, then give roles to users.')">
        <x-slot:actions>
            @can('create', \Spatie\Permission\Models\Role::class)
                <x-ui.button :href="route('admin.roles.create')" icon="plus" wire:navigate>{{ __('Add role') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @forelse ($this->roles as $role)
            @php
                $isSuperAdmin = $role->name === SystemRole::SuperAdmin->value;
            @endphp

            <x-ui.card class="flex flex-col transition duration-300 hover:-translate-y-0.5 hover:shadow-xl hover:shadow-primary-900/5" wire:key="role-{{ $role->id }}">
                <div class="flex items-start gap-3">
                    <div @class([
                        'flex size-10 shrink-0 items-center justify-center rounded-lg',
                        'bg-linear-to-br from-primary-500 to-primary-700 text-white shadow-lg shadow-primary-600/30 ring-1 ring-white/20' => $isSuperAdmin,
                        'bg-primary-500/10 text-primary-600 ring-1 ring-primary-500/15 dark:text-primary-300' => ! $isSuperAdmin,
                    ])>
                        <x-dynamic-component :component="$isSuperAdmin ? 'heroicon-o-shield-check' : 'heroicon-o-identification'" class="size-5" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <h2 class="truncate font-semibold text-zinc-900 dark:text-white">{{ $role->name }}</h2>

                        @if (SystemRole::isProtectedName($role->name))
                            <x-ui.badge class="mt-1">{{ __('Built-in') }}</x-ui.badge>
                        @endif
                    </div>
                </div>

                <dl class="my-5 grid grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Users') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">{{ number_format($role->users_count) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Permissions') }}</dt>
                        <dd class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $isSuperAdmin ? __('All') : number_format($role->permissions_count) }}
                        </dd>
                    </div>
                </dl>

                <div class="mt-auto flex min-h-9 items-center gap-2 border-t border-zinc-200 pt-4 dark:border-white/10">
                    @if ($isSuperAdmin)
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Super Admins pass every permission check automatically.') }}</p>
                    @else
                        @can('update', $role)
                            <x-ui.button variant="secondary" size="sm" icon="pencil-square" :href="route('admin.roles.edit', $role)" wire:navigate>
                                {{ __('Edit') }}
                            </x-ui.button>
                        @endcan

                        @can('delete', $role)
                            <x-ui.button variant="ghost" size="sm" icon="trash" wire:click="confirmDelete({{ $role->id }})" class="text-red-600! dark:text-red-400!">
                                {{ __('Delete') }}
                            </x-ui.button>
                        @endcan
                    @endif
                </div>
            </x-ui.card>
        @empty
            <x-ui.card :padding="false" class="md:col-span-2 xl:col-span-3">
                <x-ui.empty-state icon="identification" :title="__('No roles yet')" :description="__('Run the database seeder to create the default roles.')" />
            </x-ui.card>
        @endforelse
    </div>
</div>
