@php
    use App\Enums\SocialProvider;
    use App\Enums\SystemRole;
    use Illuminate\Support\Number;
    use Illuminate\Support\Str;

    $stats = $this->stats;
    $series = $this->series;
    $days = $this->days();
    $user = auth()->user();

    $compact = fn (int $value) => $value >= 10_000 ? Number::abbreviate($value, maxPrecision: 1) : number_format($value);
    $percentOf = fn (int $part, int $whole) => $whole > 0 ? (int) round($part / $whole * 100) : 0;

    // Change against the previous period; null when there is nothing to compare with.
    $delta = fn (int $current, int $previous) => $previous > 0 ? (int) round(($current - $previous) / $previous * 100) : ($current > 0 ? null : 0);

    // A clean top value for a chart axis: 5, 10, 25, 50, 200, ...
    $niceMax = function (int $peak): int {
        $step = $peak < 10 ? 5 : 10 ** (strlen((string) $peak) - 1) / 2;

        return (int) max(5, ceil($peak / $step) * $step);
    };

    // Sparkline points in a 100 x 32 box.
    $sparkline = function (array $values): array {
        $max = max(1, ...$values);
        $count = max(1, count($values) - 1);
        $points = [];

        foreach ($values as $index => $value) {
            $points[] = round($index / $count * 100, 2).','.round(30 - $value / $max * 26, 2);
        }

        return ['line' => implode(' ', $points), 'area' => '0,32 '.implode(' ', $points).' 100,32'];
    };

    $columnLabel = fn (array $column) => $column['from']->isSameDay($column['to'])
        ? $column['from']->translatedFormat('j M')
        : $column['from']->translatedFormat('j M').' – '.$column['to']->translatedFormat('j M');

    $metrics = [
        'signups' => [__('Sign-ups'), 'bg-primary-600 dark:bg-primary-400', 'group-hover:bg-primary-500 dark:group-hover:bg-primary-300'],
        'sign_ins' => [__('Sign-ins'), 'bg-cyan-600 dark:bg-cyan-400', 'group-hover:bg-cyan-500 dark:group-hover:bg-cyan-300'],
        'threats' => [__('Threats'), 'bg-red-600 dark:bg-red-400', 'group-hover:bg-red-500 dark:group-hover:bg-red-300'],
    ];

    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? __('Good morning') : ($hour < 18 ? __('Good afternoon') : __('Good evening'));

    $twoFactorShare = $percentOf($stats['two_factor'], $stats['users']);
    $verifiedShare = $percentOf($stats['verified'], $stats['users']);
    $signInLine = $sparkline(array_column($series, 'sign_ins'));

    $kpis = [
        [
            'label' => __('New users'), 'icon' => 'user-plus', 'value' => $compact($stats['new']),
            'delta' => $delta($stats['new'], $stats['new_previous']), 'goodWhenUp' => true,
            'hint' => __(':count users in total', ['count' => number_format($stats['users'])]),
            'chart' => 'bars', 'values' => array_column($series, 'signups'), 'color' => 'bg-primary-500 dark:bg-primary-400',
        ],
        [
            'label' => __('Sign-ins'), 'icon' => 'arrow-right-end-on-rectangle', 'value' => $compact($stats['sign_ins']),
            'delta' => $delta($stats['sign_ins'], $stats['sign_ins_previous']), 'goodWhenUp' => true,
            'hint' => $stats['online'] === null ? null : __(':count online now', ['count' => number_format($stats['online'])]),
            'chart' => 'line',
        ],
        [
            'label' => __('Two-factor adoption'), 'icon' => 'finger-print', 'value' => $twoFactorShare.'%',
            'delta' => false, 'goodWhenUp' => true,
            'hint' => __(':percent% verified their email', ['percent' => $verifiedShare]),
            'chart' => 'ring',
        ],
        [
            'label' => __('Blocked threats'), 'icon' => 'shield-exclamation', 'value' => $compact($stats['threats']),
            'delta' => $delta($stats['threats'], $stats['threats_previous']), 'goodWhenUp' => false,
            'hint' => __(':count in the last 24 hours', ['count' => number_format($stats['threats_today'])]),
            'chart' => 'bars', 'values' => array_column($series, 'threats'), 'color' => 'bg-red-500 dark:bg-red-400',
        ],
    ];

    $health = $this->health;
    $healthScore = count(array_filter(array_column($health, 'ok')));
    $roleMax = max(1, (int) $this->roles->max('users_count'));
