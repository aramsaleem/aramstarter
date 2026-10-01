<?php

namespace App\Livewire\Admin\Website\Concerns;

use App\Enums\ActivityEvent;
use App\Enums\SystemPermission;
use App\Support\Audit;
use App\Support\Localization;

/**
 * Shared by the Admin > Website screens.
 */
trait ManagesContent
{
    /**
     * Checked on every action, not only when the page loads.
     */
    protected function authorizeContent(): void
    {
        $this->authorize(SystemPermission::ManageContent->value);
    }

    /**
     * Rules for a field with one value per language. With $required, the default language
     * must be filled in; the other languages are optional and fall back to it.
     *
     * @return array<string, list<string>>
     */
    protected function translatableRules(string $field, int $max, bool $required = true): array
    {
        $locales = Localization::codes();

        // "array:en,ar,ckb" rejects any key that isn't a supported language.
        $rules = [$field => [$required ? 'required' : 'nullable', 'array:'.implode(',', $locales)]];

        foreach ($locales as $index => $locale) {
            $rules["{$field}.{$locale}"] = [$required && $index === 0 ? 'required' : 'nullable', 'string', "max:{$max}"];
        }

        return $rules;
    }

    /**
     * Readable names for validation messages, e.g. "Title (العربية)".
     *
     * @return array<string, string>
     */
    protected function translatableAttributes(string $field, string $label): array
    {
        $attributes = [];

        foreach (Localization::codes() as $locale) {
            $attributes["{$field}.{$locale}"] = $label.' ('.Localization::nativeName($locale).')';
        }

        return $attributes;
    }

    /**
     * @return array<string, string>
     */
    protected function emptyTranslations(): array
    {
        return array_fill_keys(Localization::codes(), '');
    }

    /**
     * Trimmed translations without the empty ones, so those languages fall back to the default.
     *
     * @param  array<string, mixed>  $translations
     * @return array<string, string>
     */
    protected function filledTranslations(array $translations): array
    {
        $filled = [];

        foreach (Localization::codes() as $locale) {
            $value = trim((string) ($translations[$locale] ?? ''));

            if ($value !== '') {
                $filled[$locale] = $value;
            }
        }

        return $filled;
    }

    protected function recordContentChange(string $section, string $action, ?string $label = null): void
    {
        Audit::log(ActivityEvent::ContentUpdated, properties: array_filter([
            'section' => $section,
            'action' => $action,
            'label' => $label,
        ]));
    }

    /**
     * -1 (up) or 1 (down), whatever the client sent.
     */
    protected function direction(int $direction): int
    {
        return $direction < 0 ? -1 : 1;
    }
}
