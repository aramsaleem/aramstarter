{{--
    The signed-in layout: a collapsible sidebar, a top bar with breadcrumbs and search,
    and the command palette (Ctrl/⌘ + K). Used by both the app and the admin panel.
--}}
@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\Support\Localization::direction() }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-canvas font-sans text-zinc-900 antialiased dark:bg-ink dark:text-zinc-100" x-data="{ sidebarOpen: false }">
        <x-aurora subtle :grid="false" class="fixed inset-x-0 top-0 -z-10 h-[36rem]" />

        {{-- Mobile backdrop --}}
        <div x-cloak x-show="sidebarOpen" x-transition.opacity x-on:click="sidebarOpen = false" class="fixed inset-0 z-40 bg-zinc-950/40 backdrop-blur-sm lg:hidden"></div>

        {{-- Sidebar --}}
        <aside
            x-bind:class="{ 'flex!': sidebarOpen }"
            x-on:keydown.escape.window="sidebarOpen = false"
            class="fixed inset-y-0 start-0 z-50 hidden w-72 flex-col border-e border-zinc-200/70 bg-canvas/90 backdrop-blur-xl transition-[width] duration-300 ease-out lg:flex lg:w-(--sidebar-width) dark:border-white/[0.06] dark:bg-ink/80"
        >
            <div class="flex h-16 shrink-0 items-center justify-between gap-2 px-5 sidebar-collapsed:lg:justify-center sidebar-collapsed:lg:px-0">
                <a href="{{ route('dashboard') }}" wire:navigate class="min-w-0">
                    <x-app-logo collapsible />
                </a>

                <button type="button" x-on:click="sidebarOpen = false" class="rounded-lg p-1.5 text-zinc-500 hover:bg-zinc-900/5 lg:hidden dark:hover:bg-white/10" aria-label="{{ __('Close navigation') }}">
                    <x-heroicon-o-x-mark class="size-5" />
                </button>
            </div>

            <nav class="flex flex-1 flex-col gap-1 overflow-x-hidden overflow-y-auto px-3 py-3" aria-label="{{ __('Main') }}">
                <p class="px-3 pb-2 text-[0.68rem] font-semibold tracking-[0.14em] text-zinc-400 uppercase sidebar-collapsed:lg:hidden dark:text-zinc-500">{{ __('Workspace') }}</p>

                <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" icon="home" collapsible>
                    {{ __('Dashboard') }}
                </x-nav-link>

                <x-nav-link :href="route('settings.profile')" :active="request()->routeIs('settings.*')" icon="cog-6-tooth" collapsible>
                    {{ __('Settings') }}
                </x-nav-link>

                @can('admin.access')
                    <div class="mx-3 my-3 hidden h-px bg-zinc-200 sidebar-collapsed:lg:block dark:bg-white/10"></div>

                    <p class="flex items-center justify-between px-3 pt-5 pb-2 text-[0.68rem] font-semibold tracking-[0.14em] text-zinc-400 uppercase sidebar-collapsed:lg:hidden dark:text-zinc-500">
                        {{ __('Administration') }}
                        <span class="rounded-full bg-linear-to-r from-primary-500 to-accent-500 px-1.5 py-px text-[0.6rem] tracking-normal text-white normal-case">{{ __('Admin') }}</span>
                    </p>

                    <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')" icon="squares-2x2" collapsible>
                        {{ __('Overview') }}
                    </x-nav-link>

                    @can('users.view')
                        <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')" icon="users" collapsible>
                            {{ __('Users') }}
                        </x-nav-link>
                    @endcan

                    @can('roles.view')
                        <x-nav-link :href="route('admin.roles.index')" :active="request()->routeIs('admin.roles.*')" icon="identification" collapsible>
                            {{ __('Roles') }}
                        </x-nav-link>
                    @endcan

                    @can('permissions.view')
                        <x-nav-link :href="route('admin.permissions.index')" :active="request()->routeIs('admin.permissions.*')" icon="key" collapsible>
                            {{ __('Permissions') }}
                        </x-nav-link>
                    @endcan

                    @can('activity.view')
                        <x-nav-link :href="route('admin.activity.index')" :active="request()->routeIs('admin.activity.*')" icon="shield-check" collapsible>
                            {{ __('Activity') }}
                        </x-nav-link>
                    @endcan

                    @can('content.manage')
                        <x-nav-link :href="route('admin.website.settings')" :active="request()->routeIs('admin.website.*')" icon="globe-alt" collapsible>
                            {{ __('Website') }}
                        </x-nav-link>
                    @endcan
                @endcan
            </nav>

            @unless (auth()->user()->hasEnabledTwoFactorAuthentication() || \App\Support\Impersonation::active())
                <div class="relative mx-3 mb-3 overflow-hidden rounded-2xl border border-primary-500/20 bg-linear-to-br from-primary-500/10 via-transparent to-accent-400/10 p-4 sidebar-collapsed:lg:hidden">
                    <div aria-hidden="true" class="absolute -end-6 -top-6 size-20 rounded-full bg-primary-500/20 blur-2xl"></div>
                    <x-heroicon-o-shield-exclamation class="relative size-5 text-primary-600 dark:text-primary-300" />
                    <p class="relative mt-2 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Protect your account') }}</p>
                    <p class="relative mt-1 text-xs text-zinc-600 dark:text-zinc-400">{{ __('Two-factor authentication is not enabled yet.') }}</p>
                    <a href="{{ route('settings.two-factor') }}" wire:navigate class="relative mt-3 inline-flex items-center gap-1 text-xs font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-300">
                        {{ __('Enable it now') }}
                        <x-heroicon-o-arrow-right class="size-3.5 rtl:-scale-x-100" />
                    </a>
                </div>
            @endunless

            <div class="space-y-1 border-t border-zinc-200/70 p-3 dark:border-white/[0.06]">
                <button
                    type="button"
                    x-on:click="window.sidebar.toggle()"
                    class="hidden h-10 w-full items-center gap-3 rounded-xl px-3 text-sm font-medium text-zinc-500 transition-colors hover:bg-zinc-900/[0.04] hover:text-zinc-900 lg:flex sidebar-collapsed:lg:justify-center sidebar-collapsed:lg:px-0 dark:text-zinc-400 dark:hover:bg-white/[0.04] dark:hover:text-white"
                    title="{{ __('Collapse or expand the sidebar') }}"
                >
                    <x-heroicon-o-chevron-double-left class="size-5 shrink-0 transition-transform duration-300 sidebar-collapsed:rotate-180 rtl:-scale-x-100" />
                    <span class="sidebar-collapsed:lg:hidden">{{ __('Collapse sidebar') }}</span>
                </button>

                <x-user-menu />
            </div>
        </aside>

        <div class="transition-[padding] duration-300 ease-out lg:ps-(--sidebar-width)">
            {{-- Top bar --}}
            <header class="glass sticky top-0 z-30 border-b border-zinc-200/70 dark:border-white/[0.06]">
                <div class="flex h-16 items-center gap-2 px-4 sm:gap-3 sm:px-6 lg:px-8">
                    <button type="button" x-on:click="sidebarOpen = true" class="-ms-1 rounded-lg p-2 text-zinc-600 hover:bg-zinc-900/5 lg:hidden dark:text-zinc-300 dark:hover:bg-white/10" aria-label="{{ __('Open navigation') }}">
                        <x-heroicon-o-bars-3 class="size-5" />
                    </button>

                    <x-breadcrumbs class="flex-1" />

                    <button
                        type="button"
                        x-on:click="$dispatch('open-command-palette')"
                        class="hidden h-9 w-56 items-center gap-2.5 rounded-xl bg-white px-3 text-sm text-zinc-400 shadow-xs ring-1 ring-zinc-200 transition hover:text-zinc-600 hover:ring-zinc-300 md:flex lg:w-72 dark:bg-white/[0.04] dark:ring-white/10 dark:hover:text-zinc-300 dark:hover:ring-white/20"
                    >
                        <x-heroicon-o-magnifying-glass class="size-4" />
                        <span class="flex-1 truncate text-start">{{ __('Search or jump to…') }}</span>
                        <kbd class="rounded-md border border-zinc-200 bg-zinc-50 px-1.5 font-mono text-[0.65rem] text-zinc-500 dark:border-white/10 dark:bg-white/5" x-text="/Mac|iPhone|iPad/.test(navigator.userAgent) ? '⌘K' : 'Ctrl K'">Ctrl K</kbd>
                    </button>

                    <button type="button" x-on:click="$dispatch('open-command-palette')" class="flex size-9 items-center justify-center rounded-xl text-zinc-600 hover:bg-zinc-900/5 md:hidden dark:text-zinc-300 dark:hover:bg-white/[0.07]" aria-label="{{ __('Search or jump to…') }}">
                        <x-heroicon-o-magnifying-glass class="size-4.5" />
                    </button>

                    <div class="flex items-center">
                        <x-locale-switcher />
                        <x-appearance-toggle />
                    </div>
                </div>
            </header>

            <main class="animate-fade-up mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                {{ $slot }}
            </main>
        </div>

        <x-command-palette />
        <x-impersonation-banner />
        <x-flash-alert />

        @livewireScripts
    </body>
</html>