@endphp

<div>
    {{-- ============================ Header ============================ --}}
    <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="min-w-0">
            <p class="mb-2 text-xs font-semibold tracking-[0.14em] text-primary-600 uppercase dark:text-primary-400">{{ now()->translatedFormat('l j F Y') }}</p>
            <h1 class="text-2xl font-semibold tracking-tight text-balance text-zinc-900 sm:text-3xl dark:text-white">{{ $greeting }}, {{ Str::before($user->name, ' ') }}</h1>
            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Here is what happened in the last :days days.', ['days' => $days]) }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-xl border border-zinc-200/80 bg-white p-1 shadow-xs dark:border-white/10 dark:bg-white/[0.03]" role="group" aria-label="{{ __('Period') }}">
                @foreach (array_keys(\App\Livewire\Admin\Dashboard::PERIODS) as $option)
                    <button type="button" wire:click="$set('period', '{{ $option }}')" aria-pressed="{{ $days === $option ? 'true' : 'false' }}" @class([
                        'h-8 rounded-lg px-3 text-xs font-medium whitespace-nowrap transition',
                        'bg-zinc-900 text-white shadow-sm dark:bg-white dark:text-zinc-900' => $days === $option,
                        'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $days !== $option,
                    ])>{{ __(':count days', ['count' => $option]) }}</button>
                @endforeach
            </div>

            @can('users.create')
                <x-ui.button :href="route('admin.users.create')" icon="user-plus" wire:navigate>{{ __('Add user') }}</x-ui.button>
            @endcan
        </div>
    </div>

    <div class="space-y-4" wire:loading.class="opacity-60" wire:target="period">
        {{-- ============================ KPI cards ============================ --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($kpis as $kpi)
                <x-ui.card class="flex flex-col overflow-hidden p-5!">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $kpi['label'] }}</p>
                        <span class="flex size-8 items-center justify-center rounded-lg bg-zinc-900/[0.04] text-zinc-500 dark:bg-white/[0.06] dark:text-zinc-300">
                            <x-dynamic-component :component="'heroicon-o-'.$kpi['icon']" class="size-4" />
                        </span>
                    </div>

                    <div class="mt-3 flex items-end justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ $kpi['value'] }}</p>

                                @if ($kpi['delta'] === null)
                                    <x-ui.badge color="sky">{{ __('New') }}</x-ui.badge>
                                @elseif ($kpi['delta'] !== false)
                                    @php $good = $kpi['delta'] === 0 ? null : ($kpi['delta'] > 0) === $kpi['goodWhenUp']; @endphp
                                    <span @class([
                                        'inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 text-xs font-semibold',
                                        'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' => $good === true,
                                        'bg-red-500/10 text-red-700 dark:text-red-400' => $good === false,
                                        'bg-zinc-900/[0.04] text-zinc-500 dark:bg-white/[0.06] dark:text-zinc-400' => $good === null,
                                    ]) title="{{ __('Compared with the previous :days days', ['days' => $days]) }}">
                                        @if ($kpi['delta'] > 0)
                                            <x-heroicon-m-arrow-trending-up class="size-3.5" />
                                        @elseif ($kpi['delta'] < 0)
                                            <x-heroicon-m-arrow-trending-down class="size-3.5" />
                                        @endif
                                        <span dir="ltr">{{ $kpi['delta'] > 0 ? '+' : '' }}{{ $kpi['delta'] }}%</span>
                                    </span>
                                @endif
                            </div>

                            @if ($kpi['hint'])
                                <p class="mt-1 flex items-center gap-1.5 truncate text-xs text-zinc-500 dark:text-zinc-400">
                                    @if ($kpi['chart'] === 'line')
                                        <span class="relative flex size-1.5"><span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-75"></span><span class="relative inline-flex size-1.5 rounded-full bg-emerald-500"></span></span>
                                    @endif
                                    {{ $kpi['hint'] }}
                                </p>
                            @endif
                        </div>

                        @if ($kpi['chart'] === 'ring')
                            <svg viewBox="0 0 36 36" class="size-14 shrink-0 -rotate-90" role="img" aria-label="{{ $kpi['label'] }}: {{ $kpi['value'] }}">
                                <circle cx="18" cy="18" r="15.9155" fill="none" stroke-width="4" class="stroke-primary-500/15" />
                                @if ($twoFactorShare > 0)
                                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke-width="4" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ $twoFactorShare }} 100" class="stroke-primary-600 dark:stroke-primary-400" />
                                @endif
                            </svg>
                        @endif
                    </div>

                    @if ($kpi['chart'] === 'bars')
                        @php $peak = max(1, ...$kpi['values']); @endphp
                        <div class="mt-4 flex h-10 items-end gap-[2px]" aria-hidden="true">
                            @foreach ($kpi['values'] as $value)
                                <span class="min-h-[2px] flex-1 rounded-t-[2px] {{ $value > 0 ? $kpi['color'] : 'bg-zinc-200 dark:bg-white/10' }}" style="height: {{ $value > 0 ? max(8, $value / $peak * 100) : 5 }}%"></span>
                            @endforeach
                        </div>
                    @elseif ($kpi['chart'] === 'line')
                        <svg viewBox="0 0 100 32" preserveAspectRatio="none" class="mt-4 h-10 w-full overflow-visible" aria-hidden="true">
                            <defs>
                                <linearGradient id="kpi-sign-ins" x1="0" x2="0" y1="0" y2="1">
                                    <stop offset="0%" stop-color="rgb(6 182 212)" stop-opacity="0.3" />
                                    <stop offset="100%" stop-color="rgb(6 182 212)" stop-opacity="0" />
                                </linearGradient>
                            </defs>
                            <polygon points="{{ $signInLine['area'] }}" fill="url(#kpi-sign-ins)" />
                            <polyline points="{{ $signInLine['line'] }}" fill="none" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" class="stroke-cyan-600 dark:stroke-cyan-400" />
                        </svg>
                    @else
                        <div class="mt-4 h-2 overflow-hidden rounded-full bg-emerald-500/15" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $verifiedShare }}" aria-label="{{ __('Verified email') }}">
                            <div class="h-full rounded-full bg-emerald-600 dark:bg-emerald-400" style="width: {{ $verifiedShare }}%"></div>
                        </div>
                    @endif
                </x-ui.card>
            @endforeach
        </div>

        {{-- ============================ Trend chart + roles ============================ --}}
        <div class="grid gap-4 lg:grid-cols-3">
            <x-ui.card class="lg:col-span-2">
                <div x-data="{ metric: 'signups', view: 'chart' }">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Trends') }}</h2>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $days > 30 ? __('Per week, last :days days', ['days' => $days]) : __('Per day, last :days days', ['days' => $days]) }}
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <div class="flex rounded-xl bg-zinc-900/[0.04] p-1 text-xs font-medium dark:bg-white/[0.05]" role="group" aria-label="{{ __('Metric') }}">
                                @foreach ($metrics as $key => [$label])
                                    <button type="button" x-on:click="metric = '{{ $key }}'" x-bind:aria-pressed="metric === '{{ $key }}'"
                                        x-bind:class="metric === '{{ $key }}' ? 'bg-white text-zinc-900 shadow-sm dark:bg-white/10 dark:text-white' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                        class="rounded-lg px-3 py-1.5 transition-colors">{{ $label }}</button>
                                @endforeach
                            </div>

                            <div class="flex rounded-xl bg-zinc-900/[0.04] p-1 text-xs font-medium dark:bg-white/[0.05]" role="group" aria-label="{{ __('Display as') }}">
                                @foreach (['chart' => ['chart-bar', __('Chart')], 'table' => ['table-cells', __('Table')]] as $view => [$icon, $label])
                                    <button type="button" x-on:click="view = '{{ $view }}'" x-bind:aria-pressed="view === '{{ $view }}'" title="{{ $label }}"
                                        x-bind:class="view === '{{ $view }}' ? 'bg-white text-zinc-900 shadow-sm dark:bg-white/10 dark:text-white' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'"
                                        class="rounded-lg px-2 py-1.5 transition-colors">
                                        <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-4" />
                                        <span class="sr-only">{{ $label }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @foreach ($metrics as $key => [$label, $barClass, $hoverClass])
                        @php
                            $totals = array_column($series, $key);
                            $peak = max($totals);
                            $scaleMax = $niceMax($peak);
                            $peakIndex = $peak > 0 ? array_search($peak, $totals, true) : null;
                        @endphp

                        <div x-show="metric === '{{ $key }}'" @if ($key !== 'signups') x-cloak @endif wire:key="trend-{{ $key }}-{{ $days }}">
                            <p class="mt-5 text-3xl font-semibold tracking-tight text-zinc-900 dark:text-white">
                                {{ number_format(array_sum($totals)) }}
                                <span class="text-sm font-normal text-zinc-500 dark:text-zinc-400">{{ $label }}</span>
                            </p>

                            {{-- Top margin leaves room for the tooltip above the tallest column --}}
                            <div x-show="view === 'chart'" class="mt-10">
                                <div class="flex gap-3">
                                    <div class="flex h-52 w-8 shrink-0 flex-col justify-between text-end text-xs text-zinc-400 tabular-nums dark:text-zinc-500" aria-hidden="true">
                                        <span class="-translate-y-1/2">{{ number_format($scaleMax) }}</span>
                                        <span>{{ number_format($scaleMax / 2) }}</span>
                                        <span class="translate-y-1/2">0</span>
                                    </div>

                                    <div class="relative h-52 flex-1">
                                        <div class="absolute inset-x-0 top-0 border-t border-zinc-100 dark:border-white/[0.05]"></div>
                                        <div class="absolute inset-x-0 top-1/2 border-t border-zinc-100 dark:border-white/[0.05]"></div>
                                        <div class="absolute inset-x-0 bottom-0 border-t border-zinc-200 dark:border-white/10"></div>

                                        <ol class="absolute inset-0 flex items-end gap-1" aria-label="{{ $label }}">
                                            @foreach ($series as $index => $column)
                                                @php $height = $column[$key] / $scaleMax * 100; @endphp
                                                <li class="group relative flex h-full flex-1 cursor-default items-end justify-center outline-none" tabindex="0" aria-label="{{ $columnLabel($column) }}: {{ $column[$key] }}">
                                                    <span class="w-full max-w-6 rounded-t-[4px] transition-colors {{ $barClass }} {{ $hoverClass }}" style="height: {{ $height }}%"></span>

                                                    @if ($index === $peakIndex)
                                                        <span class="absolute text-xs font-medium text-zinc-700 group-hover:invisible group-focus-visible:invisible dark:text-zinc-300" style="bottom: calc({{ $height }}% + 0.25rem)">{{ number_format($peak) }}</span>
                                                    @endif

                                                    <span class="pointer-events-none absolute left-1/2 z-10 hidden -translate-x-1/2 rounded-xl border border-white/10 bg-zinc-900 px-3 py-2 text-center whitespace-nowrap shadow-xl group-hover:block group-focus-visible:block" style="bottom: calc({{ $height }}% + 0.5rem)" role="tooltip">
                                                        <span class="block text-sm font-semibold text-white">{{ number_format($column[$key]) }}</span>
                                                        <span class="block text-xs text-zinc-400">{{ $columnLabel($column) }}</span>
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ol>
                                    </div>
                                </div>

                                <div class="ms-11 mt-2 flex justify-between text-xs text-zinc-400 dark:text-zinc-500" aria-hidden="true">
                                    <span>{{ $series[0]['from']->translatedFormat('j M') }}</span>
                                    <span>{{ $series[array_key_last($series)]['to']->translatedFormat('j M') }}</span>
                                </div>
                            </div>

                            <div x-show="view === 'table'" x-cloak class="mt-6 max-h-72 overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead class="text-xs text-zinc-500 uppercase dark:text-zinc-400">
                                        <tr>
                                            <th scope="col" class="pb-2 text-start font-medium">{{ __('Date') }}</th>
                                            <th scope="col" class="pb-2 text-end font-medium">{{ $label }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-zinc-100 dark:divide-white/[0.05]">
                                        @foreach (array_reverse($series) as $column)
                                            <tr>
                                                <td class="py-2.5 text-zinc-700 dark:text-zinc-300">{{ $columnLabel($column) }}</td>
                                                <td class="py-2.5 text-end font-medium text-zinc-900 tabular-nums dark:text-white">{{ number_format($column[$key]) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>

            {{-- Users by role: one hue for every role - the length carries the value. --}}
            <x-ui.card class="flex flex-col">
                <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Users by role') }}</h2>
                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('How many users hold each role.') }}</p>

                @if ($this->roles->isEmpty())
                    <x-ui.empty-state icon="identification" :title="__('No roles yet')" class="py-8!" />
                @else
                    <ul class="mt-6 space-y-5">
                        @foreach ($this->roles as $role)
                            <li wire:key="role-{{ $role->id }}">
                                <div class="flex items-baseline justify-between gap-3 text-sm">
                                    <span class="truncate font-medium text-zinc-700 dark:text-zinc-300">{{ $role->name }}</span>
                                    <span class="text-zinc-500 tabular-nums dark:text-zinc-400">{{ number_format($role->users_count) }}</span>
                                </div>
                                <div class="mt-2 h-2 rounded-e bg-zinc-100 dark:bg-white/[0.05]">
                                    <div class="h-2 rounded-e bg-primary-600 dark:bg-primary-400" style="width: {{ $role->users_count / $roleMax * 100 }}%"></div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @can('roles.view')
                    <div class="mt-auto pt-6">
                        <x-ui.button :href="route('admin.roles.index')" variant="secondary" size="sm" class="w-full" wire:navigate>{{ __('Manage roles') }}</x-ui.button>
                    </div>
                @endcan
            </x-ui.card>
        </div>

        {{-- ============================ People: list + detail ============================ --}}
        @can('users.view')
            @php $selected = $this->selectedUser; @endphp

            <div class="grid gap-4 lg:grid-cols-5">
                <x-ui.card :padding="false" class="lg:col-span-3">
                    <div class="flex flex-col gap-3 border-b border-zinc-200/80 p-5 sm:flex-row sm:items-center dark:border-white/[0.07]">
                        <div class="flex-1">
                            <h2 id="people-title" class="font-semibold text-zinc-900 dark:text-white">{{ __('People') }}</h2>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Pick someone to see their details.') }}</p>
                        </div>

                        <div class="flex gap-2">
                            <div class="group/search relative flex-1 sm:w-48">
                                <x-heroicon-o-magnifying-glass class="pointer-events-none absolute start-3 top-1/2 size-4 -translate-y-1/2 text-zinc-400 group-focus-within/search:text-primary-500" />
                                <input type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search…') }}" aria-label="{{ __('Search users') }}"
                                    class="block h-10 w-full rounded-xl border-0 bg-zinc-900/[0.03] ps-9 pe-3 text-sm text-zinc-900 ring-1 ring-transparent ring-inset placeholder:text-zinc-400 focus:bg-white focus:ring-2 focus:ring-primary-500 dark:bg-white/[0.04] dark:text-white dark:focus:ring-primary-400">
                            </div>
                            <x-ui.select wire:model.live="role" class="w-36" aria-label="{{ __('Filter by role') }}">
                                <option value="">{{ __('All roles') }}</option>
                                @foreach ($this->roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </x-ui.select>
                        </div>
                    </div>

                    <ul class="space-y-1 p-2" aria-labelledby="people-title" wire:loading.class="opacity-50" wire:target="search, role">
                        @forelse ($this->people as $person)
                            @php $isSelected = $selected?->is($person); @endphp
                            <li wire:key="person-{{ $person->id }}">
                                <button type="button" wire:click="selectUser({{ $person->id }})" aria-pressed="{{ $isSelected ? 'true' : 'false' }}" @class([
                                    'flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-start transition',
                                    'bg-primary-500/[0.07] ring-1 ring-primary-500/25 dark:bg-primary-400/10 dark:ring-primary-400/25' => $isSelected,
                                    'hover:bg-zinc-900/[0.03] dark:hover:bg-white/[0.03]' => ! $isSelected,
                                ])>
                                    <x-ui.avatar :user="$person" size="sm" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $person->name }}</span>
                                        <span class="block truncate text-xs text-zinc-500 dark:text-zinc-400">{{ $person->email }}</span>
                                    </span>
                                    <span class="hidden shrink-0 gap-1 md:flex">
                                        @foreach ($person->roles->take(2) as $personRole)
                                            <x-ui.badge :color="$personRole->name === SystemRole::SuperAdmin->value ? 'primary' : 'zinc'">{{ $personRole->name }}</x-ui.badge>
                                        @endforeach
                                    </span>
                                    <span class="flex shrink-0 items-center gap-1.5">
                                        <span title="{{ $person->hasEnabledTwoFactorAuthentication() ? __('Two-factor enabled') : __('Two-factor off') }}">
                                            <x-heroicon-s-finger-print @class(['size-4', 'text-emerald-500' => $person->hasEnabledTwoFactorAuthentication(), 'text-zinc-300 dark:text-zinc-600' => ! $person->hasEnabledTwoFactorAuthentication()]) />
                                        </span>
                                        <span title="{{ $person->hasVerifiedEmail() ? __('Verified') : __('Unverified') }}">
                                            <x-heroicon-s-check-badge @class(['size-4', 'text-sky-500' => $person->hasVerifiedEmail(), 'text-zinc-300 dark:text-zinc-600' => ! $person->hasVerifiedEmail()]) />
                                        </span>
                                    </span>
                                    <x-heroicon-o-chevron-right @class(['size-4 shrink-0 rtl:-scale-x-100', 'text-primary-500' => $isSelected, 'text-zinc-300 dark:text-zinc-600' => ! $isSelected]) />
                                </button>
                            </li>
                        @empty
                            <li class="px-4 py-12 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ __('No users match.') }}</li>
                        @endforelse
                    </ul>

                    <div class="border-t border-zinc-200/80 p-2 dark:border-white/[0.07]">
                        <a href="{{ route('admin.users.index') }}" wire:navigate class="flex h-10 items-center justify-center gap-1.5 rounded-xl text-sm font-medium text-zinc-600 transition hover:bg-zinc-900/[0.03] hover:text-zinc-900 dark:text-zinc-300 dark:hover:bg-white/[0.04] dark:hover:text-white">
                            {{ __('All users') }}
                            <x-heroicon-o-arrow-right class="size-4 rtl:-scale-x-100" />
                        </a>
                    </div>
                </x-ui.card>

                {{-- Detail panel --}}
                <x-ui.card :padding="false" class="overflow-hidden lg:col-span-2" aria-live="polite">
                    @if ($selected)
                        <div wire:key="detail-{{ $selected->id }}">
                            {{-- Soft gradient banner --}}
                            <div class="relative h-24 overflow-hidden bg-linear-to-br from-primary-200 via-fuchsia-100 to-cyan-100 dark:from-primary-500/30 dark:via-fuchsia-500/10 dark:to-cyan-500/20">
                                <div aria-hidden="true" class="bg-grid absolute inset-0 [--grid-line:rgb(255_255_255/0.6)] dark:[--grid-line:rgb(255_255_255/0.06)]"></div>
                            </div>

                            <div class="px-6 pb-6">
                                <div class="relative -mt-9 flex items-end justify-between gap-3">
                                    <span class="rounded-full bg-white p-1 shadow-sm dark:bg-zinc-900"><x-ui.avatar :user="$selected" size="lg" /></span>
                                    <div class="flex flex-wrap justify-end gap-1.5">
                                        @can('impersonate', $selected)
                                            <x-ui.button variant="secondary" size="sm" icon="eye" wire:click="confirmImpersonate({{ $selected->id }})">{{ __('Sign in as') }}</x-ui.button>
                                        @endcan
                                        @can('update', $selected)
                                            <x-ui.button :href="route('admin.users.edit', $selected)" variant="secondary" size="sm" icon="pencil-square" wire:navigate>{{ __('Edit user') }}</x-ui.button>
                                        @endcan
                                    </div>
                                </div>

                                <p class="mt-3 truncate text-lg font-semibold text-zinc-900 dark:text-white">{{ $selected->name }}</p>
                                <p class="truncate text-sm text-zinc-500 dark:text-zinc-400">{{ $selected->email }}</p>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @forelse ($selected->roles as $selectedRole)
                                        <x-ui.badge :color="$selectedRole->name === SystemRole::SuperAdmin->value ? 'primary' : 'zinc'">{{ $selectedRole->name }}</x-ui.badge>
                                    @empty
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('No roles') }}</span>
                                    @endforelse
                                </div>

                                @php
                                    $providers = $selected->socialAccounts->map(fn ($account) => $account->provider->label())->all();
                                    $facts = [
                                        [__('Joined'), $selected->created_at->translatedFormat('j M Y'), 'calendar', null],
                                        [__('Last sign-in'), $this->selectedUserLastSignIn?->diffForHumans() ?? __('Never'), 'clock', null],
                                        [__('Email'), $selected->hasVerifiedEmail() ? __('Verified') : __('Unverified'), 'envelope', $selected->hasVerifiedEmail()],
                                        [__('Two-factor'), $selected->hasEnabledTwoFactorAuthentication() ? __('Enabled') : __('Off'), 'finger-print', $selected->hasEnabledTwoFactorAuthentication()],
                                        [__('Sign-in methods'), implode(', ', array_filter([$selected->hasPassword() ? __('Password') : null, ...$providers])) ?: '—', 'key', null],
                                    ];
                                @endphp

                                <dl class="mt-5 grid grid-cols-2 gap-2">
                                    @foreach ($facts as [$label, $value, $icon, $ok])
                                        <div @class(['rounded-xl bg-zinc-50 p-3 ring-1 ring-zinc-200/70 dark:bg-white/[0.03] dark:ring-white/[0.06]', 'col-span-2' => $loop->last])>
                                            <dt class="flex items-center gap-1.5 text-xs text-zinc-500 dark:text-zinc-400">
                                                <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-3.5" />
                                                {{ $label }}
                                            </dt>
                                            <dd @class([
                                                'mt-1 truncate text-sm font-semibold',
                                                'text-emerald-600 dark:text-emerald-400' => $ok === true,
                                                'text-amber-600 dark:text-amber-400' => $ok === false,
                                                'text-zinc-900 dark:text-white' => $ok === null,
                                            ])>{{ $value }}</dd>
                                        </div>
                                    @endforeach
                                </dl>

                                @can('activity.view')
                                    <div class="mt-6">
                                        <div class="flex items-center justify-between gap-3">
                                            <p class="text-xs font-semibold tracking-wide text-zinc-500 uppercase dark:text-zinc-400">{{ __('Recent activity') }}</p>
                                            <x-ui.link :href="route('admin.activity.index', ['search' => $selected->email, 'period' => 'all'])" class="text-xs" wire:navigate>{{ __('Full history') }}</x-ui.link>
                                        </div>
                                        <ol class="mt-3 space-y-2.5">
                                            @forelse ($this->selectedUserActivity as $entry)
                                                <li class="flex items-center gap-3 text-sm" wire:key="entry-{{ $entry->id }}">
                                                    <span @class(['size-2 shrink-0 rounded-full', 'bg-red-500' => $entry->event->severity() === 'danger', 'bg-amber-500' => $entry->event->severity() === 'warning', 'bg-emerald-500' => $entry->event->severity() === 'success', 'bg-primary-500' => $entry->event->severity() === 'info'])></span>
                                                    <span class="min-w-0 flex-1 truncate text-zinc-700 dark:text-zinc-300">{{ $entry->event->label() }}</span>
                                                    <time class="shrink-0 text-xs text-zinc-500 dark:text-zinc-400" datetime="{{ $entry->created_at->toIso8601String() }}">{{ $entry->created_at->diffForHumans(short: true) }}</time>
                                                </li>
                                            @empty
                                                <li class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Nothing recorded yet.') }}</li>
                                            @endforelse
                                        </ol>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    @else
                        <x-ui.empty-state icon="user-circle" :title="__('No user selected.')" />
                    @endif
                </x-ui.card>
            </div>
        @endcan

        {{-- ============================ Activity + security checklist ============================ --}}
        <div class="grid gap-4 lg:grid-cols-3">
            @can('activity.view')
                <x-ui.card :padding="false" class="lg:col-span-2">
                    <div class="flex items-center justify-between gap-4 px-6 pt-6 pb-3">
                        <div>
                            <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Recent activity') }}</h2>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Sign-ins, security events and changes.') }}</p>
                        </div>
                        <x-ui.link :href="route('admin.activity.index')" class="shrink-0 text-sm" wire:navigate>{{ __('View all') }}</x-ui.link>
                    </div>

                    <ol class="px-3 pb-3">
                        @forelse ($this->activity as $entry)
                            <li class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-colors hover:bg-zinc-900/[0.02] dark:hover:bg-white/[0.02]" wire:key="activity-{{ $entry->id }}">
                                <x-admin.activity-icon :event="$entry->event" size="sm" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-zinc-900 dark:text-white">{{ $entry->event->label() }}</p>
                                    <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $entry->user?->name ?? __('Guest') }}
                                        @if ($entry->subjectLabel() && $entry->subjectLabel() !== $entry->user?->email)
                                            · {{ $entry->subjectLabel() }}
                                        @elseif (isset($entry->properties['email']))
                                            · <span dir="ltr">{{ $entry->properties['email'] }}</span>
                                        @endif
                                    </p>
                                </div>
                                <time class="shrink-0 text-xs text-zinc-500 dark:text-zinc-400" datetime="{{ $entry->created_at->toIso8601String() }}" title="{{ $entry->created_at->translatedFormat('j F Y, H:i') }}">{{ $entry->created_at->diffForHumans() }}</time>
                            </li>
                        @empty
                            <li><x-ui.empty-state icon="shield-check" :title="__('No activity yet')" class="py-10!" /></li>
                        @endforelse
                    </ol>
                </x-ui.card>
            @endcan

            <x-ui.card @class(['lg:col-span-3' => ! auth()->user()->can('activity.view')])>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-semibold text-zinc-900 dark:text-white">{{ __('Security checklist') }}</h2>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('How ready this installation is for production.') }}</p>
                    </div>
                    <p class="shrink-0 text-2xl font-semibold text-zinc-900 dark:text-white"><span dir="ltr">{{ $healthScore }}/{{ count($health) }}</span></p>
                </div>

                <div class="mt-4 flex gap-1" aria-hidden="true">
                    @foreach ($health as $check)
                        <span @class(['h-1.5 flex-1 rounded-full', 'bg-emerald-500' => $check['ok'], 'bg-zinc-200 dark:bg-white/10' => ! $check['ok']])></span>
                    @endforeach
                </div>

                <ul class="mt-5 space-y-4">
                    @foreach ($health as $check)
                        <li class="flex gap-3">
                            @if ($check['ok'])
                                <x-heroicon-s-check-circle class="size-5 shrink-0 text-emerald-500" />
                            @else
                                <x-heroicon-s-exclamation-circle class="size-5 shrink-0 text-amber-500" />
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $check['label'] }}</p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $check['detail'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </x-ui.card>
        </div>
    </div>
</div>
