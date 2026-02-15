<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgUnit extends Model
{
    /** @use HasFactory<\Database\Factories\OrgUnitFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'unit';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_unit_atasan',
        'kode',
        'nama_unit',
        'level_unit',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_unit_atasan', 'id_user');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'id_unit_atasan', 'id_user');
    }

    public function chiefs(): HasMany
    {
        return $this->hasMany(OrgUnitChief::class, 'id_unit', 'id_user');
    }

    public function grants(): HasMany
    {
        return $this->hasMany(Grant::class, 'id_satuan_kerja', 'id_user');
    }
}
