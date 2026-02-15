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
        Schema::create('jadwal_pelaksanaan_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_hibah')->constrained('hibah')->cascadeOnDelete();
            $table->text('uraian_kegiatan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->index('id_hibah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelaksanaan_kegiatan');
    }
};
