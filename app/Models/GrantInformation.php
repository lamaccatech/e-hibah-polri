<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantInformation extends Model
{
    /** @use HasFactory<\Database\Factories\GrantInformationFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'informasi_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'judul',
        'tahapan',
    ];

    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'id_hibah');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(GrantInformationContent::class, 'id_informasi_hibah');
    }
}
