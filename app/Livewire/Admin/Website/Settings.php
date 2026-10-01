<?php

namespace App\Livewire\Admin\Website;

use App\Livewire\Admin\Website\Concerns\ManagesContent;
use App\Livewire\Concerns\InteractsWithAlerts;
use App\Support\Localization;
use App\Support\SiteSettings;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Texts and section switches of the public website.
 */
#[Layout('components.layouts.admin')]
class Settings extends Component
{
    use InteractsWithAlerts, ManagesContent;

    /**
     * @var array<string, mixed>
     */
    public array $values = [];

    public function mount(): void
    {
        $this->authorizeContent();

        $this->fillValues();
    }

    public function save(): void
    {
        $this->authorizeContent();

        $this->validate();

        $values = [];

        foreach (SiteSettings::fields() as $key => $field) {
            $value = $this->values[$key] ?? null;

            $values[$key] = match (true) {
                $field['translatable'] => $this->filledTranslations(is_array($value) ? $value : []) ?: null,
                $field['type'] === 'boolean' => (bool) $value,
                default => filled($value) ? trim((string) $value) : null,
            };
        }

        SiteSettings::save($values);

        $this->recordContentChange('settings', 'updated');

        $this->toast(__('Website content saved.'));
    }

    public function confirmReset(): void
    {
        $this->authorizeContent();

        $this->askForConfirmation(
            __('Restore the default text?'),
            __('Every text on this page goes back to its default, in every language. Features, pricing and questions are not affected.'),
            'restoreDefaults',
            confirmButtonText: __('Restore'),
        );
    }

    public function restoreDefaults(): void
    {
        $this->authorizeContent();

        SiteSettings::reset();
        $this->fillValues();
        $this->resetErrorBag();

        $this->recordContentChange('settings', 'reset');

        $this->toast(__('Default text restored.'));
    }

    /**
     * @return array<string, list<mixed>>
     */
    protected function rules(): array
    {
        $fields = SiteSettings::fields();
        $rules = ['values' => ['required', 'array:'.implode(',', array_keys($fields))]];

        foreach ($fields as $key => $field) {
            $rules += match (true) {
                $field['translatable'] => $this->translatableRules("values.{$key}", $field['max'] ?? 255, required: false),
                $field['type'] === 'boolean' => ["values.{$key}" => ['required', 'boolean']],
                $field['type'] === 'email' => ["values.{$key}" => ['nullable', 'string', 'email', 'max:255']],
                default => ["values.{$key}" => ['nullable', 'string', 'max:'.($field['max'] ?? 255)]],
            };
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        $attributes = [];

        foreach (self::labels() as $key => $label) {
            $attributes["values.{$key}"] = $label;
            $attributes += $this->translatableAttributes("values.{$key}", $label);
        }

        return $attributes;
    }

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'hero_badge' => __('Badge'),
            'hero_title' => __('Headline'),
            'hero_highlight' => __('Highlighted line'),
            'hero_subtitle' => __('Introduction'),
            'hero_primary_cta' => __('Main button'),
            'hero_secondary_cta' => __('Second button'),
            'cta_title' => __('Closing headline'),
            'cta_subtitle' => __('Closing text'),
            'footer_tagline' => __('Footer text'),
            'contact_email' => __('Contact email'),
            'show_stats' => __('Live numbers'),
            'show_features' => __('Features'),
            'show_how_it_works' => __('How it works'),
            'show_pricing' => __('Pricing'),
            'show_faq' => __('FAQ'),
        ];
    }

    protected function fillValues(): void
    {
        $stored = SiteSettings::stored();
        $locales = array_flip(Localization::codes());

        foreach (SiteSettings::fields() as $key => $field) {
            $value = $stored[$key] ?? null;

            $this->values[$key] = match (true) {
                $field['translatable'] => [...$this->emptyTranslations(), ...(is_array($value) ? array_intersect_key($value, $locales) : [])],
                $field['type'] === 'boolean' => (bool) ($value ?? $field['default']),
                default => (string) ($value ?? ''),
            };
        }
    }

    public function render(): View
    {
        return view('livewire.admin.website.settings', [
            'fields' => SiteSettings::fields(),
            'labels' => self::labels(),
            'defaults' => SiteSettings::defaults(),
        ])->title(__('Website content'));
    }
}
