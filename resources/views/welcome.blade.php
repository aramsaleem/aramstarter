{{--
    The public website. Texts, features, plans and questions come from Admin > Website
    (see HomeController); everything is escaped on output and links are re-checked here.
--}}
@php
    use App\Livewire\Admin\Website\Plans as PlanEditor;
    use App\Models\Feature;
    use App\Support\Localization;

    $cta = auth()->check() ? route('dashboard') : route('register');
    $ctaLabel = auth()->check() ? __('Go to dashboard') : $site['hero_primary_cta'];

    $nav = array_values(array_filter([
        $features->isNotEmpty() ? ['#features', __('Features')] : null,
        $site['show_how_it_works'] ? ['#how-it-works', __('How it works')] : null,
        $plans->isNotEmpty() ? ['#pricing', __('Pricing')] : null,
        $faqs->isNotEmpty() ? ['#faq', __('FAQ')] : null,
    ]));

    $secondaryHref = $site['show_how_it_works'] ? '#how-it-works' : ($nav[0][0] ?? null);

    $stack = ['Laravel 12', 'Livewire 3', 'Tailwind CSS 4', 'Alpine.js', 'Pest 4', 'Spatie Permission', 'Socialite', 'Google2FA', 'Vite 7', 'MySQL'];

    $counters = $stats ? [
        [$stats['users'], __('Registered users')],
        [$stats['languages'], __('Languages, with RTL')],
        [$stats['permissions'], __('Permissions to assign')],
        [$stats['events'], __('Security events logged')],
    ] : [];

    $steps = [
        ['01', __('Install'), __('One command installs everything, runs the migrations and builds the frontend.'), 'composer run setup'],
        ['02', __('Configure'), __('Add your mail and social login keys to .env - buttons appear on their own.'), 'php artisan app:create-super-admin'],
        ['03', __('Launch'), __('Deploy with confidence: the test suite has your back.'), 'composer run review'],
    ];

    // Icon tile colour and hover glow per card, in order.
    $tones = [
        ['bg-primary-50 text-primary-600 ring-primary-100', 'rgb(139 92 246 / 0.10)'],
        ['bg-cyan-50 text-cyan-600 ring-cyan-100', 'rgb(6 182 212 / 0.10)'],
        ['bg-emerald-50 text-emerald-600 ring-emerald-100', 'rgb(16 185 129 / 0.10)'],
        ['bg-amber-50 text-amber-600 ring-amber-100', 'rgb(245 158 11 / 0.10)'],
        ['bg-sky-50 text-sky-600 ring-sky-100', 'rgb(14 165 233 / 0.10)'],
        ['bg-rose-50 text-rose-600 ring-rose-100', 'rgb(244 63 94 / 0.10)'],
    ];

    $hasYearly = $plans->contains(fn ($plan) => $plan->price_yearly !== null);
    $saving = $plans
        ->filter(fn ($plan) => $plan->price_yearly !== null && (float) $plan->price_monthly > 0)
        ->map(fn ($plan) => (int) round((1 - (float) $plan->price_yearly / ((float) $plan->price_monthly * 12)) * 100))
        ->max();

    // Links were validated when saved; check again so a tampered row can never become a javascript: link.
    $planLink = fn ($plan) => filled($plan->cta_url) && preg_match(PlanEditor::LINK_PATTERN, $plan->cta_url) ? $plan->cta_url : $cta;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ Localization::direction() }}" class="scroll-smooth" data-force-light>
    <head>
        @include('partials.head', ['title' => null])
        <meta name="description" content="{{ $site['hero_subtitle'] }}">
        <noscript><style>.reveal { opacity: 1; transform: none; }</style></noscript>
    </head>
    <body class="min-h-screen overflow-x-hidden bg-white font-sans text-zinc-900 antialiased selection:bg-primary-200 selection:text-primary-950">
        {{-- Background: pastel light, grid --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-x-0 top-0 h-[64rem] overflow-hidden">
            <div class="absolute start-1/2 -top-[26rem] h-[52rem] w-[80rem] -translate-x-1/2 rounded-full bg-[radial-gradient(closest-side,rgb(167_139_250/0.32),transparent)] rtl:translate-x-1/2"></div>
            <div class="absolute end-[-12rem] top-24 size-[34rem] rounded-full bg-[radial-gradient(closest-side,rgb(103_232_249/0.28),transparent)]"></div>
            <div class="absolute start-[-14rem] top-[28rem] size-[30rem] rounded-full bg-[radial-gradient(closest-side,rgb(244_114_182/0.14),transparent)]"></div>
            <div class="bg-grid absolute inset-0 [--grid-line:rgb(24_24_27/0.05)]"></div>
        </div>

        {{-- ============================ Navigation ============================ --}}
        <header class="sticky top-4 z-50 px-4" x-data="{ menu: false }">
            <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 rounded-full border border-zinc-200/80 bg-white/75 py-2 ps-5 pe-2 shadow-[0_8px_30px_-12px_rgb(24_24_27/0.18)] backdrop-blur-xl">
                <a href="{{ route('home') }}" aria-label="{{ config('app.name') }}"><x-app-logo /></a>

                <nav class="hidden items-center gap-1 text-sm md:flex" aria-label="{{ __('Main') }}">
                    @foreach ($nav as [$href, $label])
                        <a href="{{ $href }}" class="rounded-full px-3.5 py-2 text-zinc-600 transition hover:bg-zinc-900/[0.04] hover:text-zinc-950">{{ $label }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-1">
                    <x-locale-switcher />

                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800">{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden rounded-full px-4 py-2 text-sm font-medium text-zinc-700 transition hover:text-zinc-950 sm:block">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-zinc-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-800">{{ __('Get started') }}</a>
                    @endauth

                    @if ($nav !== [])
                        <button type="button" x-on:click="menu = ! menu" class="flex size-9 items-center justify-center rounded-full text-zinc-600 hover:bg-zinc-900/5 md:hidden" aria-label="{{ __('Open navigation') }}" x-bind:aria-expanded="menu">
                            <x-heroicon-o-bars-3 class="size-5" />
                        </button>
                    @endif
                </div>
            </div>

            <nav x-cloak x-show="menu" x-transition x-on:click="menu = false" class="mx-auto mt-2 grid max-w-5xl gap-1 rounded-3xl border border-zinc-200 bg-white/95 p-2 shadow-xl backdrop-blur-xl md:hidden">
                @foreach ($nav as [$href, $label])
                    <a href="{{ $href }}" class="rounded-2xl px-4 py-3 text-sm text-zinc-700 hover:bg-zinc-50">{{ $label }}</a>
                @endforeach
            </nav>
        </header>

        <main class="relative">
            {{-- ============================ Hero ============================ --}}
            <section class="mx-auto max-w-6xl px-4 pt-20 text-center sm:px-6 sm:pt-28">
                <a href="{{ $nav[0][0] ?? $cta }}" class="border-beam animate-fade-up group inline-flex max-w-full items-center gap-2 rounded-full bg-white/80 px-4 py-1.5 text-xs font-medium text-zinc-700 shadow-sm ring-1 ring-zinc-200/80 backdrop-blur transition hover:bg-white">
                    <span class="relative flex size-2 shrink-0"><span class="absolute inline-flex size-full animate-ping rounded-full bg-primary-400 opacity-60"></span><span class="relative inline-flex size-2 rounded-full bg-primary-500"></span></span>
                    <span class="truncate">{{ $site['hero_badge'] }}</span>
                    <x-heroicon-o-arrow-right class="size-3.5 shrink-0 transition group-hover:translate-x-0.5 rtl:-scale-x-100" />
                </a>

                <h1 class="animate-fade-up mx-auto mt-8 max-w-4xl text-[2.6rem] leading-[1.04] font-semibold tracking-[-0.04em] text-balance text-zinc-950 [animation-delay:80ms] sm:text-7xl lg:text-[5.25rem]">
                    {{ $site['hero_title'] }}<br>
                    <span class="text-gradient-vivid animate-shine">{{ $site['hero_highlight'] }}</span>
                </h1>

                <p class="animate-fade-up mx-auto mt-7 max-w-2xl text-lg leading-8 text-pretty text-zinc-600 [animation-delay:160ms]">{{ $site['hero_subtitle'] }}</p>

                <div class="animate-fade-up mt-10 flex flex-wrap items-center justify-center gap-3 [animation-delay:240ms]">
                    <a href="{{ $cta }}" class="group relative inline-flex h-12 items-center gap-2 overflow-hidden rounded-full bg-primary-600 px-7 text-sm font-semibold text-white shadow-[0_12px_32px_-10px_var(--color-primary-600)] transition hover:-translate-y-0.5 hover:bg-primary-500">
                        <span aria-hidden="true" class="absolute inset-0 -translate-x-full bg-linear-to-r from-transparent via-white/30 to-transparent transition-transform duration-700 group-hover:translate-x-full"></span>
                        {{ $ctaLabel }}
                        <x-heroicon-o-arrow-right class="size-4 transition group-hover:translate-x-0.5 rtl:-scale-x-100" />
                    </a>
                    @if ($secondaryHref)
                        <a href="{{ $secondaryHref }}" class="inline-flex h-12 items-center gap-2 rounded-full bg-white px-7 text-sm font-semibold text-zinc-900 shadow-sm ring-1 ring-zinc-200 transition hover:-translate-y-0.5 hover:ring-zinc-300">
                            <x-heroicon-o-play-circle class="size-5 text-primary-600" />
                            {{ $site['hero_secondary_cta'] }}
                        </a>
                    @endif
                </div>

                <div class="animate-fade-up mt-6 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-zinc-500 [animation-delay:300ms]">
                    @foreach ([__('Two-factor ready'), __('Arabic & Kurdish RTL'), __('Audit log included')] as $point)
                        <span class="inline-flex items-center gap-1.5"><x-heroicon-s-check-circle class="size-4 text-emerald-500" />{{ $point }}</span>
                    @endforeach
                </div>

                {{-- Product preview in perspective --}}
                <div aria-hidden="true" class="animate-fade-up relative mx-auto mt-16 max-w-5xl [animation-delay:360ms] [perspective:2000px]">
                    <div class="absolute inset-x-16 -top-6 bottom-24 rounded-full bg-linear-to-r from-primary-300/50 via-fuchsia-200/40 to-cyan-200/50 blur-[90px]"></div>

                    <div class="relative rounded-[1.75rem] border border-white/80 bg-white/60 p-2 shadow-[0_40px_120px_-40px_rgb(76_29_149/0.45)] ring-1 ring-zinc-200/70 backdrop-blur transition duration-700 [transform:rotateX(16deg)] hover:[transform:rotateX(0deg)] sm:p-3">
                        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white text-start">
                            <div class="flex items-center gap-2 border-b border-zinc-100 bg-zinc-50/80 px-4 py-2.5">
                                <span class="size-2.5 rounded-full bg-[#ff5f57]"></span><span class="size-2.5 rounded-full bg-[#febc2e]"></span><span class="size-2.5 rounded-full bg-[#28c840]"></span>
                                <span class="mx-auto flex h-6 w-56 items-center justify-center rounded-md bg-white text-[0.65rem] text-zinc-400 ring-1 ring-zinc-200" dir="ltr">{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'app.test' }}/admin</span>
                            </div>
                            <div class="flex">
                                <div class="hidden w-48 shrink-0 border-e border-zinc-100 bg-zinc-50/60 p-4 md:block">
                                    <div class="flex items-center gap-2"><x-app-logo-icon class="size-6" /><span class="text-sm font-semibold">{{ config('app.name') }}</span></div>
                                    <div class="mt-6 space-y-1 text-xs">
                                        @foreach ([['squares-2x2', __('Overview'), true], ['users', __('Users'), false], ['identification', __('Roles'), false], ['shield-check', __('Activity'), false], ['globe-alt', __('Website'), false]] as [$icon, $label, $active])
                                            <div @class(['flex items-center gap-2.5 rounded-lg px-2.5 py-2', 'bg-white text-zinc-900 shadow-xs ring-1 ring-zinc-200' => $active, 'text-zinc-500' => ! $active])>
                                                <x-dynamic-component :component="'heroicon-o-'.$icon" @class(['size-4', 'text-primary-600' => $active]) />
                                                {{ $label }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="min-w-0 flex-1 p-4 sm:p-6">
                                    <p class="text-sm font-semibold">{{ __('Overview') }}</p>
                                    <div class="mt-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
                                        @foreach ([['12,540', '+18%', 'bg-primary-500'], ['98.2%', '+4%', 'bg-cyan-500'], ['64%', '+12%', 'bg-emerald-500'], ['2,318', '+9%', 'bg-amber-500']] as [$value, $change, $color])
                                            <div class="rounded-xl border border-zinc-100 bg-white p-3 shadow-xs">
                                                <div class="flex items-center gap-1.5"><span class="size-1.5 rounded-full {{ $color }}"></span><span class="h-1.5 w-10 rounded bg-zinc-100"></span></div>
                                                <p class="mt-2 text-lg font-semibold">{{ $value }}</p>
                                                <p class="text-[0.65rem] font-medium text-emerald-600">{{ $change }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-3 grid gap-3 lg:grid-cols-3">
                                        <div class="rounded-xl border border-zinc-100 bg-white p-4 shadow-xs lg:col-span-2">
                                            <div class="flex h-36 items-end gap-1.5">
                                                @foreach ([28, 40, 34, 52, 46, 60, 55, 68, 62, 78, 72, 88, 82, 100] as $height)
                                                    <span class="flex-1 rounded-t bg-linear-to-t from-primary-200 to-primary-500" style="height: {{ $height }}%"></span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="space-y-3 rounded-xl border border-zinc-100 bg-white p-4 shadow-xs">
                                            @foreach ([['bg-primary-500', 82], ['bg-cyan-500', 58], ['bg-emerald-500', 34]] as [$color, $width])
                                                <div class="flex items-center gap-2">
                                                    <span class="size-6 shrink-0 rounded-full bg-zinc-100"></span>
                                                    <div class="flex-1">
                                                        <div class="h-1.5 w-14 rounded bg-zinc-100"></div>
                                                        <div class="mt-2 h-1.5 rounded-full bg-zinc-100"><div class="h-1.5 rounded-full {{ $color }}" style="width: {{ $width }}%"></div></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="absolute inset-x-0 bottom-0 h-40 bg-linear-to-t from-white to-transparent"></div>
                </div>
            </section>

            {{-- ============================ Stack marquee ============================ --}}
            <section class="relative py-10" aria-label="{{ __('Built on tools you already love') }}">
                <p class="text-center text-xs font-semibold tracking-[0.2em] text-zinc-400 uppercase">{{ __('Built on tools you already love') }}</p>
                <div class="mt-7 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_15%,black_85%,transparent)]" dir="ltr">
                    <div class="animate-marquee flex w-max gap-3 hover:[animation-play-state:paused]">
                        @foreach ([...$stack, ...$stack] as $tool)
                            <span class="rounded-full bg-white px-5 py-2 text-sm font-medium whitespace-nowrap text-zinc-600 shadow-xs ring-1 ring-zinc-200">{{ $tool }}</span>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- ============================ Live counters ============================ --}}
            @if ($counters !== [])
                <section class="mx-auto max-w-5xl px-4 py-14 sm:px-6" aria-label="{{ __('In numbers') }}">
                    <div class="grid grid-cols-2 overflow-hidden rounded-3xl bg-white shadow-[0_20px_60px_-30px_rgb(24_24_27/0.25)] ring-1 ring-zinc-200/80 lg:grid-cols-4">
                        @foreach ($counters as $index => [$target, $label])
                            <div @class(['reveal px-6 py-8 text-center', 'border-e border-zinc-100' => ! $loop->last, 'border-b border-zinc-100 lg:border-b-0' => $index < 2]) style="--reveal-delay: {{ $index * 90 }}ms"
                                x-data="{ value: 0 }"
                                x-intersect.once="let start = null; const step = (time) => { start ??= time; const progress = Math.min((time - start) / 1200, 1); value = Math.round({{ (int) $target }} * (1 - Math.pow(1 - progress, 3))); if (progress < 1) requestAnimationFrame(step) }; requestAnimationFrame(step)">
                                <p class="text-4xl font-semibold tracking-tight text-zinc-950 sm:text-5xl"><span x-text="value.toLocaleString('en')">{{ number_format($target) }}</span></p>
                                <p class="mt-2 text-sm text-zinc-500">{{ $label }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ============================ Features ============================ --}}
            @if ($features->isNotEmpty())
                <section id="features" class="mx-auto max-w-6xl scroll-mt-28 px-4 py-24 sm:px-6">
                    <div class="reveal mx-auto max-w-2xl text-center">
                        <p class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 ring-1 ring-primary-100">{{ __('Features') }}</p>
                        <h2 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-balance text-zinc-950 sm:text-5xl">{{ __('Everything already built, tested and translated') }}</h2>
                    </div>

                    <div class="mt-16 grid auto-rows-[minmax(14rem,auto)] gap-4 md:grid-flow-dense md:grid-cols-3">
                        @foreach ($features as $feature)
                            @php
                                [$tone, $glow] = $tones[$loop->index % count($tones)];
                                $large = $loop->first && $features->count() >= 3;
                                $icon = in_array($feature->icon, Feature::ICONS, true) ? $feature->icon : 'sparkles';
                                $visual = in_array($feature->visual, Feature::VISUALS, true) ? $feature->visual : null;
                            @endphp

                            <article @class(['reveal group relative flex flex-col overflow-hidden rounded-3xl bg-white p-8 shadow-[0_1px_2px_rgb(0_0_0/0.04),0_12px_40px_-20px_rgb(24_24_27/0.18)] ring-1 ring-zinc-200/80 transition duration-300 hover:-translate-y-1 hover:shadow-[0_24px_60px_-24px_rgb(76_29_149/0.3)]', 'md:col-span-2 md:row-span-2' => $large])
                                style="--reveal-delay: {{ ($loop->index % 3) * 100 }}ms"
                                x-data="{ x: 0, y: 0 }" x-on:mousemove="x = $event.offsetX; y = $event.offsetY"
                                x-bind:style="`background-image: radial-gradient(420px circle at ${x}px ${y}px, {{ $glow }}, transparent 45%)`">
                                <span class="flex size-11 items-center justify-center rounded-xl ring-1 {{ $tone }}">
                                    <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5" />
                                </span>
                                <h3 @class(['mt-6 font-semibold text-zinc-950', 'text-2xl tracking-tight' => $large, 'text-lg' => ! $large])>{{ $feature->title }}</h3>
                                <p @class(['mt-2 text-zinc-600', 'max-w-md' => $large, 'text-sm' => ! $large])>{{ $feature->description }}</p>

                                @if ($visual)
                                    <div @class(['flex flex-1 items-center justify-center pt-8' => $large, 'mt-auto pt-8' => ! $large])>
                                        @include('landing.visuals.'.$visual)
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ============================ How it works ============================ --}}
            @if ($site['show_how_it_works'])
                <section id="how-it-works" class="scroll-mt-28 bg-linear-to-b from-zinc-50/0 via-zinc-50 to-zinc-50/0">
                    <div class="mx-auto grid max-w-6xl items-center gap-16 px-4 py-24 sm:px-6 lg:grid-cols-2">
                        <div>
                            <p class="reveal inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 ring-1 ring-primary-100">{{ __('How it works') }}</p>
                            <h2 class="reveal mt-4 text-4xl font-semibold tracking-[-0.03em] text-balance text-zinc-950 sm:text-5xl">{{ __('From zero to launch in three steps') }}</h2>

                            <ol class="relative mt-12 space-y-10 before:absolute before:start-[1.1875rem] before:top-2 before:bottom-2 before:w-px before:bg-linear-to-b before:from-primary-300 before:to-cyan-200">
                                @foreach ($steps as $index => [$number, $title, $text, $command])
                                    <li class="reveal relative flex gap-5" style="--reveal-delay: {{ $index * 120 }}ms">
                                        <span class="relative flex size-10 shrink-0 items-center justify-center rounded-full bg-white font-mono text-xs font-semibold text-primary-600 shadow-sm ring-1 ring-primary-200">{{ $number }}</span>
                                        <div class="pt-1.5">
                                            <h3 class="font-semibold text-zinc-950">{{ $title }}</h3>
                                            <p class="mt-1 text-sm leading-6 text-zinc-600">{{ $text }}</p>
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>

                        {{-- Terminal, light --}}
                        <div class="reveal border-beam rounded-3xl bg-white p-1 shadow-[0_30px_80px_-30px_rgb(76_29_149/0.35)]" dir="ltr">
                            <div class="overflow-hidden rounded-[1.35rem] ring-1 ring-zinc-200">
                                <div class="flex items-center gap-2 border-b border-zinc-100 bg-zinc-50 px-5 py-3.5">
                                    <span class="size-3 rounded-full bg-[#ff5f57]"></span>
                                    <span class="size-3 rounded-full bg-[#febc2e]"></span>
                                    <span class="size-3 rounded-full bg-[#28c840]"></span>
                                    <span class="ms-3 font-mono text-xs text-zinc-400">~/{{ \Illuminate\Support\Str::slug(config('app.name')) }}</span>
                                </div>
                                <div class="space-y-5 bg-white p-6 font-mono text-sm">
                                    @foreach ($steps as [$number, $title, $text, $command])
                                        <div>
                                            <p><span class="text-emerald-600">➜</span> <span class="text-primary-600">~</span> <span class="text-zinc-900">{{ $command }}</span></p>
                                            <p class="mt-1 text-zinc-400">✓ {{ $title }}</p>
                                        </div>
                                    @endforeach
                                    <p><span class="text-emerald-600">➜</span> <span class="text-primary-600">~</span> <span class="animate-blink text-zinc-900">▋</span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @endif

            {{-- ============================ Pricing ============================ --}}
            @if ($plans->isNotEmpty())
                <section id="pricing" class="mx-auto max-w-6xl scroll-mt-28 px-4 py-24 sm:px-6" x-data="{ yearly: false }">
                    <div class="reveal mx-auto max-w-2xl text-center">
                        <p class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 ring-1 ring-primary-100">{{ __('Pricing') }}</p>
                        <h2 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-zinc-950 sm:text-5xl">{{ __('Simple, transparent pricing') }}</h2>
                        <p class="mt-4 text-zinc-600">{{ __('Start free and upgrade when your team grows.') }}</p>

                        @if ($hasYearly)
                            <div class="mt-8 inline-flex items-center rounded-full bg-white p-1 text-sm shadow-sm ring-1 ring-zinc-200" role="group" aria-label="{{ __('Billing period') }}">
                                <button type="button" x-on:click="yearly = false" x-bind:aria-pressed="! yearly" x-bind:class="! yearly ? 'bg-zinc-950 text-white' : 'text-zinc-600'" class="rounded-full px-5 py-2 font-medium transition">{{ __('Monthly') }}</button>
                                <button type="button" x-on:click="yearly = true" x-bind:aria-pressed="yearly" x-bind:class="yearly ? 'bg-zinc-950 text-white' : 'text-zinc-600'" class="flex items-center gap-2 rounded-full px-5 py-2 font-medium transition">
                                    {{ __('Yearly') }}
                                    @if ($saving > 0)
                                        <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700" dir="ltr">-{{ $saving }}%</span>
                                    @endif
                                </button>
                            </div>
                        @endif
                    </div>

                    <div @class(['mx-auto mt-14 grid gap-5', 'max-w-md' => $plans->count() === 1, 'max-w-3xl md:grid-cols-2' => $plans->count() === 2, 'lg:grid-cols-3' => $plans->count() >= 3])>
                        @foreach ($plans as $plan)
                            <div @class([
                                'reveal relative flex flex-col rounded-3xl p-8',
                                'border-beam bg-linear-to-b from-primary-50 to-white shadow-[0_30px_80px_-30px_var(--color-primary-400)] ring-1 ring-primary-200' => $plan->is_featured,
                                'bg-white shadow-[0_12px_40px_-24px_rgb(24_24_27/0.25)] ring-1 ring-zinc-200/80' => ! $plan->is_featured,
                            ]) style="--reveal-delay: {{ ($loop->index % 3) * 100 }}ms">
                                <div class="flex items-center justify-between gap-3">
                                    <h3 class="font-semibold text-zinc-950">{{ $plan->name }}</h3>
                                    @if ($plan->is_featured)
                                        <span class="rounded-full bg-primary-600 px-3 py-1 text-xs font-semibold text-white shadow-sm">{{ __('Most popular') }}</span>
                                    @endif
                                </div>
                                @if (filled($plan->description))
                                    <p class="mt-2 text-sm text-zinc-600">{{ $plan->description }}</p>
                                @endif

                                <p class="mt-8 flex items-baseline gap-1" dir="ltr">
                                    <span class="text-5xl font-semibold tracking-tight text-zinc-950" x-show="! yearly || {{ $plan->price_yearly === null ? 'true' : 'false' }}">{{ $plan->formatPrice($plan->price_monthly) }}</span>
                                    @if ($plan->price_yearly !== null)
                                        <span class="text-5xl font-semibold tracking-tight text-zinc-950" x-show="yearly" x-cloak>{{ $plan->formatPrice($plan->yearlyMonthlyPrice()) }}</span>
                                    @endif
                                    <span class="text-sm text-zinc-500">/{{ __('month') }}</span>
                                </p>
                                <p class="mt-1 h-5 text-xs text-zinc-500">
                                    @if ($plan->price_yearly !== null && (float) $plan->price_yearly > 0)
                                        <span x-show="yearly" x-cloak>{{ __(':price billed yearly', ['price' => $plan->formatPrice($plan->price_yearly)]) }}</span>
                                    @endif
                                </p>

                                <a href="{{ $planLink($plan) }}" @class([
                                    'mt-6 flex h-11 items-center justify-center rounded-full text-sm font-semibold transition',
                                    'bg-primary-600 text-white shadow-[0_10px_24px_-10px_var(--color-primary-600)] hover:bg-primary-500' => $plan->is_featured,
                                    'bg-zinc-950 text-white hover:bg-zinc-800' => ! $plan->is_featured,
                                ])>{{ $plan->cta_label ?: __('Get started') }}</a>

                                @if ($plan->featureList() !== [])
                                    <ul class="mt-8 space-y-3 text-sm">
                                        @foreach ($plan->featureList() as $line)
                                            <li class="flex items-start gap-3 text-zinc-700">
                                                <x-heroicon-s-check-circle class="mt-px size-5 shrink-0 text-primary-500" />
                                                {{ $line }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ============================ FAQ ============================ --}}
            @if ($faqs->isNotEmpty())
                <section id="faq" class="mx-auto max-w-3xl scroll-mt-28 px-4 py-24 sm:px-6">
                    <div class="reveal text-center">
                        <p class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 ring-1 ring-primary-100">{{ __('FAQ') }}</p>
                        <h2 class="mt-4 text-4xl font-semibold tracking-[-0.03em] text-zinc-950">{{ __('Frequently asked questions') }}</h2>
                    </div>

                    <div class="mt-12 space-y-3" x-data="{ open: 0 }">
                        @foreach ($faqs as $faq)
                            <div class="reveal overflow-hidden rounded-2xl bg-white ring-1 transition" x-bind:class="open === {{ $loop->index }} ? 'shadow-[0_16px_40px_-24px_rgb(76_29_149/0.35)] ring-primary-200' : 'ring-zinc-200 hover:ring-zinc-300'">
                                <h3>
                                    <button type="button" id="faq-{{ $faq->id }}" x-on:click="open = open === {{ $loop->index }} ? null : {{ $loop->index }}" class="flex w-full items-center justify-between gap-4 px-6 py-5 text-start font-medium text-zinc-950" x-bind:aria-expanded="open === {{ $loop->index }}" aria-controls="faq-{{ $faq->id }}-answer">
                                        {{ $faq->question }}
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full ring-1 transition" x-bind:class="open === {{ $loop->index }} ? 'rotate-45 bg-primary-600 text-white ring-primary-600' : 'text-zinc-500 ring-zinc-200'">
                                            <x-heroicon-o-plus class="size-4" />
                                        </span>
                                    </button>
                                </h3>
                                <div id="faq-{{ $faq->id }}-answer" role="region" aria-labelledby="faq-{{ $faq->id }}" x-show="open === {{ $loop->index }}" x-collapse @unless ($loop->first) x-cloak @endunless>
                                    <p class="px-6 pb-6 text-sm leading-7 whitespace-pre-line text-zinc-600">{{ $faq->answer }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ============================ Closing CTA ============================ --}}
            <section class="mx-auto max-w-6xl px-4 pt-8 pb-24 sm:px-6">
                <div class="reveal relative overflow-hidden rounded-[2.5rem] bg-linear-to-br from-primary-100 via-white to-cyan-100 px-6 py-20 text-center ring-1 ring-primary-100 sm:px-16">
                    <div aria-hidden="true" class="bg-grid absolute inset-0 [--grid-line:rgb(124_58_237/0.08)]"></div>
                    <div aria-hidden="true" class="absolute -top-24 start-1/2 h-64 w-[40rem] max-w-full -translate-x-1/2 rounded-full bg-primary-300/40 blur-[100px] rtl:translate-x-1/2"></div>

                    <div class="relative">
                        <h2 class="mx-auto max-w-2xl text-4xl font-semibold tracking-[-0.03em] text-balance text-zinc-950 sm:text-6xl">{{ $site['cta_title'] }}</h2>
                        <p class="mx-auto mt-6 max-w-xl text-zinc-600">{{ $site['cta_subtitle'] }}</p>
                        <a href="{{ $cta }}" class="mt-10 inline-flex h-12 items-center gap-2 rounded-full bg-primary-600 px-8 text-sm font-semibold text-white shadow-[0_12px_32px_-10px_var(--color-primary-600)] transition hover:-translate-y-0.5 hover:bg-primary-500">
                            {{ $ctaLabel }}
                            <x-heroicon-o-arrow-right class="size-4 rtl:-scale-x-100" />
                        </a>
                    </div>
                </div>
            </section>
        </main>

        {{-- ============================ Footer ============================ --}}
        <footer class="border-t border-zinc-200 bg-zinc-50/70">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-4">
                <div class="md:col-span-2">
                    <x-app-logo />
                    <p class="mt-4 max-w-sm text-sm leading-6 text-zinc-500">{{ $site['footer_tagline'] }}</p>
                    @if (filled($site['contact_email']))
                        <a href="mailto:{{ $site['contact_email'] }}" class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-zinc-700 hover:text-primary-600" dir="ltr">
                            <x-heroicon-o-envelope class="size-4" />
                            {{ $site['contact_email'] }}
                        </a>
                    @endif
                </div>
                @if ($nav !== [])
                    <div>
                        <p class="text-sm font-semibold text-zinc-950">{{ __('Product') }}</p>
                        <ul class="mt-4 space-y-3 text-sm text-zinc-500">
                            @foreach ($nav as [$href, $label])
                                <li><a href="{{ $href }}" class="transition hover:text-zinc-950">{{ $label }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div>
                    <p class="text-sm font-semibold text-zinc-950">{{ __('Account') }}</p>
                    <ul class="mt-4 space-y-3 text-sm text-zinc-500">
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="transition hover:text-zinc-950">{{ __('Dashboard') }}</a></li>
                            <li><a href="{{ route('settings.profile') }}" class="transition hover:text-zinc-950">{{ __('Settings') }}</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="transition hover:text-zinc-950">{{ __('Log in') }}</a></li>
                            <li><a href="{{ route('register') }}" class="transition hover:text-zinc-950">{{ __('Create an account') }}</a></li>
                        @endauth
                    </ul>
                </div>
            </div>
            <div class="border-t border-zinc-200">
                <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-2 px-4 py-6 text-sm text-zinc-500 sm:flex-row sm:px-6">
                    <p>&copy; {{ date('Y') }} {{ config('app.name') }}</p>
                    <p class="flex items-center gap-1.5"><x-heroicon-s-lock-closed class="size-3.5 text-emerald-500" />{{ __('Secured with two-factor authentication and a full audit trail.') }}</p>
                </div>
            </div>
        </footer>

        <x-impersonation-banner />

        <x-flash-alert />

        @livewireScripts
    </body>
</html>
