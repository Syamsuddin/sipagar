<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tahun_anggaran', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('tahun')->unique();
            $table->enum('status', ['draft', 'aktif', 'terkunci'])->default('draft');
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Rentang tahun docs/07 (CHECK 2020–2034); MySQL 8 menegakkan CHECK.
        DB::statement('ALTER TABLE tahun_anggaran ADD CONSTRAINT chk_tahun_anggaran_rentang CHECK (tahun BETWEEN 2020 AND 2034)');
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_anggaran');
    }
};
