<?php

namespace App\Models;

use App\Enums\AssessmentResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantAssessmentResult extends Model
{
    /** @use HasFactory<\Database\Factories\GrantAssessmentResultFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'hasil_pengkajian_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'rekomendasi',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rekomendasi' => AssessmentResult::class,
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(GrantAssessment::class, 'id_pengkajian_hibah');
    }

    public function orgUnit(): BelongsTo
    {
        return $this->belongsTo(OrgUnit::class, 'id_unit', 'id_user');
    }
}
