<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make(env('DEFAULT_KETUA_PASSWORD', 'password'));

        User::updateOrCreate(
            ['email' => env('DEFAULT_KETUA_EMAIL', 'ketua@example.com')],
            [
                'name' => 'Ketua',
                'password' => $password,
                'role' => 'ketua',
                'must_change_password' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'anggota1@example.com'],
            [
                'name' => 'Anggota 1',
                'password' => $password,
                'role' => 'anggota',
                'must_change_password' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'anggota2@example.com'],
            [
                'name' => 'Anggota 2',
                'password' => $password,
                'role' => 'anggota',
                'must_change_password' => true,
            ]
        );

        $readyPassword = Hash::make('Ready123!');

        User::updateOrCreate(
            ['email' => 'dosen.ready@example.com'],
            [
                'name' => 'Budi Santoso',
                'password' => $readyPassword,
                'role' => 'anggota',
                'academic_role' => 'dosen',
                'department' => 'Teknik Informatika',
                'must_change_password' => false,
            ]
        );

        User::updateOrCreate(
            ['email' => 'mahasiswa.ready@example.com'],
            [
                'name' => 'Siti Aminah',
                'password' => $readyPassword,
                'role' => 'anggota',
                'academic_role' => 'mahasiswa',
                'entry_year' => 2022,
                'department' => 'Teknik Informatika',
                'must_change_password' => false,
            ]
        );
    }
}
