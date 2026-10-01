{{--
    Ctrl/⌘ + K (or "/") opens a searchable list of pages and actions.
    Commands are built here so they respect the signed-in user's permissions.
--}}
@php
    use App\Enums\SocialProvider;
    use App\Support\Localization;

    $user = auth()->user();
    $icon = fn (string $name) => svg('heroicon-o-'.$name, 'size-4.5')->toHtml();
    $pages = __('Pages');
    $admin = __('Administration');
    $actions = __('Actions');

    $commands = [
        ['group' => $pages, 'label' => __('Dashboard'), 'icon' => $icon('home'), 'type' => 'link', 'value' => route('dashboard')],
        ['group' => $pages, 'label' => __('Profile'), 'icon' => $icon('user-circle'), 'type' => 'link', 'value' => route('settings.profile'), 'keywords' => 'settings account name email'],
    ];

    // Credentials are off limits while an admin is signed in as this user.
    $credentials = ! \App\Support\Impersonation::active();

    if ($credentials) {
        $commands[] = ['group' => $pages, 'label' => __('Password'), 'icon' => $icon('lock-closed'), 'type' => 'link', 'value' => route('settings.password'), 'keywords' => 'settings security'];
        $commands[] = ['group' => $pages, 'label' => __('Two-factor authentication'), 'icon' => $icon('finger-print'), 'type' => 'link', 'value' => route('settings.two-factor'), 'keywords' => '2fa totp security'];
    }

    if ($credentials && (SocialProvider::enabled() !== [] || $user->socialAccounts()->exists())) {
        $commands[] = ['group' => $pages, 'label' => __('Connected accounts'), 'icon' => $icon('link'), 'type' => 'link', 'value' => route('settings.connected-accounts'), 'keywords' => 'google facebook x social'];
    }

    $commands[] = ['group' => $pages, 'label' => __('Appearance'), 'icon' => $icon('paint-brush'), 'type' => 'link', 'value' => route('settings.appearance'), 'keywords' => 'theme dark light'];
    $commands[] = ['group' => $pages, 'label' => __('Language'), 'icon' => $icon('language'), 'type' => 'link', 'value' => route('settings.language'), 'keywords' => 'locale'];

    if ($user->can('admin.access')) {
        $commands[] = ['group' => $admin, 'label' => __('Admin dashboard'), 'icon' => $icon('squares-2x2'), 'type' => 'link', 'value' => route('admin.dashboard')];

        foreach ([
            ['users.view', __('Users'), 'users', 'admin.users.index'],
            ['users.create', __('Create user'), 'user-plus', 'admin.users.create'],
            ['roles.view', __('Roles'), 'identification', 'admin.roles.index'],
            ['roles.create', __('Create role'), 'plus', 'admin.roles.create'],
            ['permissions.view', __('Permissions'), 'key', 'admin.permissions.index'],
            ['activity.view', __('Activity log'), 'shield-check', 'admin.activity.index'],
            ['content.manage', __('Website content'), 'globe-alt', 'admin.website.settings'],
            ['content.manage', __('Website features'), 'squares-plus', 'admin.website.features'],
            ['content.manage', __('Website pricing'), 'banknotes', 'admin.website.plans'],
            ['content.manage', __('Website FAQ'), 'question-mark-circle', 'admin.website.faqs'],
        ] as [$permission, $label, $iconName, $route]) {
            if ($user->can($permission)) {
                $commands[] = ['group' => $admin, 'label' => $label, 'icon' => $icon($iconName), 'type' => 'link', 'value' => route($route)];
            }
        }
    }

    foreach (['light' => [__('Light'), 'sun'], 'dark' => [__('Dark'), 'moon'], 'system' => [__('System'), 'computer-desktop']] as $mode => [$label, $iconName]) {
        $commands[] = ['group' => $actions, 'label' => __('Theme').': '.$label, 'icon' => $icon($iconName), 'type' => 'theme', 'value' => $mode, 'keywords' => 'appearance mode'];
    }

    foreach (Localization::supported() as $code => $locale) {
        if ($code !== app()->getLocale()) {
            $commands[] = ['group' => $actions, 'label' => __('Language').': '.$locale['native'], 'icon' => $icon('language'), 'type' => 'locale', 'value' => $code, 'keywords' => $locale['name']];
        }
    }

    $commands[] = ['group' => $actions, 'label' => __('Collapse or expand the sidebar'), 'icon' => $icon('view-columns'), 'type' => 'sidebar', 'value' => null];
    $commands[] = ['group' => $actions, 'label' => __('Log out'), 'icon' => $icon('arrow-right-start-on-rectangle'), 'type' => 'logout', 'value' => null];

    foreach ($commands as $index => &$command) {
        $command['id'] = $index;
        $command['keywords'] ??= '';
    }
    unset($command);
@endphp

