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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->morphs('fileable');
            $table->string('file_type');
            $table->string('name');
            $table->string('path');
            $table->string('url')->nullable();
            $table->string('mime_type');
            $table->unsignedBigInteger('size_in_bytes');
            $table->string('description')->nullable();
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->index('file_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
