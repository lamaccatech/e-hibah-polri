<?php

namespace App\Concerns;

use App\Models\ChangeHistory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait for models that can have their changes tracked
 *
 * Models using this trait must implement the contract:
 *
 * @see \App\Contracts\HasChangeHistory
 *
 * Usage example:
 * ```
 * use App\Concerns\HasChangeHistory as HasChangeHistoryTrait;
 * use App\Contracts\HasChangeHistory;
 *
 * class Invoice extends Model implements HasChangeHistory
 * {
 *     use HasChangeHistoryTrait;
 * }
 * ```
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasChangeHistory
{
    /**
     * Change history for this model
     */
    public function changes(): MorphMany
    {
        return $this->morphMany(ChangeHistory::class, 'changeable');
    }
}
