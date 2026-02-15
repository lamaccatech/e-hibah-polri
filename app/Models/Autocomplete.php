<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Autocomplete extends Model
{
    /** @use HasFactory<\Database\Factories\AutocompleteFactory> */
    use HasFactory;

    protected $table = 'autocomplete';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'identifier',
        'value',
    ];
}
