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
        Schema::create('report_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('report_id')
                ->constrained('reports')
                ->cascadeOnDelete();

            $table->enum('progress', [
                'Laporan Baru',
                'Klarifikasi',
                'Pemeriksaan',
                'Kesimpulan',
                'Pasca',
                'Selesai',
                'Laporan Dihentikan',
                'Laporan Ditolak',
            ]);

            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_logs');
    }
};
