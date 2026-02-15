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
        Schema::create('isi_pengkajian_hibah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengkajian_hibah')->constrained('pengkajian_hibah')->cascadeOnDelete();
            $table->string('subjudul');
            $table->text('isi');
            $table->unsignedSmallInteger('nomor_urut');
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->index('id_pengkajian_hibah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isi_pengkajian_hibah');
    }
};
