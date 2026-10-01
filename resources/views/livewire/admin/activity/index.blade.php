@php
    use App\Enums\ActivityEvent;
    use App\Models\ActivityLog;
    use App\Support\UserAgent;
    use Illuminate\Support\Str;

    $periods = ['1' => __('24 hours'), '7' => __('7 days'), '30' => __('30 days'), '90' => __('90 days'), 'all' => __('All time')];

    $chips = ['' => __('Everything'), ...ActivityEvent::groups(), 'threats' => __('Threats')];

    $propertyLabels = [
        'changed' => __('Changed'),
        'provider' => __('Provider'),
        'email' => __('Attempted email'),
        'method' => __('Method'),
        'remember' => __('Remember me'),
        'roles' => __('Roles'),
        'permissions' => __('Permissions'),
        'previous' => __('Previous name'),
        'section' => __('Section'),
        'action' => __('Action'),
        'first_password' => __('First password'),
        'impersonated_by' => __('Done by admin'),
        'minutes' => __('Minutes'),
    ];

    $format = fn (mixed $value) => match (true) {
        is_bool($value) => $value ? __('Yes') : __('No'),
        is_array($value) => implode(', ', array_map(fn ($item) => is_scalar($item) ? (string) $item : json_encode($item), $value)),
        default => (string) $value,
    };

    $summary = $this->summary;
    $cards = [
        [__('Events'), $summary['total'], 'bolt', 'text-primary-600 dark:text-primary-300'],
        [__('Sign-ins'), $summary['signins'], 'arrow-right-end-on-rectangle', 'text-emerald-600 dark:text-emerald-400'],
        [__('Threats'), $summary['threats'], 'exclamation-triangle', $summary['threats'] > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-400'],
        [__('Admin changes'), $summary['admin'], 'wrench-screwdriver', 'text-amber-600 dark:text-amber-400'],
        [__('Unique IP addresses'), $summary['ips'], 'globe-alt', 'text-sky-600 dark:text-sky-400'],
    ];

    $previousDay = null;
@endphp

<div>
    <x-ui.page-header
        :eyebrow="__('Administration')"
        :title="__('Activity log')"
        :description="__('Sign-ins, failed attempts and every change to accounts, roles and the website. Entries are kept for :days days.', ['days' => ActivityLog::RETENTION_DAYS])"
    />

    {{-- Summary for the selected period --}}
    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-5">
        @foreach ($cards as [$label, $value, $icon, $tone])
            <x-ui.card class="p-4!">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ $label }}</p>
                    <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-4 {{ $tone }}" />
                </div>
                <p class="mt-2 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-white">{{ number_format($value) }}</p>
            </x-ui.card>
        @endforeach
    </div>

    <x-ui.card :padding="false" class="overflow-hidden">
        {{-- Filters --}}
        <div class="space-y-3 border-b border-zinc-200/80 p-4 dark:border-white/[0.07]">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center">
                <div class="group/search relative flex-1">
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute start-3.5 top-1/2 size-4.5 -translate-y-1/2 text-zinc-400 group-focus-within/search:text-primary-500" />
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('Search by person, email or IP address…') }}"
                        aria-label="{{ __('Search the activity log') }}"
                        class="block h-11 w-full rounded-xl border-0 bg-zinc-900/[0.03] ps-10 pe-3 text-sm text-zinc-900 ring-1 ring-transparent ring-inset placeholder:text-zinc-400 focus:bg-white focus:ring-2 focus:ring-primary-500 dark:bg-white/[0.04] dark:text-white dark:focus:bg-white/[0.06] dark:focus:ring-primary-400"
                    >
                </div>

                <x-ui.select wire:model.live="event" class="lg:w-60" aria-label="{{ __('Filter by event') }}">
                    <option value="">{{ __('All events') }}</option>
                    @foreach (ActivityEvent::groups() as $groupKey => $groupLabel)
                        <optgroup label="{{ $groupLabel }}">
                            @foreach (ActivityEvent::inGroup($groupKey) as $case)
                                <option value="{{ $case->value }}">{{ $case->label() }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </x-ui.select>

                <div class="inline-flex shrink-0 overflow-x-auto rounded-xl bg-zinc-900/[0.04] p-1 dark:bg-white/[0.05]" role="group" aria-label="{{ __('Period') }}">
                    @foreach ($periods as $value => $label)
                        <button type="button" wire:click="$set('period', '{{ $value }}')" aria-pressed="{{ $period === (string) $value ? 'true' : 'false' }}" @class([
                            'h-9 shrink-0 rounded-lg px-3 text-xs font-medium whitespace-nowrap transition',
                            'bg-white text-zinc-900 shadow-xs dark:bg-white/10 dark:text-white' => $period === (string) $value,
                            'text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white' => $period !== (string) $value,
                        ])>{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @foreach ($chips as $value => $label)
                    <button type="button" wire:click="setGroup('{{ $value }}')" aria-pressed="{{ $group === $value && $event === '' ? 'true' : 'false' }}" @class([
                        'inline-flex h-8 items-center gap-1.5 rounded-full px-3 text-xs font-medium ring-1 transition ring-inset',
                        'bg-zinc-900 text-white ring-zinc-900 dark:bg-white dark:text-zinc-900 dark:ring-white' => $group === $value && $event === '',
                        'text-zinc-600 ring-zinc-200 hover:bg-zinc-50 dark:text-zinc-300 dark:ring-white/10 dark:hover:bg-white/5' => ! ($group === $value && $event === ''),
                    ])>
                        @if ($value === 'threats')
                            <span class="size-1.5 rounded-full bg-red-500"></span>
                        @endif
                        {{ $label }}
                    </button>
                @endforeach

                @if ($search !== '' || $group !== '' || $event !== '' || $period !== '30')
                    <button type="button" wire:click="resetFilters" class="ms-auto inline-flex items-center gap-1 text-xs font-medium text-zinc-500 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                        <x-heroicon-o-x-mark class="size-3.5" />
                        {{ __('Clear filters') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Timeline --}}
        <div class="transition-opacity" wire:loading.class="opacity-50" wire:target="search, event, period, setGroup, resetFilters, gotoPage, nextPage, previousPage">
            @forelse ($this->logs as $log)
                @php
                    $day = $log->created_at->isToday() ? __('Today') : ($log->created_at->isYesterday() ? __('Yesterday') : $log->created_at->translatedFormat('l j F Y'));
                    $details = collect($log->properties ?? [])->except('label')->filter(fn ($value) => $value !== null && $value !== '' && $value !== []);
                    $device = UserAgent::describe($log->user_agent);
                    $expandable = $details->isNotEmpty() || filled($log->user_agent);
                @endphp

                @if ($day !== $previousDay)
                    <div class="sticky top-16 z-10 border-b border-zinc-200/80 bg-zinc-50/90 px-5 py-2 text-xs font-semibold tracking-wide text-zinc-500 uppercase backdrop-blur dark:border-white/[0.07] dark:bg-zinc-900/80 dark:text-zinc-400" wire:key="day-{{ $log->id }}">{{ $day }}</div>
                    @php $previousDay = $day; @endphp
                @endif

                <div wire:key="log-{{ $log->id }}" x-data="{ open: false }" class="border-b border-zinc-100 last:border-b-0 dark:border-white/[0.05]">
                    <div class="flex items-start gap-4 px-5 py-3.5 transition-colors hover:bg-zinc-900/[0.015] dark:hover:bg-white/[0.02]">
                        <x-admin.activity-icon :event="$log->event" />

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                <p class="font-medium text-zinc-900 dark:text-white">{{ $log->event->label() }}</p>
                                @if ($log->subjectLabel())
                                    <span class="truncate text-sm text-zinc-500 dark:text-zinc-400">{{ $log->subjectLabel() }}</span>
                                @elseif (isset($log->properties['email']))
                                    <span class="truncate text-sm text-zinc-500 dark:text-zinc-400" dir="ltr">{{ $log->properties['email'] }}</span>
                                @endif
                                @if (isset($log->properties['impersonated_by']))
                                    <x-ui.badge color="amber">
                                        <x-heroicon-m-eye class="size-3" />
                                        <span dir="ltr">{{ __('by :email', ['email' => $log->properties['impersonated_by']]) }}</span>
                                    </x-ui.badge>
                                @endif
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-zinc-500 dark:text-zinc-400">
                                <span class="inline-flex items-center gap-1.5">
                                    @if ($log->user)
                                        <x-ui.avatar :user="$log->user" size="xs" />
                                        {{ $log->user->name }}
                                    @else
                                        <x-heroicon-o-user class="size-3.5" />
                                        {{ __('Guest') }}
                                    @endif
                                </span>
                                @if ($log->ip_address)
                                    <span class="inline-flex items-center gap-1 font-mono" dir="ltr"><x-heroicon-o-map-pin class="size-3.5" />{{ $log->ip_address }}</span>
                                @endif
                                @if ($device)
                                    <span class="inline-flex items-center gap-1">
                                        <x-dynamic-component :component="UserAgent::isMobile($log->user_agent) ? 'heroicon-o-device-phone-mobile' : 'heroicon-o-computer-desktop'" class="size-3.5" />
                                        {{ $device }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <time datetime="{{ $log->created_at->toIso8601String() }}" title="{{ $log->created_at->translatedFormat('j F Y, H:i:s') }}" class="text-xs whitespace-nowrap text-zinc-500 dark:text-zinc-400">
                                {{ $log->created_at->diffForHumans() }}
                            </time>

                            @if ($expandable)
                                <button type="button" x-on:click="open = ! open" x-bind:aria-expanded="open" class="flex size-7 items-center justify-center rounded-lg text-zinc-400 transition hover:bg-zinc-900/5 hover:text-zinc-700 dark:hover:bg-white/10 dark:hover:text-zinc-200">
                                    <x-heroicon-o-chevron-down class="size-4 transition-transform" x-bind:class="open && 'rotate-180'" />
                                    <span class="sr-only">{{ __('Details') }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    @if ($expandable)
                        <div x-show="open" x-collapse x-cloak>
                            <dl class="mx-5 mb-4 grid gap-x-6 gap-y-2 rounded-xl bg-zinc-50 p-4 text-xs sm:grid-cols-2 dark:bg-white/[0.03]">
                                @foreach ($details as $key => $value)
                                    <div class="flex gap-2">
                                        <dt class="shrink-0 text-zinc-500 dark:text-zinc-400">{{ $propertyLabels[$key] ?? Str::headline($key) }}:</dt>
                                        <dd class="min-w-0 font-medium break-words text-zinc-800 dark:text-zinc-200">{{ $format($value) }}</dd>
                                    </div>
                                @endforeach
                                @if ($log->user_agent)
                                    <div class="flex gap-2 sm:col-span-2">
                                        <dt class="shrink-0 text-zinc-500 dark:text-zinc-400">{{ __('Browser') }}:</dt>
                                        <dd class="min-w-0 font-mono break-all text-zinc-600 dark:text-zinc-300" dir="ltr">{{ $log->user_agent }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif
                </div>
            @empty
                <x-ui.empty-state icon="shield-check" :title="__('No activity found')" :description="__('Nothing matches these filters. Try a longer period or clear the filters.')" />
            @endforelse
        </div>

        @if ($this->logs->hasPages())
            <div class="border-t border-zinc-200/80 px-5 py-3 dark:border-white/[0.07]">
                {{ $this->logs->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
