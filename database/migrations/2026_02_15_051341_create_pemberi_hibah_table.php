<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pemberi_hibah', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('asal');
            $table->text('alamat');
            $table->string('negara');
            $table->string('kode_provinsi')->nullable();
            $table->string('nama_provinsi')->nullable();
            $table->string('kode_kabupaten_kota')->nullable();
            $table->string('nama_kabupaten_kota')->nullable();
            $table->string('kode_kecamatan')->nullable();
            $table->string('nama_kecamatan')->nullable();
            $table->string('kode_desa_kelurahan')->nullable();
            $table->string('nama_desa_kelurahan')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->string('email')->nullable();
            $table->string('kategori')->nullable();
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemberi_hibah');
    }
};
