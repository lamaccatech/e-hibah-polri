<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantFinanceMinistrySubmission extends Model
{
    /** @use HasFactory<\Database\Factories\GrantFinanceMinistrySubmissionFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'informasi_hibah_untuk_sehati';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'penerima_hibah',
        'sumber_pembiayaan',
        'jenis_pembiayaan',
        'cara_penarikan',
        'tanggal_efektif',
        'tanggal_batas_penarikan',
        'tanggal_penutupan_rekening',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_efektif' => 'date',
            'tanggal_batas_penarikan' => 'date',
            'tanggal_penutupan_rekening' => 'date',
        ];
    }

    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'id_hibah');
    }
}
