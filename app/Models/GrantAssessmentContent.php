<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantAssessmentContent extends Model
{
    /** @use HasFactory<\Database\Factories\GrantAssessmentContentFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'isi_pengkajian_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'subjudul',
        'isi',
        'nomor_urut',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(GrantAssessment::class, 'id_pengkajian_hibah');
    }
}
