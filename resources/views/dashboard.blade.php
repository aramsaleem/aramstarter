@php
    $user = auth()->user();
    // Credentials are off limits while an admin is signed in as this user.
    $credentials = ! \App\Support\Impersonation::active();
    $connectedProviders = $user->socialAccounts()->get()->map(fn ($account) => $account->provider->label());

    $checks = [
        [
            'done' => $user->hasVerifiedEmail(),
            'icon' => 'envelope',
            'title' => __('Email address'),
            'text' => $user->email,
            'status' => $user->hasVerifiedEmail() ? __('Verified') : __('Unverified'),
            'href' => route('settings.profile'),
        ],
        [
            'done' => $user->hasPassword(),
            'icon' => 'lock-closed',
            'title' => __('Password'),
            'text' => $user->hasPassword() ? __('Use a long, random password to keep your account secure.') : __('You signed up with a social account, so your account has no password yet.'),
            'status' => $user->hasPassword() ? __('Set') : __('Not set'),
            'href' => $credentials ? route('settings.password') : null,
        ],
        [
            'done' => $user->hasEnabledTwoFactorAuthentication(),
            'icon' => 'finger-print',
            'title' => __('Two-factor authentication'),
            'text' => $user->hasEnabledTwoFactorAuthentication() ? __('You will be asked for a code from your authenticator app when you log in.') : __('Two-factor authentication is not enabled yet.'),
            'status' => $user->hasEnabledTwoFactorAuthentication() ? __('Enabled') : __('Disabled'),
            'href' => $credentials ? route('settings.two-factor') : null,
        ],
    ];

    $passed = collect($checks)->where('done', true)->count();
    $score = (int) round($passed / count($checks) * 100);

    // The fill carries the severity; the track is a lighter step of the same hue.
    [$fill, $track, $scoreText] = match (true) {
        $passed === count($checks) => ['bg-emerald-500', 'bg-emerald-500/15', 'text-emerald-600 dark:text-emerald-400'],
        $passed >= 2 => ['bg-amber-500', 'bg-amber-500/15', 'text-amber-600 dark:text-amber-400'],
        default => ['bg-red-500', 'bg-red-500/15', 'text-red-600 dark:text-red-400'],
    };
@endphp

<x-layouts.app :title="__('Dashboard')">
    {{-- Welcome banner --}}
    <div class="relative mb-8 overflow-hidden rounded-3xl bg-linear-to-br from-primary-100 via-white to-cyan-100 p-8 ring-1 ring-primary-100 sm:p-10 dark:from-primary-500/15 dark:via-transparent dark:to-cyan-500/10 dark:ring-white/10">
        <x-aurora class="absolute inset-0" />

        <div class="relative flex flex-col gap-8 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-primary-700 dark:text-primary-300">{{ now()->translatedFormat('l, j F') }}</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-zinc-950 sm:text-4xl dark:text-white">
                    {{ __('Welcome back, :name!', ['name' => \Illuminate\Support\Str::before($user->name, ' ')]) }}
                </h1>
                <p class="mt-2 text-zinc-600 dark:text-white/60">{{ __('Here is an overview of your account.') }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-ui.button :href="route('settings.profile')" variant="secondary" icon="user-circle" wire:navigate>
                    {{ __('Profile') }}
                </x-ui.button>

                @can('admin.access')
                    <x-ui.button :href="route('admin.dashboard')" icon="shield-check" wire:navigate>{{ __('Open admin panel') }}</x-ui.button>
                @endcan
            </div>
        </div>
    </div>

    @if (request()->boolean('verified'))
        <x-ui.callout variant="success" class="mb-6">{{ __('Thanks for verifying your email address!') }}</x-ui.callout>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Security --}}
        <x-ui.card :padding="false" class="lg:col-span-2">
            <div class="flex flex-col gap-6 border-b border-zinc-200/80 p-6 sm:flex-row sm:items-center sm:justify-between dark:border-white/[0.07]">
                <div>
                    <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Account security') }}</h2>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __(':passed of :total checks passed', ['passed' => $passed, 'total' => count($checks)]) }}</p>
                </div>

                <div class="w-full sm:w-56">
                    <div class="flex items-baseline justify-between">
                        <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Security score') }}</span>
                        <span class="text-2xl font-semibold tracking-tight {{ $scoreText }}">{{ $score }}%</span>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full {{ $track }}" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $score }}" aria-label="{{ __('Security score') }}">
                        <div class="h-full rounded-full {{ $fill }} transition-all duration-700" style="width: {{ $score }}%"></div>
                    </div>
                </div>
            </div>

            <ul class="divide-y divide-zinc-100 dark:divide-white/[0.05]">
                @foreach ($checks as $check)
                    <li class="flex items-center gap-4 px-6 py-4">
                        <span @class([
                            'flex size-10 shrink-0 items-center justify-center rounded-xl ring-1',
                            'bg-emerald-500/10 text-emerald-600 ring-emerald-500/20 dark:text-emerald-400' => $check['done'],
                            'bg-amber-500/10 text-amber-600 ring-amber-500/20 dark:text-amber-400' => ! $check['done'],
                        ])>
                            <x-dynamic-component :component="'heroicon-o-'.$check['icon']" class="size-5" />
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $check['title'] }}</p>
                            <p class="truncate text-sm text-zinc-500 dark:text-zinc-400">{{ $check['text'] }}</p>
                        </div>

                        <x-ui.badge :color="$check['done'] ? 'green' : 'amber'" dot class="hidden sm:inline-flex">{{ $check['status'] }}</x-ui.badge>

                        @if ($check['href'])
                            <x-ui.button :href="$check['href']" variant="ghost" size="sm" wire:navigate>
                                {{ __('Manage') }}
                                <x-heroicon-o-chevron-right class="size-3.5 rtl:-scale-x-100" />
                            </x-ui.button>
                        @endif
                    </li>
                @endforeach
            </ul>
        </x-ui.card>

        <div class="space-y-6">
            {{-- Profile --}}
            <x-ui.card>
                <div class="flex items-center gap-4">
                    <x-ui.avatar :user="$user" size="lg" class="ring-4 ring-primary-500/10" />

                    <div class="min-w-0">
                        <p class="truncate font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Member since :date', ['date' => $user->created_at->translatedFormat('F Y')]) }}</p>
                    </div>
                </div>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Roles') }}</dt>
                        <dd class="flex flex-wrap justify-end gap-1">
                            @forelse ($user->getRoleNames() as $role)
                                <x-ui.badge color="primary">{{ $role }}</x-ui.badge>
                            @empty
                                <x-ui.badge>{{ __('No role') }}</x-ui.badge>
                            @endforelse
                        </dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Language') }}</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">{{ \App\Support\Localization::nativeName() }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-zinc-500 dark:text-zinc-400">{{ __('Connected accounts') }}</dt>
                        <dd class="font-medium text-zinc-900 dark:text-white">{{ $connectedProviders->isEmpty() ? __('None') : $connectedProviders->join(', ') }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            {{-- Shortcut tip --}}
            <button type="button" x-data x-on:click="$dispatch('open-command-palette')" class="group relative w-full overflow-hidden rounded-2xl border border-dashed border-primary-500/30 bg-primary-500/[0.04] p-5 text-start transition hover:border-primary-500/60 hover:bg-primary-500/[0.07]">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-primary-500/15 text-primary-600 dark:text-primary-300">
                        <x-heroicon-o-command-line class="size-5" />
                    </span>
                    <div>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Command palette') }}</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Jump to any page or action with Ctrl + K.') }}</p>
                    </div>
                </div>
            </button>
        </div>
    </div>
</x-layouts.app>
