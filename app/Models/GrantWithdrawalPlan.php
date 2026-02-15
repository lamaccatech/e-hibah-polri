<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantWithdrawalPlan extends Model
{
    /** @use HasFactory<\Database\Factories\GrantWithdrawalPlanFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'rencana_penarikan_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nomor_urut',
        'uraian',
        'tanggal',
        'nilai',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nilai' => 'decimal:2',
        ];
    }

    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'id_hibah');
    }
}
