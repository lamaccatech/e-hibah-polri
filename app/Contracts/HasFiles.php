<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasFiles
{
    /**
     * Get all files attached to this entity.
     */
    public function files(): MorphMany;
}
