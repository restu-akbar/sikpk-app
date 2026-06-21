<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('report_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis', ['korban', 'terlapor']);
            $table->string('nama')->nullable();
            $table->string('nomor_identitas')->nullable();
            $table->string('nomor_wa')->nullable();
            $table->string('jenis_kelamin')->nullable();
            $table->string('peran_akademik')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('prodi')->nullable();
            $table->string('angkatan')->nullable();
            $table->boolean('disabilitas')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_subjects');
    }
};
