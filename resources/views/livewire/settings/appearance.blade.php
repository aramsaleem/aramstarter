<x-settings.layout
    :heading="__('Appearance')"
    :subheading="__('Choose how the interface looks. Your choice is saved in this browser.')"
>
    <div class="grid gap-4 sm:grid-cols-3" role="radiogroup" aria-label="{{ __('Theme') }}">
        @foreach (['light' => [__('Light'), 'sun'], 'dark' => [__('Dark'), 'moon'], 'system' => [__('System'), 'computer-desktop']] as $mode => [$label, $icon])
            <button
                type="button"
                role="radio"
                x-data
                x-on:click="$store.appearance.set('{{ $mode }}')"
                x-bind:aria-checked="$store.appearance.mode === '{{ $mode }}'"
                x-bind:class="$store.appearance.mode === '{{ $mode }}'
                    ? 'border-primary-500 ring-4 ring-primary-500/15'
                    : 'border-zinc-200 hover:border-zinc-300 dark:border-white/10 dark:hover:border-white/20'"
                class="group rounded-2xl border bg-white p-2.5 text-start transition dark:bg-white/[0.02]"
            >
                {{-- Miniature preview of the theme --}}
                <div @class([
                    'relative h-24 overflow-hidden rounded-xl',
                    'bg-zinc-100' => $mode === 'light',
                    'bg-zinc-900' => $mode === 'dark',
                    'bg-linear-to-br from-zinc-100 from-50% to-zinc-900 to-50%' => $mode === 'system',
                ])>
                    <div @class([
                        'absolute inset-y-2.5 start-2.5 w-7 rounded-lg',
                        'bg-white shadow-sm' => $mode === 'light',
                        'bg-zinc-800' => $mode === 'dark',
                        'bg-white/80' => $mode === 'system',
                    ])></div>
                    <div class="absolute inset-x-0 top-3 ms-12 me-3 space-y-2">
                        <div class="h-2.5 w-3/4 rounded-full bg-linear-to-r from-primary-500 to-accent-400"></div>
                        <div @class(['h-2 w-1/2 rounded-full', 'bg-zinc-300' => $mode !== 'dark', 'bg-zinc-700' => $mode === 'dark'])></div>
                        <div @class(['h-2 w-2/3 rounded-full', 'bg-zinc-300' => $mode === 'light', 'bg-zinc-700' => $mode !== 'light'])></div>
                    </div>
                </div>

                <span class="flex items-center gap-2 px-1.5 pt-3 pb-1 text-sm font-medium text-zinc-900 dark:text-white">
                    <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-4 text-zinc-400" />
                    <span class="flex-1">{{ $label }}</span>
                    <x-heroicon-s-check-circle class="size-5 text-primary-500" x-cloak x-show="$store.appearance.mode === '{{ $mode }}'" />
                </span>
            </button>
        @endforeach
    </div>
</x-settings.layout>
