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
        Schema::create('riwayat_perubahan_status_hibah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_hibah')->nullable()->constrained('hibah')->nullOnDelete();
            $table->string('status_sebelum')->nullable();
            $table->string('status_sesudah');
            $table->text('keterangan')->nullable();
            $table->timestampsTz();

            $table->index('id_hibah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_perubahan_status_hibah');
    }
};
