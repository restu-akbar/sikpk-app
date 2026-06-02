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

            $table->string('nama');
            $table->string('whatsapp', 30);

            $table->string('status_pelapor');
            $table->string('status_civitas')->nullable();

            $table->string('nama_terlapor');
            $table->string('status_terlapor');

            $table->string('jenis_kekerasan');

            $table->string('tempat_kejadian');
            $table->timestamp('waktu_kejadian');

            $table->longText('kronologi');

            $table->json('disabilitas')->nullable();

            $table->boolean('agreed');

            $table->timestamps();
        });

        Schema::create('report_evidences', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('report_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('path');

            $table->json('edeks');

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
