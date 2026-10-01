@php
    use App\Support\Localization;

    $sections = [
        'hero' => [__('Hero'), __('The first thing visitors see.'), 'sparkles', ['hero_badge', 'hero_title', 'hero_highlight', 'hero_subtitle', 'hero_primary_cta', 'hero_secondary_cta']],
        'cta' => [__('Closing call to action'), __('The last push before the footer.'), 'megaphone', ['cta_title', 'cta_subtitle']],
        'general' => [__('Footer & contact'), __('Shown at the bottom of the website.'), 'envelope', ['footer_tagline', 'contact_email']],
    ];

    $toggles = [
        'show_stats' => __('Numbers counted from your real data.'),
        'show_features' => __('The feature cards.'),
        'show_how_it_works' => __('Three steps with the terminal.'),
        'show_pricing' => __('Your pricing plans.'),
        'show_faq' => __('Questions and answers.'),
    ];

    $directions = collect(Localization::codes())->mapWithKeys(fn (string $locale) => [$locale => Localization::direction($locale)]);
@endphp

<div>
    <x-ui.page-header :eyebrow="__('Website')" :title="__('Website content')" :description="__('Edit the texts on your public website in every language. Empty fields use the default text.')">
        <x-slot:actions>
            <x-ui.button variant="secondary" icon="arrow-uturn-left" wire:click="confirmReset">{{ __('Restore defaults') }}</x-ui.button>
            <x-ui.button type="submit" form="website-settings" icon="check" loading="save">{{ __('Save changes') }}</x-ui.button>
        </x-slot:actions>
    </x-ui.page-header>

    <x-admin.website-nav />

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_24rem]">
        <form id="website-settings" wire:submit="save" class="space-y-6">
            @foreach ($sections as $key => [$title, $description, $icon, $keys])
                <x-ui.card :padding="false" wire:key="section-{{ $key }}">
                    <div class="flex items-center gap-3 border-b border-zinc-200/80 px-6 py-4 dark:border-white/[0.07]">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-primary-500/10 text-primary-600 ring-1 ring-primary-500/20 dark:text-primary-300">
                            <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-4.5" />
                        </span>
                        <div>
                            <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $title }}</h2>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
                        </div>
                    </div>

                    <div class="grid gap-6 p-6 sm:grid-cols-2">
                        @foreach ($keys as $field)
                            @php $definition = $fields[$field]; @endphp

                            @if ($definition['translatable'])
                                <x-ui.translatable
                                    :model="'values.'.$field"
                                    :label="$labels[$field]"
                                    :type="$definition['type'] === 'textarea' ? 'textarea' : 'text'"
                                    :placeholders="$defaults[$field] ?? []"
                                    :maxlength="$definition['max'] ?? null"
                                    :class="$definition['type'] === 'textarea' || ! str_ends_with($field, '_cta') ? 'sm:col-span-2' : ''"
                                />
                            @else
                                <x-ui.input
                                    wire:model="values.{{ $field }}"
                                    type="email"
                                    :label="$labels[$field]"
                                    :description="__('Shown in the footer. Leave empty to hide it.')"
                                    icon="envelope"
                                    placeholder="hello@example.com"
                                    dir="ltr"
                                    class="sm:col-span-2"
                                />
                            @endif
                        @endforeach
                    </div>
                </x-ui.card>
            @endforeach
        </form>

        <aside class="space-y-6 xl:sticky xl:top-24">
            {{-- Live preview of the hero, in any language --}}
            <x-ui.card :padding="false" class="overflow-hidden">
                <div x-data="{
                    locale: @js(app()->getLocale()),
                    defaults: @js($defaults),
                    directions: @js($directions),
                    text(key) {
                        const value = this.$wire.values[key]?.[this.locale]
                        return (value && value.trim()) || this.defaults[key]?.[this.locale] || ''
                    },
                }">
                <div class="flex items-center justify-between gap-3 border-b border-zinc-200/80 px-5 py-3 dark:border-white/[0.07]">
                    <p class="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                        <span class="relative flex size-2">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                            <span class="relative inline-flex size-2 rounded-full bg-emerald-500"></span>
                        </span>
                        {{ __('Live preview') }}
                    </p>

                    <div class="inline-flex rounded-lg bg-zinc-100 p-0.5 dark:bg-white/[0.05]" role="group" aria-label="{{ __('Preview language') }}">
                        @foreach (Localization::codes() as $locale)
                            <button type="button" x-on:click="locale = @js($locale)"
                                x-bind:aria-pressed="locale === @js($locale)"
                                x-bind:class="locale === @js($locale) ? 'bg-white text-zinc-900 shadow-xs dark:bg-white/10 dark:text-white' : 'text-zinc-500 dark:text-zinc-400'"
                                class="rounded-md px-2 py-0.5 text-[0.7rem] font-medium uppercase transition" dir="ltr">{{ $locale }}</button>
                        @endforeach
                    </div>
                </div>

                <div class="relative isolate overflow-hidden bg-white px-5 py-8 text-center text-zinc-950" x-bind:dir="directions[locale]" x-bind:lang="locale">
                    <div aria-hidden="true" class="absolute start-1/2 -top-24 -z-10 h-48 w-80 -translate-x-1/2 rounded-full bg-[radial-gradient(closest-side,rgb(167_139_250/0.45),transparent)] rtl:translate-x-1/2"></div>
                    <div aria-hidden="true" class="bg-grid absolute inset-0 -z-10 [--grid-line:rgb(24_24_27/0.05)]"></div>

                    <span class="inline-flex max-w-full items-center gap-1.5 rounded-full bg-white px-2.5 py-1 text-[0.65rem] text-zinc-700 shadow-xs ring-1 ring-zinc-200">
                        <span class="size-1 shrink-0 rounded-full bg-primary-500"></span>
                        <span class="truncate" x-text="text('hero_badge')"></span>
                    </span>

                    <p class="mt-4 text-2xl leading-tight font-semibold tracking-tight text-balance">
                        <span x-text="text('hero_title')"></span><br>
                        <span class="text-gradient-vivid" x-text="text('hero_highlight')"></span>
                    </p>

                    <p class="mx-auto mt-3 line-clamp-3 max-w-xs text-xs leading-5 text-zinc-600" x-text="text('hero_subtitle')"></p>

                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                        <span class="rounded-full bg-primary-600 px-3.5 py-1.5 text-[0.7rem] font-semibold text-white" x-text="text('hero_primary_cta')"></span>
                        <span class="rounded-full bg-white px-3.5 py-1.5 text-[0.7rem] font-semibold text-zinc-900 shadow-xs ring-1 ring-zinc-200" x-text="text('hero_secondary_cta')"></span>
                    </div>
                </div>
                </div>
            </x-ui.card>

            {{-- Section switches --}}
            <x-ui.card>
                <h2 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ __('Sections') }}</h2>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Hide a section without deleting its content.') }}</p>

                <div class="mt-5 space-y-4">
                    @foreach ($toggles as $field => $description)
                        <x-ui.switch wire:model="values.{{ $field }}" :label="$labels[$field]" :description="$description" form="website-settings" />
                    @endforeach
                </div>
            </x-ui.card>
        </aside>
    </div>

    {{-- Floating save bar while there are unsaved changes --}}
    <div wire:dirty.class="translate-y-0! opacity-100!" wire:dirty.class.remove="pointer-events-none" class="pointer-events-none fixed inset-x-4 bottom-4 z-40 mx-auto flex max-w-lg translate-y-4 items-center justify-between gap-4 rounded-2xl border border-zinc-200 bg-white/90 p-2 ps-4 opacity-0 shadow-2xl shadow-zinc-950/10 backdrop-blur-xl transition duration-300 dark:border-white/10 dark:bg-zinc-900/90">
        <p class="flex items-center gap-2 text-sm font-medium text-zinc-700 dark:text-zinc-200">
            <x-heroicon-o-pencil-square class="size-4 text-amber-500" />
            {{ __('You have unsaved changes.') }}
        </p>
        <x-ui.button type="submit" form="website-settings" size="sm" loading="save">{{ __('Save changes') }}</x-ui.button>
    </div>
</div>
