<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_triwulan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_kegiatan_id')->constrained('sub_kegiatan')->cascadeOnDelete();
            $table->unsignedTinyInteger('triwulan');
            $table->unsignedBigInteger('target_keuangan')->default(0);
            $table->decimal('target_fisik', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['sub_kegiatan_id', 'triwulan']);
        });

        DB::statement('ALTER TABLE target_triwulan ADD CONSTRAINT chk_target_triwulan_tw CHECK (triwulan BETWEEN 1 AND 4)');
        DB::statement('ALTER TABLE target_triwulan ADD CONSTRAINT chk_target_triwulan_fisik CHECK (target_fisik BETWEEN 0 AND 100)');
    }

    public function down(): void
    {
        Schema::dropIfExists('target_triwulan');
    }
};
