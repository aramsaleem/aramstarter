<div>
    <x-ui.page-header :eyebrow="__('Administration')" :title="__('Users')" :description="__('Manage user accounts and the roles they hold.')">
        <x-slot:actions>
            @can('create', \App\Models\User::class)
                <x-ui.button :href="route('admin.users.create')" icon="user-plus" wire:navigate>{{ __('Add user') }}</x-ui.button>
            @endcan
        </x-slot:actions>
    </x-ui.page-header>

    <x-ui.card :padding="false" class="overflow-hidden">
        {{-- Filters --}}
        <div class="flex flex-col gap-3 border-b border-zinc-200/80 p-4 sm:flex-row sm:items-center dark:border-white/[0.07]">
            <div class="group/search relative flex-1">
                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute start-3.5 top-1/2 size-4.5 -translate-y-1/2 text-zinc-400 group-focus-within/search:text-primary-500" />
                <input
                    type="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search by name or email…') }}"
                    aria-label="{{ __('Search users') }}"
                    class="block h-11 w-full rounded-xl border-0 bg-zinc-900/[0.03] ps-10 pe-3 text-sm text-zinc-900 ring-1 ring-transparent ring-inset placeholder:text-zinc-400 focus:bg-white focus:ring-2 focus:ring-primary-500 dark:bg-white/[0.04] dark:text-white dark:focus:bg-white/[0.06] dark:focus:ring-primary-400"
                >
            </div>

            <x-ui.select wire:model.live="role" class="sm:w-44" aria-label="{{ __('Filter by role') }}">
                <option value="">{{ __('All roles') }}</option>
                @foreach ($this->roles as $roleName)
                    <option value="{{ $roleName }}">{{ $roleName }}</option>
                @endforeach
            </x-ui.select>

            <x-ui.select wire:model.live="status" class="sm:w-48" aria-label="{{ __('Filter by status') }}">
                <option value="">{{ __('Any status') }}</option>
                <option value="verified">{{ __('Verified') }}</option>
                <option value="unverified">{{ __('Unverified') }}</option>
                <option value="two-factor">{{ __('Two-factor enabled') }}</option>
            </x-ui.select>
        </div>

        {{-- "relative" keeps the absolutely positioned sr-only labels inside the scroll area --}}
        <div class="relative overflow-x-auto transition-opacity" wire:loading.class="opacity-50" wire:target="search, role, status, sort, gotoPage, nextPage, previousPage">
            <table class="min-w-full text-sm">
                <thead class="text-xs tracking-wide text-zinc-500 dark:text-zinc-400">
                    <tr class="border-b border-zinc-200/80 dark:border-white/[0.07]">
                        <x-ui.sort-header column="name" :sort-by="$sortBy" :sort-direction="$sortDirection">{{ __('Name') }}</x-ui.sort-header>
                        <th scope="col" class="px-5 py-3 text-start font-medium uppercase">{{ __('Roles') }}</th>
                        <th scope="col" class="px-5 py-3 text-start font-medium uppercase">{{ __('Status') }}</th>
                        <x-ui.sort-header column="created_at" :sort-by="$sortBy" :sort-direction="$sortDirection">{{ __('Joined') }}</x-ui.sort-header>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">{{ __('Actions') }}</span></th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-100 dark:divide-white/[0.05]">
                    @forelse ($this->users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="group transition-colors hover:bg-zinc-900/[0.02] dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <x-ui.avatar :user="$user" size="sm" />

                                    <div class="min-w-0">
                                        <p class="flex items-center gap-2 truncate font-medium text-zinc-900 dark:text-white">
                                            {{ $user->name }}

                                            @if ($user->is(auth()->user()))
                                                <x-ui.badge color="primary">{{ __('You') }}</x-ui.badge>
                                            @endif
                                        </p>
                                        <p class="truncate text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($user->roles as $userRole)
                                        <x-ui.badge :color="$userRole->name === \App\Enums\SystemRole::SuperAdmin->value ? 'primary' : 'zinc'">{{ $userRole->name }}</x-ui.badge>
                                    @empty
                                        <span class="text-zinc-400">—</span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex flex-wrap gap-1">
                                    @if ($user->hasVerifiedEmail())
                                        <x-ui.badge color="green" dot>{{ __('Verified') }}</x-ui.badge>
                                    @else
                                        <x-ui.badge color="amber" dot>{{ __('Unverified') }}</x-ui.badge>
                                    @endif

                                    @if ($user->two_factor_confirmed_at)
                                        <x-ui.badge color="sky">
                                            <x-heroicon-o-finger-print class="size-3.5" />
                                            {{ __('2FA') }}
                                        </x-ui.badge>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-3.5 whitespace-nowrap text-zinc-500 dark:text-zinc-400" title="{{ $user->created_at->toDayDateTimeString() }}">
                                {{ $user->created_at->diffForHumans() }}
                            </td>

                            <td class="px-5 py-3.5">
                                <div class="flex justify-end gap-1 opacity-70 transition-opacity group-hover:opacity-100">
                                    @can('impersonate', $user)
                                        <x-ui.button variant="ghost" size="sm" icon="eye" wire:click="confirmImpersonate({{ $user->id }})" :title="__('Sign in as user')">
                                            <span class="sr-only">{{ __('Sign in as :name', ['name' => $user->name]) }}</span>
                                        </x-ui.button>
                                    @endcan

                                    @can('update', $user)
                                        <x-ui.button variant="ghost" size="sm" icon="pencil-square" :href="route('admin.users.edit', $user)" :title="__('Edit')" wire:navigate>
                                            <span class="sr-only">{{ __('Edit :name', ['name' => $user->name]) }}</span>
                                        </x-ui.button>
                                    @endcan

                                    @can('delete', $user)
                                        <x-ui.button variant="ghost" size="sm" icon="trash" wire:click="confirmDelete({{ $user->id }})" :title="__('Delete')" class="text-red-600! hover:bg-red-500/10! dark:text-red-400!">
                                            <span class="sr-only">{{ __('Delete :name', ['name' => $user->name]) }}</span>
                                        </x-ui.button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state icon="users" :title="__('No users found')" :description="__('Try a different search or clear the filters.')">
                                    <x-ui.button variant="secondary" size="sm" wire:click="resetFilters">{{ __('Clear filters') }}</x-ui.button>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->users->hasPages())
            <div class="border-t border-zinc-200/80 px-5 py-3 dark:border-white/[0.07]">
                {{ $this->users->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
