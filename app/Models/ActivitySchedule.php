<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivitySchedule extends Model
{
    /** @use HasFactory<\Database\Factories\ActivityScheduleFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'jadwal_pelaksanaan_kegiatan';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uraian_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'id_hibah');
    }
}
