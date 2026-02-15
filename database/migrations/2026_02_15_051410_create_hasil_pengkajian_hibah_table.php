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
        Schema::create('hasil_pengkajian_hibah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengkajian_hibah')->constrained('pengkajian_hibah')->cascadeOnDelete();
            $table->unsignedBigInteger('id_unit')->nullable();
            $table->string('rekomendasi');
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->foreign('id_unit')->references('id_user')->on('unit')->nullOnDelete();
            $table->index('id_pengkajian_hibah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_pengkajian_hibah');
    }
};
