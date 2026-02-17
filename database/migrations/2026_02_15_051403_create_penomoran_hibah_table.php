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
        Schema::create('penomoran_hibah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_hibah')->nullable()->constrained('hibah')->nullOnDelete();
            $table->string('nomor');
            $table->string('kode');
            $table->unsignedSmallInteger('nomor_urut');
            $table->string('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->string('tahapan');
            $table->string('kode_satuan_kerja');
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->index('id_hibah');
            $table->index('tahapan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penomoran_hibah');
    }
};
