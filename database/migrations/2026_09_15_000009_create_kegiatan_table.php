<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('program')->restrictOnDelete();
            $table->string('kode', 20);
            $table->string('nama', 255);
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['program_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan');
    }
};
