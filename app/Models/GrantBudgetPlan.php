<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantBudgetPlan extends Model
{
    /** @use HasFactory<\Database\Factories\GrantBudgetPlanFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'rencana_anggaran_biaya_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nomor_urut',
        'uraian',
        'nilai',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nilai' => 'decimal:2',
        ];
    }

    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'id_hibah');
    }
}
