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
        Schema::create('hibah', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_satuan_kerja');
            $table->foreignId('id_pemberi_hibah')->nullable()->constrained('pemberi_hibah')->nullOnDelete();
            $table->string('nama_hibah');
            $table->string('jenis_hibah');
            $table->string('tahapan');
            $table->string('bentuk_hibah');
            $table->decimal('nilai_hibah', 20, 2)->nullable();
            $table->string('mata_uang')->nullable();
            $table->boolean('ada_usulan')->default(false);
            $table->string('nomor_surat_dari_calon_pemberi_hibah')->nullable();
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->foreign('id_satuan_kerja')->references('id_user')->on('unit')->restrictOnDelete();
            $table->index('id_satuan_kerja');
            $table->index('tahapan');
            $table->index('jenis_hibah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hibah');
    }
};
