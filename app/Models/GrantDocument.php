<?php

namespace App\Models;

use App\Concerns\HasFiles as HasFilesTrait;
use App\Contracts\HasFiles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrantDocument extends Model implements HasFiles
{
    /** @use HasFactory<\Database\Factories\GrantDocumentFactory> */
    use HasFactory, HasFilesTrait, SoftDeletes;

    protected $table = 'dokumen_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama_dokumen',
        'tahapan',
        'jenis_berkas',
    ];

    public function grant(): BelongsTo
    {
        return $this->belongsTo(Grant::class, 'id_hibah');
    }
}
