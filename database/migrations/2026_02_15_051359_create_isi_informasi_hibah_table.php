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
        Schema::create('isi_informasi_hibah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_informasi_hibah')->constrained('informasi_hibah')->cascadeOnDelete();
            $table->string('subjudul');
            $table->text('isi');
            $table->unsignedSmallInteger('nomor_urut');
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->index('id_informasi_hibah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('isi_informasi_hibah');
    }
};
