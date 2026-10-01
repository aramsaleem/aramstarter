<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One editable website setting. See App\Support\SiteSettings for the available keys.
 */
class SiteSetting extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = ['key', 'value'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }
}
