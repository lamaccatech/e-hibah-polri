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
        Schema::create('informasi_hibah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_hibah')->constrained('hibah')->cascadeOnDelete();
            $table->string('judul');
            $table->string('tahapan');
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
        Schema::dropIfExists('informasi_hibah');
    }
};
