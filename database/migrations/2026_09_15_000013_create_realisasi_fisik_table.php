<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_fisik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_kegiatan_id')->constrained('sub_kegiatan')->cascadeOnDelete();
            $table->unsignedTinyInteger('bulan');
            $table->decimal('persen', 5, 2);
            $table->string('keterangan', 255)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['sub_kegiatan_id', 'bulan']);
        });

        DB::statement('ALTER TABLE realisasi_fisik ADD CONSTRAINT chk_realisasi_fisik_bulan CHECK (bulan BETWEEN 1 AND 12)');
        DB::statement('ALTER TABLE realisasi_fisik ADD CONSTRAINT chk_realisasi_fisik_persen CHECK (persen BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_fisik');
    }
};
