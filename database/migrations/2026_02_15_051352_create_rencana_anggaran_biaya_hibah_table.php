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
        Schema::create('rencana_anggaran_biaya_hibah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_hibah')->constrained('hibah')->cascadeOnDelete();
            $table->unsignedInteger('nomor_urut');
            $table->text('uraian');
            $table->decimal('nilai', 20, 2);
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
        Schema::dropIfExists('rencana_anggaran_biaya_hibah');
    }
};
