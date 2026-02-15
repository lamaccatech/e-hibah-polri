<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantAssessment extends Model
{
    /** @use HasFactory<\Database\Factories\GrantAssessmentFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'pengkajian_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'judul',
        'aspek',
        'tahapan',
    ];

    public function statusHistory(): BelongsTo
    {
        return $this->belongsTo(GrantStatusHistory::class, 'id_riwayat_perubahan_status_hibah');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(GrantAssessmentContent::class, 'id_pengkajian_hibah');
    }

    public function result(): HasOne
    {
        return $this->hasOne(GrantAssessmentResult::class, 'id_pengkajian_hibah');
    }
}
