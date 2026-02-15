<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantNumbering extends Model
{
    /** @use HasFactory<\Database\Factories\GrantNumberingFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'penomoran_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nomor',
        'kode',
        'nomor_urut',
        'bulan',
        'tahun',
        'tahapan',
        'kode_satuan_kerja',
    ];

    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'id_hibah');
    }
}
