<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            'ALMATSUS',
            'OPERASIONAL POLRI',
            'KEGIATAN KEPOLISIAN',
            'KEGIATAN RUTIN KEPOLISIAN',
            'KEGIATAN HARKAMTIBMAS',
            'KEGIATAN PENGAMANAN PILKADA',
            'PENGADAAN ALAT KANTOR',
            'PENGADAAN KENDARAAN KHUSUS',
            'BELANJA MODAL',
        ];

        foreach ($tags as $item) {
            Tag::create(['name' => $item]);
        }
    }
}
