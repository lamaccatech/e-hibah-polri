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
        Schema::create('unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->unique()->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('id_unit_atasan')->nullable();
            $table->string('kode');
            $table->string('nama_unit');
            $table->string('level_unit');
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->index('level_unit');
        });

        Schema::table('unit', function (Blueprint $table) {
            $table->foreign('id_unit_atasan')->references('id_user')->on('unit')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('unit', function (Blueprint $table) {
            $table->dropForeign(['id_unit_atasan']);
        });

        Schema::dropIfExists('unit');
    }
};
