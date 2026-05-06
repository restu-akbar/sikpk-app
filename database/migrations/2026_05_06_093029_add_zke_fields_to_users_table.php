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
        Schema::table('users', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Zero Knowledge Encryption
            |--------------------------------------------------------------------------
            */

            // Public key user
            $table->text('public_key')
                ->nullable()
                ->after('password');

            // Private key terenkripsi oleh MEK
            $table->longText('encrypted_private_key')
                ->nullable()
                ->after('public_key');

            /*
            |--------------------------------------------------------------------------
            | Password Protected MEK
            |--------------------------------------------------------------------------
            */

            // MEK encrypted menggunakan password aktif user
            $table->longText('emek_password')
                ->nullable()
                ->after('encrypted_private_key');

            // Salt derivation password
            $table->string('emek_password_salt')
                ->nullable()
                ->after('emek_password');

            /*
            |--------------------------------------------------------------------------
            | Recovery Protected MEK
            |--------------------------------------------------------------------------
            */

            // MEK encrypted menggunakan temporary password awal
            $table->longText('emek_recovery')
                ->nullable()
                ->after('emek_password_salt');

            // Salt derivation recovery
            $table->string('emek_recovery_salt')
                ->nullable()
                ->after('emek_recovery');

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */

            // Force user change password after first login
            $table->boolean('must_change_password')
                ->default(true)
                ->after('emek_recovery_salt');

            $table->enum('role', [
                'ketua',
                'anggota',
            ])
                ->default('anggota')
                ->after('emek_recovery_salt');

            // Timestamp setup encryption
            $table->timestamp('encryption_initialized_at')
                ->nullable()
                ->after('encryption_version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'public_key',
                'encrypted_private_key',

                'emek_password',
                'emek_password_salt',

                'emek_recovery',
                'emek_recovery_salt',

                'must_change_password',

                'role',
                'encryption_initialized_at',
            ]);
        });
    }
};
