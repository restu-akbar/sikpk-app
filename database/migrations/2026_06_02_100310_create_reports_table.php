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
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('reporter_id')
                ->constrained('reporters')
                ->nullOnDelete();

            $table->string('status_pelapor');

            $table->string('nama_terlapor');
            $table->string('status_terlapor');

            $table->string('jenis_kekerasan')->nullable();

            $table->string('tempat_kejadian')->nullable();
            $table->timestamp('waktu_kejadian')->nullable();

            $table->longText('kronologi')->nullable();

            $table->enum('progress', [
                'Laporan Baru',
                'Klarifikasi',
                'Pemeriksaan',
                'Kesimpulan',
                'Pasca',
                'Selesai',
                'Laporan Dihentikan',
                'Laporan Ditolak',
            ])->default('Laporan Baru');
            $table->boolean('completeness_document')->default(false);

            $table->enum('rejected_reason', [
                'ranah_satgas',
                'unit_lain',
                'tidak_berkenan',
            ])->nullable();
            $table->timestamps();
        });

        Schema::create('report_handlers', function (Blueprint $table) {
            $table->foreignUuid('report_id')
                ->constrained('reports')
                ->cascadeOnDelete()
                ->primary();

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->primary();

            $table->timestamps();
        });
        Schema::create('report_evidences', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('report_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('path');
            $table->json('edeks');
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });

        Schema::create('report_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('report_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('path');
            $table->json('edeks');
            $table->string('original_filename')->nullable();
            $table->string('mime_type')->nullable();
            $table->enum('type', [
                'clarification',
                'inspection',
                'conclusion',
                'post',
            ])->default('clarification');

            $table->enum('subtype', [
                'generated_pdf',
                'documentation',
                'uploaded_pdf',
            ])->default('generated_pdf');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('report_evidences');
    }
};
