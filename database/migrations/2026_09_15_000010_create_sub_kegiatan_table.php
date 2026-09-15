<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->restrictOnDelete();
            $table->foreignId('bidang_id')->constrained('bidang')->restrictOnDelete();
            $table->foreignId('sumber_dana_id')->constrained('sumber_dana')->restrictOnDelete();
            $table->string('kode', 25);
            $table->string('nama', 255);
            $table->unsignedBigInteger('pagu');
            $table->string('pptk', 100)->nullable();
            $table->unsignedSmallInteger('urutan')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['kegiatan_id', 'kode']);
            $table->index(['kegiatan_id', 'urutan']);
        });

        DB::statement('ALTER TABLE sub_kegiatan ADD CONSTRAINT chk_sub_kegiatan_pagu CHECK (pagu > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_kegiatan');
    }
};
