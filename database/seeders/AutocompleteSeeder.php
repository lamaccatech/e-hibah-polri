<?php

namespace Database\Seeders;

use App\Models\Autocomplete;
use Illuminate\Database\Seeder;

class AutocompleteSeeder extends Seeder
{
    public function run(): void
    {
        $suggestions = [
            ['identifier' => 'mata_uang', 'value' => 'IDR'],
            ['identifier' => 'mata_uang', 'value' => 'USD'],
            ['identifier' => 'sumber_pembiayaan', 'value' => 'Lembaga Multilateral'],
            ['identifier' => 'sumber_pembiayaan', 'value' => 'Lembaga Bilateral'],
            ['identifier' => 'sumber_pembiayaan', 'value' => 'Lembaga Swasta'],
            ['identifier' => 'sumber_pembiayaan', 'value' => 'Perorangan'],
            ['identifier' => 'sumber_pembiayaan', 'value' => 'Pemerintahan'],
            ['identifier' => 'cara_penarikan', 'value' => 'PP'],
            ['identifier' => 'cara_penarikan', 'value' => 'L/C'],
            ['identifier' => 'cara_penarikan', 'value' => 'PL'],
            ['identifier' => 'cara_penarikan', 'value' => 'Reksus'],
            ['identifier' => 'cara_penarikan', 'value' => 'Hibah Lansung'],
            ['identifier' => 'kategori_pemberi_hibah.dalam_negeri', 'value' => 'Lembaga Keuangan Dalam Negeri'],
            ['identifier' => 'kategori_pemberi_hibah.dalam_negeri', 'value' => 'Lembaga Non Keuangan Dalam Negeri'],
            ['identifier' => 'kategori_pemberi_hibah.dalam_negeri', 'value' => 'Pemerintah Daerah'],
            ['identifier' => 'kategori_pemberi_hibah.dalam_negeri', 'value' => 'Perusahaan Asing yang Berdomisili dan Melakukan Kegiatan Usaha di Wilayah Negara Republik Indonesia'],
            ['identifier' => 'kategori_pemberi_hibah.dalam_negeri', 'value' => 'Lembaga Lainnya'],
            ['identifier' => 'kategori_pemberi_hibah.dalam_negeri', 'value' => 'Perorangan'],
            ['identifier' => 'kategori_pemberi_hibah.luar_negeri', 'value' => 'Negara Asing'],
            ['identifier' => 'kategori_pemberi_hibah.luar_negeri', 'value' => 'Lembaga di bawah Perserikatan Bangsa-Bangsa'],
            ['identifier' => 'kategori_pemberi_hibah.luar_negeri', 'value' => 'Lembaga Multilateral'],
            ['identifier' => 'kategori_pemberi_hibah.luar_negeri', 'value' => 'Lembaga Keuangan Asing'],
            ['identifier' => 'kategori_pemberi_hibah.luar_negeri', 'value' => 'Lembaga Non Keuangan Asing'],
            ['identifier' => 'kategori_pemberi_hibah.luar_negeri', 'value' => 'Lembaga Keuangan Nasional yang Berdomisili dan Melakukan Kegiatan Usaha di Luar Wilayah Negara Republik Indonesia'],
            ['identifier' => 'kategori_pemberi_hibah.luar_negeri', 'value' => 'Perorangan'],
        ];

        foreach ($suggestions as $item) {
            Autocomplete::create($item);
        }
    }
}
