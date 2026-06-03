<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('academic_role', ['dosen', 'mahasiswa'])
                ->nullable()
                ->after('role');

            $table->smallInteger('entry_year')
                ->nullable()
                ->after('academic_role');

            $table->string('department')
                ->nullable()
                ->after('entry_year');

            $table->string('study_program')
                ->nullable()
                ->after('department');
        });

        // Expand role enum — PostgreSQL stores it as VARCHAR with CHECK constraint
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_role_check
            CHECK (role IN ('ketua', 'wakil_ketua', 'sekretaris', 'anggota'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_role_check
            CHECK (role IN ('ketua', 'anggota'))
        ");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['academic_role', 'entry_year', 'department', 'study_program']);
        });
    }
};
