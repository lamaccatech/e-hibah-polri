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
        Schema::create('informasi_hibah_untuk_sehati', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_hibah')->unique()->constrained('hibah')->cascadeOnDelete();
            $table->string('penerima_hibah');
            $table->string('sumber_pembiayaan');
            $table->string('jenis_pembiayaan');
            $table->string('cara_penarikan');
            $table->date('tanggal_efektif');
            $table->date('tanggal_batas_penarikan');
            $table->date('tanggal_penutupan_rekening');
            $table->softDeletesTz();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informasi_hibah_untuk_sehati');
    }
};
