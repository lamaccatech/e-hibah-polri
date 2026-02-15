<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasChangeHistory
{
    /**
     * Change history for this model
     */
    public function changes(): MorphMany;
}
