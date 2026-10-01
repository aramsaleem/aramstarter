{{-- Breadcrumbs for the top bar, worked out from the current route name. --}}
@php
    $admin = [__('Admin'), 'admin.dashboard'];
    $settings = [__('Settings'), 'settings.profile'];
    $users = [__('Users'), 'admin.users.index'];
    $roles = [__('Roles'), 'admin.roles.index'];
    $website = [__('Website'), 'admin.website.settings'];

    $trail = match (request()->route()?->getName()) {
        'dashboard' => [[__('Dashboard'), null]],
        'settings.profile' => [$settings, [__('Profile'), null]],
        'settings.password' => [$settings, [__('Password'), null]],
        'settings.two-factor' => [$settings, [__('Two-factor auth'), null]],
        'settings.connected-accounts' => [$settings, [__('Connected accounts'), null]],
        'settings.appearance' => [$settings, [__('Appearance'), null]],
        'settings.language' => [$settings, [__('Language'), null]],
        'admin.dashboard' => [$admin, [__('Overview'), null]],
        'admin.users.index' => [$admin, [__('Users'), null]],
        'admin.users.create' => [$admin, $users, [__('Create user'), null]],
        'admin.users.edit' => [$admin, $users, [__('Edit user'), null]],
        'admin.roles.index' => [$admin, [__('Roles'), null]],
        'admin.roles.create' => [$admin, $roles, [__('Create role'), null]],
        'admin.roles.edit' => [$admin, $roles, [__('Edit role'), null]],
        'admin.permissions.index' => [$admin, [__('Permissions'), null]],
        'admin.activity.index' => [$admin, [__('Activity'), null]],
        'admin.website.settings' => [$admin, $website, [__('Content'), null]],
        'admin.website.features' => [$admin, $website, [__('Features'), null]],
        'admin.website.plans' => [$admin, $website, [__('Pricing'), null]],
        'admin.website.faqs' => [$admin, $website, [__('FAQ'), null]],
        default => [],
    };
@endphp

<nav aria-label="{{ __('Breadcrumb') }}" {{ $attributes->class('min-w-0') }}>
    <ol class="flex min-w-0 items-center gap-1.5 text-sm">
        <li class="shrink-0">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex size-7 items-center justify-center rounded-lg text-zinc-400 transition-colors hover:bg-zinc-900/5 hover:text-zinc-700 dark:hover:bg-white/[0.06] dark:hover:text-zinc-200" aria-label="{{ __('Dashboard') }}">
                <x-heroicon-o-home class="size-4" />
            </a>
        </li>

        @foreach ($trail as [$label, $route])
            <li class="flex min-w-0 items-center gap-1.5 {{ $loop->last ? '' : 'hidden sm:flex' }}">
                <x-heroicon-o-chevron-right class="size-3.5 shrink-0 text-zinc-300 rtl:-scale-x-100 dark:text-zinc-600" />

                @if ($route && ! $loop->last)
                    <a href="{{ route($route) }}" wire:navigate class="truncate text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">{{ $label }}</a>
                @else
                    <span class="truncate font-medium text-zinc-900 dark:text-white" aria-current="page">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
