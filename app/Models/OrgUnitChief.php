<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrgUnitChief extends Model
{
    /** @use HasFactory<\Database\Factories\OrgUnitChiefFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'kepala_unit';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama_lengkap',
        'jabatan',
        'pangkat',
        'nrp',
        'tanda_tangan',
        'sedang_menjabat',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sedang_menjabat' => 'boolean',
        ];
    }

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'id_unit', 'id_user');
    }
}
