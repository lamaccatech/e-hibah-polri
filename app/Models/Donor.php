<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Donor extends Model
{
    /** @use HasFactory<\Database\Factories\DonorFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'pemberi_hibah';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'asal',
        'alamat',
        'negara',
        'kode_provinsi',
        'nama_provinsi',
        'kode_kabupaten_kota',
        'nama_kabupaten_kota',
        'kode_kecamatan',
        'nama_kecamatan',
        'kode_desa_kelurahan',
        'nama_desa_kelurahan',
        'nomor_telepon',
        'email',
        'kategori',
    ];

    public function grants(): HasMany
    {
        return $this->hasMany(Grant::class, 'id_pemberi_hibah');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