<div x-data="commandPalette(@js($commands))" x-on:keydown.window="shortcut($event)" x-on:open-command-palette.window="show()">
    <div x-cloak x-show="open" class="fixed inset-0 z-[70] overflow-y-auto p-4 sm:p-6 md:pt-[12vh]" role="dialog" aria-modal="true" aria-label="{{ __('Command palette') }}">
        <div x-show="open" x-transition.opacity.duration.150ms class="fixed inset-0 bg-zinc-950/40 backdrop-blur-sm" x-on:click="hide()"></div>

        <div
            x-show="open"
            x-trap.noscroll="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="glass relative mx-auto max-w-xl overflow-hidden rounded-3xl border border-zinc-200/80 shadow-2xl shadow-zinc-950/20 dark:border-white/10"
        >
            <div class="flex items-center gap-3 border-b border-zinc-200/80 px-5 dark:border-white/[0.07]">
                <x-heroicon-o-magnifying-glass class="size-5 shrink-0 text-zinc-400" />
                <input
                    x-ref="input"
                    x-model="query"
                    x-on:input="active = 0"
                    x-on:keydown.arrow-down.prevent="move(1)"
                    x-on:keydown.arrow-up.prevent="move(-1)"
                    x-on:keydown.enter.prevent="run()"
                    x-on:keydown.escape.prevent="hide()"
                    type="text"
                    class="h-14 w-full border-0 bg-transparent px-0 text-sm text-zinc-900 placeholder:text-zinc-400 focus:ring-0 dark:text-white"
                    placeholder="{{ __('Type a command or search…') }}"
                    aria-label="{{ __('Search commands') }}"
                    autocomplete="off"
                    spellcheck="false"
                >
                <kbd class="hidden rounded-md border border-zinc-200 px-1.5 py-0.5 font-mono text-[0.65rem] text-zinc-400 sm:block dark:border-white/10">ESC</kbd>
            </div>

            <div class="max-h-[22rem] overflow-y-auto p-2" x-ref="list">
                <template x-for="group in groups" x-bind:key="group.name">
                    <div class="pb-1">
                        <p class="px-3 pt-2 pb-1.5 text-[0.7rem] font-semibold tracking-[0.12em] text-zinc-400 uppercase" x-text="group.name"></p>

                        <template x-for="item in group.items" x-bind:key="item.id">
                            <button
                                type="button"
                                x-on:click="run(item)"
                                x-on:mousemove="active = item.index"
                                x-bind:data-active="item.index === active"
                                x-bind:class="item.index === active ? 'bg-primary-500/10 text-zinc-900 dark:bg-white/[0.08] dark:text-white' : 'text-zinc-600 dark:text-zinc-300'"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-start text-sm transition-colors"
                            >
                                <span
                                    class="flex size-8 shrink-0 items-center justify-center rounded-lg ring-1 transition-colors"
                                    x-bind:class="item.index === active ? 'bg-primary-500 text-white ring-primary-500' : 'bg-white text-zinc-500 ring-zinc-200 dark:bg-white/[0.04] dark:text-zinc-400 dark:ring-white/10'"
                                    x-html="item.icon"
                                ></span>
                                <span class="flex-1 truncate" x-text="item.label"></span>
                                <x-heroicon-o-arrow-turn-down-left class="size-4 text-zinc-400 rtl:-scale-x-100" x-show="item.index === active" />
                            </button>
                        </template>
                    </div>
                </template>

                <div x-show="results.length === 0" class="px-4 py-12 text-center">
                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ __('No results') }}</p>
                    <p class="mt-1 text-sm text-zinc-500">{{ __('Try searching for a page or an action.') }}</p>
                </div>
            </div>

            <div class="hidden items-center gap-4 border-t border-zinc-200/80 px-5 py-3 text-xs text-zinc-500 sm:flex dark:border-white/[0.07]">
                <span class="flex items-center gap-1.5"><kbd class="rounded border border-zinc-200 px-1 font-mono dark:border-white/10">↑</kbd><kbd class="rounded border border-zinc-200 px-1 font-mono dark:border-white/10">↓</kbd> {{ __('to navigate') }}</span>
                <span class="flex items-center gap-1.5"><kbd class="rounded border border-zinc-200 px-1 font-mono dark:border-white/10">↵</kbd> {{ __('to select') }}</span>
                <span class="ms-auto flex items-center gap-1.5"><kbd class="rounded border border-zinc-200 px-1 font-mono dark:border-white/10" x-text="shortcutLabel"></kbd> {{ __('to toggle') }}</span>
            </div>
        </div>
    </div>

    <form x-ref="logout" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>
    <form x-ref="locale" method="POST" action="{{ route('locale.update') }}" class="hidden">
        @csrf
        <input type="hidden" name="locale" x-ref="localeInput">
    </form>
</div>
