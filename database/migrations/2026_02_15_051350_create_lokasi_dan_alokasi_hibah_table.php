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
        Schema::create('lokasi_dan_alokasi_hibah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_hibah')->constrained('hibah')->cascadeOnDelete();
            $table->string('lokasi');
            $table->decimal('alokasi', 20, 2);
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
        Schema::dropIfExists('lokasi_dan_alokasi_hibah');
    }
};
