<?php

namespace App\Models;

use App\Models\Concerns\Sortable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * A question and answer on the public website.
 */
class Faq extends Model
{
    use HasTranslations, Sortable;

    /**
     * @var list<string>
     */
    public array $translatable = ['question', 'answer'];

    /**
     * @var list<string>
     */
    protected $fillable = ['question', 'answer', 'sort_order', 'is_active'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
