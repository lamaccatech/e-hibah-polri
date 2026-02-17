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
        Schema::create('kepala_unit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_unit')->nullable();
            $table->string('nama_lengkap');
            $table->string('jabatan');
            $table->string('pangkat');
            $table->string('nrp');
            $table->string('tanda_tangan')->nullable();
            $table->boolean('sedang_menjabat')->default(false);
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->foreign('id_unit')->references('id_user')->on('unit')->nullOnDelete();
            $table->index('id_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kepala_unit');
    }
};
