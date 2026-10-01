{{--
    One text field per language, switched with tabs.
    <x-ui.translatable model="title" :label="__('Title')" required />
    <x-ui.translatable model="values.hero_subtitle" type="textarea" :placeholders="['en' => '...', 'ar' => '...']" />
    "required" marks the default language as required; the others fall back to it.
--}}
@props([
    'model',
    'label',
    'type' => 'text',
    'rows' => 3,
    'description' => null,
    'placeholders' => [],
    'required' => false,
    'maxlength' => null,
])

@php
    use App\Support\Localization;

    $locales = Localization::codes();
    $id = 'tr-'.str_replace('.', '-', $model);
    $initial = collect($locales)->first(fn (string $locale) => $errors->has("{$model}.{$locale}")) ?? $locales[0];
    $fieldClasses = 'block w-full rounded-xl border-0 bg-white px-3.5 text-sm text-zinc-900 shadow-xs ring-1 ring-inset transition-shadow placeholder:text-zinc-400 focus:ring-2 focus:ring-inset dark:bg-white/[0.03] dark:text-white dark:placeholder:text-zinc-500';
@endphp

<div {{ $attributes->class('space-y-2') }} x-data="{ locale: @js($initial) }">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <label x-bind:for="@js($id.'-').concat(locale)" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
            {{ $label }}
            @if ($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>

        <div class="inline-flex rounded-lg bg-zinc-100 p-0.5 dark:bg-white/[0.05]" role="tablist" aria-label="{{ __('Language') }}">
            @foreach ($locales as $locale)
                <button
                    type="button"
                    role="tab"
                    x-on:click="locale = @js($locale)"
                    x-bind:aria-selected="locale === @js($locale)"
                    x-bind:class="locale === @js($locale) ? 'bg-white text-zinc-900 shadow-xs dark:bg-white/10 dark:text-white' : 'text-zinc-500 hover:text-zinc-800 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    class="relative rounded-md px-2.5 py-1 text-xs font-medium transition"
                    title="{{ Localization::nativeName($locale) }}"
                >
                    <span class="uppercase" dir="ltr">{{ $locale }}</span>

                    @error("{$model}.{$locale}")
                        <span class="absolute -end-0.5 -top-0.5 size-2 rounded-full bg-red-500 ring-2 ring-white dark:ring-zinc-900"></span>
                        <span class="sr-only">{{ __('Has errors') }}</span>
                    @enderror
                </button>
            @endforeach
        </div>
    </div>

    @foreach ($locales as $locale)
        @php
            $field = "{$model}.{$locale}";
            $invalid = $errors->has($field);
            $state = $invalid
                ? 'ring-red-400 focus:ring-red-500 dark:ring-red-500/60'
                : 'ring-zinc-200 focus:shadow-[0_0_0_4px_rgb(139_92_246/0.12)] focus:ring-primary-500 dark:ring-white/10 dark:focus:ring-primary-400';
        @endphp

        <div x-show="locale === @js($locale)" @if ($locale !== $initial) x-cloak @endif wire:key="{{ $id }}-{{ $locale }}">
            @if ($type === 'textarea')
                <textarea
                    id="{{ $id }}-{{ $locale }}"
                    wire:model="{{ $field }}"
                    rows="{{ $rows }}"
                    lang="{{ $locale }}"
                    dir="{{ Localization::direction($locale) }}"
                    placeholder="{{ $placeholders[$locale] ?? '' }}"
                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                    @if ($invalid) aria-invalid="true" @endif
                    class="{{ $fieldClasses }} {{ $state }} py-2.5 leading-6"
                ></textarea>
            @else
                <input
                    type="text"
                    id="{{ $id }}-{{ $locale }}"
                    wire:model="{{ $field }}"
                    lang="{{ $locale }}"
                    dir="{{ Localization::direction($locale) }}"
                    placeholder="{{ $placeholders[$locale] ?? '' }}"
                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                    @if ($invalid) aria-invalid="true" @endif
                    autocomplete="off"
                    class="{{ $fieldClasses }} {{ $state }} h-11"
                >
            @endif

            <x-ui.error :for="$field" class="mt-2" />
        </div>
    @endforeach

    @if ($description)
        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif
</div>
