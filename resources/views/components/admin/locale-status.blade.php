{{-- Which languages a translatable field is filled in: <x-admin.locale-status :model="$feature" field="title" /> --}}
@props(['model', 'field'])

@php
    use App\Support\Localization;
@endphp

<div {{ $attributes->class('flex items-center gap-1') }}>
    @foreach (Localization::codes() as $locale)
        @php $translated = filled($model->getTranslation($field, $locale, false)); @endphp
        <span
            dir="ltr"
            title="{{ Localization::nativeName($locale) }}: {{ $translated ? __('Translated') : __('Uses the default language') }}"
            @class([
                'rounded-md px-1.5 py-px text-[0.62rem] font-semibold uppercase ring-1 ring-inset',
                'bg-emerald-500/10 text-emerald-700 ring-emerald-500/20 dark:text-emerald-300' => $translated,
                'bg-amber-500/10 text-amber-700 ring-amber-500/25 dark:text-amber-300' => ! $translated,
            ])
        >
            {{ $locale }}
            <span class="sr-only">{{ $translated ? __('Translated') : __('Uses the default language') }}</span>
        </span>
    @endforeach
</div>
