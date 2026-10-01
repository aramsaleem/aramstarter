<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Manual ordering through a "sort_order" column, plus an "is_active" switch.
 */
trait Sortable
{
    public static function bootSortable(): void
    {
        static::creating(function ($model) {
            if (! $model->sort_order) {
                $model->sort_order = (int) static::max('sort_order') + 1;
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @param  Builder<static>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Swap places with the neighbour above (-1) or below (+1).
     */
    public function move(int $direction): void
    {
        $neighbour = static::query()
            ->where('sort_order', $direction < 0 ? '<' : '>', $this->sort_order)
            ->orderBy('sort_order', $direction < 0 ? 'desc' : 'asc')
            ->first();

        if (! $neighbour) {
            return;
        }

        [$this->sort_order, $neighbour->sort_order] = [$neighbour->sort_order, $this->sort_order];

        $this->save();
        $neighbour->save();
    }
}
