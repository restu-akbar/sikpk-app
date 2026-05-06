<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Ketua
        |--------------------------------------------------------------------------
        */

        User::create([
            'name' => 'Ketua',
            'email' => env('DEFAULT_KETUA_EMAIL', 'ketua@example.com'),
            'password' => Hash::make(
                env('DEFAULT_KETUA_PASSWORD', 'password')
            ),
            'role' => 'ketua',
            'must_change_password' => true,
        ]);
    }
}
