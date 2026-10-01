@php
    $user = auth()->user();
@endphp

<x-ui.dropdown position="top" align="start" width="w-60">
    <x-slot:trigger class="flex w-full items-center gap-3 rounded-xl p-2 text-start transition-colors hover:bg-zinc-900/[0.04] sidebar-collapsed:lg:justify-center dark:hover:bg-white/[0.05]" title="{{ $user->name }}">
        <span class="relative">
            <x-ui.avatar :user="$user" size="sm" class="ring-2 ring-white dark:ring-zinc-900" />
            <span class="absolute -end-0.5 -bottom-0.5 size-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-zinc-950"></span>
        </span>

        <span class="min-w-0 flex-1 sidebar-collapsed:lg:hidden">
            <span class="block truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $user->name }}</span>
            <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</span>
        </span>

        <x-heroicon-o-chevron-up-down class="size-4 shrink-0 text-zinc-400 sidebar-collapsed:lg:hidden" />
    </x-slot:trigger>

    <div class="px-3 pt-2 pb-2.5">
        <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $user->name }}</p>
        <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $user->email }}</p>
    </div>

    <div class="my-1 h-px bg-zinc-200/80 dark:bg-white/[0.07]"></div>

    <x-ui.dropdown-item :href="route('settings.profile')" icon="user-circle" wire:navigate>{{ __('Profile') }}</x-ui.dropdown-item>
    @unless (\App\Support\Impersonation::active())
        <x-ui.dropdown-item :href="route('settings.two-factor')" icon="finger-print" wire:navigate>{{ __('Two-factor auth') }}</x-ui.dropdown-item>
    @endunless

    @can('admin.access')
        <x-ui.dropdown-item :href="route('admin.dashboard')" icon="shield-check" wire:navigate>{{ __('Admin panel') }}</x-ui.dropdown-item>
    @endcan

    <div class="my-1 h-px bg-zinc-200/80 dark:bg-white/[0.07]"></div>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <x-ui.dropdown-item type="submit" icon="arrow-right-start-on-rectangle">{{ __('Log out') }}</x-ui.dropdown-item>
    </form>
</x-ui.dropdown>
