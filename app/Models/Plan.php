<?php

namespace App\Models;

use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;
use Spatie\Translatable\HasTranslations;

/**
 * A pricing plan on the public website.
 */
class Plan extends Model
{
    use HasTranslations, Sortable;

    public const CURRENCIES = ['USD', 'EUR', 'GBP', 'IQD', 'TRY', 'AED'];

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'description', 'features', 'cta_label'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name', 'description', 'features', 'cta_label', 'cta_url',
        'price_monthly', 'price_yearly', 'currency', 'is_featured', 'sort_order', 'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_yearly' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * The plan's feature bullet points: one per line in the current language.
     *
     * @return list<string>
     */
    public function featureList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->features))));
    }

    /**
     * Monthly price when billed yearly, or null when the plan has no yearly price.
     */
    public function yearlyMonthlyPrice(): ?float
    {
        return $this->price_yearly === null ? null : round((float) $this->price_yearly / 12, 2);
    }

    public function formatPrice(float|string|null $amount): string
    {
        $amount = (float) $amount;

        return Number::currency($amount, in: $this->currency, locale: 'en', precision: fmod($amount, 1.0) === 0.0 ? 0 : 2);
    }
}
