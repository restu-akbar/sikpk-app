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


        User::factory()->count(3)->create([
            'password' => $password,
        ]);
    }
}
