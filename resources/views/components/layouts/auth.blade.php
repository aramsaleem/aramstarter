@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ \App\Support\Localization::direction() }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-canvas font-sans text-zinc-900 antialiased dark:bg-ink dark:text-zinc-100">
        <div class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)]">
            {{-- Form side --}}
            <div class="relative flex flex-col">
                <x-aurora subtle class="absolute inset-0 -z-10 lg:hidden" />

                <header class="flex items-center justify-between px-6 py-5 sm:px-10">
                    <a href="{{ route('home') }}" wire:navigate>
                        <x-app-logo />
                    </a>

                    <div class="flex items-center">
                        <x-locale-switcher />
                        <x-appearance-toggle />
                    </div>
                </header>

                <main class="flex flex-1 items-center justify-center px-6 pt-6 pb-16 sm:px-10">
                    <div class="animate-fade-up w-full max-w-[25rem]">
                        {{ $slot }}
                    </div>
                </main>

                <footer class="px-6 pb-6 text-xs text-zinc-400 sm:px-10 dark:text-zinc-500">
                    &copy; {{ date('Y') }} {{ config('app.name') }}
                </footer>
            </div>

            {{-- Showcase side --}}
            <div class="relative m-3 hidden overflow-hidden rounded-[2rem] bg-linear-to-br from-primary-100 via-white to-cyan-100 ring-1 ring-primary-100 lg:block dark:from-primary-500/15 dark:via-ink dark:to-cyan-500/10 dark:ring-white/10">
                <x-aurora class="absolute inset-0" />

                <div class="relative flex h-full flex-col justify-between p-12 text-zinc-900 dark:text-white">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-xs font-medium text-zinc-700 shadow-sm ring-1 ring-zinc-200/80 backdrop-blur dark:bg-white/5 dark:text-white/80 dark:ring-white/15">
                        <span class="size-1.5 rounded-full bg-emerald-500 shadow-[0_0_10px_2px_rgb(16_185_129/0.45)]"></span>
                        {{ __('Secure by default') }}
                    </span>

                    {{-- Floating product preview --}}
                    <div aria-hidden="true" class="relative mx-auto w-full max-w-md">
                        <div class="rounded-3xl bg-white/85 p-5 shadow-[0_30px_80px_-30px_rgb(76_29_149/0.45)] ring-1 ring-zinc-200/80 backdrop-blur-xl dark:bg-white/[0.06] dark:ring-white/10">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-white/50">{{ __('Total users') }}</p>
                                    <p class="mt-1 text-3xl font-semibold tracking-tight">12,480</p>
                                </div>
                                <span class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-xs font-semibold text-emerald-700 dark:text-emerald-300">+18%</span>
                            </div>

                            <div class="mt-6 flex h-24 items-end gap-1.5">
                                @foreach ([35, 48, 40, 62, 55, 70, 64, 82, 76, 90, 84, 100] as $height)
                                    <span class="flex-1 rounded-t bg-linear-to-t from-primary-300 to-primary-500 dark:from-primary-500/60 dark:to-accent-400/80" style="height: {{ $height }}%"></span>
                                @endforeach
                            </div>
                        </div>

                        <div class="absolute -start-8 -bottom-10 flex items-center gap-3 rounded-2xl bg-white p-3 pe-5 shadow-xl ring-1 ring-zinc-200/80 dark:bg-zinc-900/80 dark:ring-white/10">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-500/20 dark:text-primary-300">
                                <x-heroicon-o-finger-print class="size-5" />
                            </span>
                            <span>
                                <span class="block text-sm font-medium">{{ __('Two-factor authentication') }}</span>
                                <span class="block text-xs font-medium text-emerald-600 dark:text-emerald-300">{{ __('Enabled') }}</span>
                            </span>
                        </div>

                        <div class="absolute -end-6 -top-8 flex items-center gap-2 rounded-2xl bg-white px-3 py-2 shadow-xl ring-1 ring-zinc-200/80 dark:bg-zinc-900/80 dark:ring-white/10">
                            <x-heroicon-o-language class="size-4 text-cyan-600 dark:text-accent-300" />
                            <span class="text-xs font-medium">English · العربية · کوردی</span>
                        </div>
                    </div>

                    <div>
                        <h2 class="max-w-md text-3xl leading-tight font-semibold tracking-tight text-balance">
                            {{ __('Everything your next Laravel app needs, from day one') }}
                        </h2>
                        <p class="mt-3 max-w-md text-sm leading-6 text-zinc-600 dark:text-white/60">
                            {{ __('Authentication, two-factor security, social login, roles and permissions, localization and an admin panel - ready to build on.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <x-impersonation-banner />

        <x-flash-alert />

        @livewireScripts
    </body>
</html>
