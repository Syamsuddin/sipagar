<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_keuangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_kegiatan_id')->constrained('sub_kegiatan')->restrictOnDelete();
            $table->date('tanggal');
            $table->unsignedBigInteger('jumlah');
            $table->string('uraian', 500);
            $table->string('no_sp2d', 50)->nullable();
            $table->string('lampiran_path', 255)->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sub_kegiatan_id', 'tanggal']);
            $table->index('tanggal');
        });

        DB::statement('ALTER TABLE realisasi_keuangan ADD CONSTRAINT chk_realisasi_keuangan_jumlah CHECK (jumlah > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_keuangan');
    }
};
