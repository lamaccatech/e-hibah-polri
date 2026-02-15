<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantLocationAndAllocation extends Model
{
    /** @use HasFactory<\Database\Factories\GrantLocationAndAllocationFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'lokasi_dan_alokasi_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'lokasi',
        'alokasi',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'alokasi' => 'decimal:2',
        ];
    }

    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'id_hibah');
    }
}
