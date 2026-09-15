<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_anggaran_id')->constrained('tahun_anggaran')->restrictOnDelete();
            $table->string('kode', 20);
            $table->string('nama', 255);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tahun_anggaran_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program');
    }
};
