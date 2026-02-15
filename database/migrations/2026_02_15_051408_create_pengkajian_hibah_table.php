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
        Schema::create('pengkajian_hibah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_riwayat_perubahan_status_hibah')->constrained('riwayat_perubahan_status_hibah')->cascadeOnDelete();
            $table->string('judul');
            $table->string('aspek')->nullable();
            $table->string('tahapan');
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->index('id_riwayat_perubahan_status_hibah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengkajian_hibah');
    }
};
