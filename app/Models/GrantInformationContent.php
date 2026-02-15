<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantInformationContent extends Model
{
    /** @use HasFactory<\Database\Factories\GrantInformationContentFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isi_informasi_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'subjudul',
        'isi',
        'nomor_urut',
    ];

    public function information(): BelongsTo
    {
        return $this->belongsTo(GrantInformation::class, 'id_informasi_hibah');
    }
}
