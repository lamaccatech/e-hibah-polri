<?php

namespace App\Models;

use App\Concerns\HasFiles as HasFilesTrait;
use App\Contracts\HasFiles;
use App\Enums\GrantStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrantStatusHistory extends Model implements HasFiles
{
    /** @use HasFactory<\Database\Factories\GrantStatusHistoryFactory> */
    use HasFactory, HasFilesTrait;

    protected $table = 'riwayat_perubahan_status_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'status_sebelum',
        'status_sesudah',
        'keterangan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_sebelum' => GrantStatus::class,
            'status_sesudah' => GrantStatus::class,
        ];
    }

    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'id_hibah');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(GrantAssessment::class, 'id_riwayat_perubahan_status_hibah');
    }
}
