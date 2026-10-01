@php
    use App\Enums\SystemPermission;
    use App\Support\PermissionLabel;
@endphp

<div>
    <x-ui.page-header :eyebrow="__('Administration')" :title="__('Permissions')" :description="__('Name permissions like posts.publish and check them in code with can().')">
        <x-slot:actions>
            @can('create', \Spatie\Permission\Models\Permission::class)
                <x-ui.button icon="plus" wire:click="create">{{ __('Add permission') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <div class="relative mb-6 max-w-sm">
        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute start-3.5 top-1/2 size-4.5 -translate-y-1/2 text-zinc-400" />
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search permissions…') }}"
            aria-label="{{ __('Search permissions') }}"
            class="block h-11 w-full rounded-xl border-0 bg-white ps-10 pe-3 text-sm text-zinc-900 shadow-xs ring-1 ring-zinc-200 ring-inset placeholder:text-zinc-400 focus:ring-2 focus:ring-primary-500 focus:ring-inset dark:bg-white/[0.03] dark:text-white dark:ring-white/10 dark:focus:ring-primary-400"
        >
    </div>

    <div class="space-y-6">
        @forelse ($this->groups as $group => $permissions)
            <x-ui.card :padding="false" wire:key="group-{{ $group }}">
                <div class="flex items-center justify-between gap-4 border-b border-zinc-200 px-5 py-3 dark:border-white/10">
                    <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ PermissionLabel::groupLabel($group) }}</h2>
                    <x-ui.badge>{{ $permissions->count() }}</x-ui.badge>
                </div>

                <ul class="divide-y divide-zinc-100 dark:divide-white/5">
                    @foreach ($permissions as $permission)
                        <li class="flex flex-col gap-3 px-5 py-3 sm:flex-row sm:items-center" wire:key="permission-{{ $permission->id }}">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <code class="font-mono text-sm font-medium text-zinc-900 dark:text-white" dir="ltr">{{ $permission->name }}</code>

                                    @if (SystemPermission::isSystem($permission->name))
                                        <x-ui.badge>{{ __('Built-in') }}</x-ui.badge>
                                    @endif
                                </div>

                                <div class="mt-1.5 flex flex-wrap gap-1">
                                    @forelse ($permission->roles as $role)
                                        <x-ui.badge color="primary">{{ $role->name }}</x-ui.badge>
                                    @empty
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Not assigned to any role') }}</span>
                                    @endforelse
                                </div>
                            </div>

                            <div class="flex shrink-0 gap-1">
                                @can('update', $permission)
                                    <x-ui.button variant="ghost" size="sm" icon="pencil-square" wire:click="edit({{ $permission->id }})" :title="__('Edit')">
                                        <span class="sr-only">{{ __('Edit :name', ['name' => $permission->name]) }}</span>
                                    </x-ui.button>
                                @endcan

                                @can('delete', $permission)
                                    <x-ui.button variant="ghost" size="sm" icon="trash" wire:click="confirmDelete({{ $permission->id }})" :title="__('Delete')" class="text-red-600! dark:text-red-400!">
                                        <span class="sr-only">{{ __('Delete :name', ['name' => $permission->name]) }}</span>
                                    </x-ui.button>
                                @endcan
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        @empty
            <x-ui.card :padding="false">
                <x-ui.empty-state icon="key" :title="__('No permissions found')" :description="__('Try a different search.')" />
            </x-ui.card>
        @endforelse
    </div>

    <x-ui.modal wire:model="showModal" :title="$editingId ? __('Edit permission') : __('Add permission')">
        <form wire:submit="save" class="space-y-5">
            <x-ui.input
                wire:model="name"
                :label="__('Name')"
                :description="__('Lowercase, with a dot between the group and the action.')"
                placeholder="posts.publish"
                input-class="font-mono"
                dir="ltr"
                autocomplete="off"
                required
            />

            <div class="flex justify-end gap-2">
                <x-ui.button variant="secondary" x-on:click="show = false">{{ __('Cancel') }}</x-ui.button>
                <x-ui.button type="submit" loading="save">{{ __('Save') }}</x-ui.button>
            </div>
        </form>
    </x-ui.modal>
</div>
