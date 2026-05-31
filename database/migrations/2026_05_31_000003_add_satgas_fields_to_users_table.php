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

            $table->foreignId('department_id')
                ->nullable()
                ->after('entry_year')
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('study_program_id')
                ->nullable()
                ->after('department_id')
                ->constrained('study_programs')
                ->nullOnDelete();
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
            $table->dropForeign(['study_program_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn(['academic_role', 'entry_year', 'department_id', 'study_program_id']);
        });
    }
};
