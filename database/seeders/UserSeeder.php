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
    }
}
