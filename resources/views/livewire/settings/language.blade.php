<x-settings.layout
    :heading="__('Language')"
    :subheading="__('Choose the language used in the interface and in the emails we send you.')"
>
    <form wire:submit="updateLocale" class="space-y-6">
        <fieldset class="grid gap-3">
            <legend class="sr-only">{{ __('Language') }}</legend>

            @foreach (\App\Support\Localization::supported() as $code => $locale)
                <label
                    wire:key="locale-{{ $code }}"
                    class="flex cursor-pointer items-center gap-4 rounded-2xl border border-zinc-200 p-4 transition hover:border-zinc-300 has-checked:border-primary-500 has-checked:bg-primary-500/[0.04] has-checked:ring-4 has-checked:ring-primary-500/10 dark:border-white/10 dark:hover:border-white/20"
                >
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-900/[0.04] font-mono text-xs font-semibold text-zinc-600 uppercase dark:bg-white/[0.06] dark:text-zinc-300">{{ $code }}</span>

                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-medium text-zinc-900 dark:text-white" lang="{{ $code }}">{{ $locale['native'] }}</span>
                        <span class="block text-xs text-zinc-500 dark:text-zinc-400">{{ __($locale['name']) }}</span>
                    </span>

                    @if ($locale['dir'] === 'rtl')
                        <x-ui.badge>{{ __('Right-to-left') }}</x-ui.badge>
                    @endif

                    <input type="radio" wire:model="locale" value="{{ $code }}" class="size-4.5 border-zinc-300 text-primary-600 focus:ring-primary-500 dark:border-white/20 dark:bg-white/5">
                </label>
            @endforeach
        </fieldset>

        <x-ui.error for="locale" />

        <x-ui.button type="submit" loading="updateLocale">{{ __('Save') }}</x-ui.button>
    </form>
</x-settings.layout>
