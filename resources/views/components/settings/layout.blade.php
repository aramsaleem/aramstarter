@props(['heading', 'subheading' => null])

@php
    // Credentials are off limits while an admin is signed in as this user.
    $credentials = ! \App\Support\Impersonation::active();
    $showConnectedAccounts = $credentials && (\App\Enums\SocialProvider::enabled() !== [] || auth()->user()->socialAccounts()->exists());

    $items = array_filter([
        ['route' => 'settings.profile', 'label' => __('Profile'), 'icon' => 'user-circle'],
        $credentials ? ['route' => 'settings.password', 'label' => __('Password'), 'icon' => 'lock-closed'] : null,
        $credentials ? ['route' => 'settings.two-factor', 'label' => __('Two-factor auth'), 'icon' => 'finger-print'] : null,
        $showConnectedAccounts ? ['route' => 'settings.connected-accounts', 'label' => __('Connected accounts'), 'icon' => 'link'] : null,
        ['route' => 'settings.appearance', 'label' => __('Appearance'), 'icon' => 'paint-brush'],
        ['route' => 'settings.language', 'label' => __('Language'), 'icon' => 'language'],
    ]);
@endphp

<div>
    <x-ui.page-header :eyebrow="__('Account')" :title="__('Settings')" :description="__('Manage your profile and account settings.')" />

    <div class="flex flex-col gap-6 lg:flex-row lg:gap-8">
        <nav class="-mx-1 flex gap-1 overflow-x-auto px-1 pb-1 lg:mx-0 lg:w-60 lg:shrink-0 lg:flex-col lg:overflow-visible lg:px-0 lg:pb-0" aria-label="{{ __('Settings') }}">
            @foreach ($items as $item)
                <x-nav-link :href="route($item['route'])" :active="request()->routeIs($item['route'])" :icon="$item['icon']" class="shrink-0">
                    {{ $item['label'] }}
                </x-nav-link>
            @endforeach
        </nav>

        <x-ui.card class="min-w-0 flex-1 p-6 sm:p-8">
            <div class="mb-8 border-b border-zinc-200/80 pb-6 dark:border-white/[0.07]">
                <h2 class="text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $heading }}</h2>

                @if ($subheading)
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $subheading }}</p>
                @endif
            </div>

            <div class="max-w-2xl">
                {{ $slot }}
            </div>
        </x-ui.card>
    </div>
</div>
