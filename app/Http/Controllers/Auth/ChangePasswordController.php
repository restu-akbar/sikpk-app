<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ChangePasswordController extends Controller
{
    public function update(Request $request)
    {
        $request->validate(
            [
                'current_password' => [
                    'required',
                    'current_password',
                ],

                'password' => [
                    'required',
                    'confirmed',
                    Password::min(8)
                        ->mixedCase()
                        ->numbers()
                        ->symbols(),
                ],
            ],
            [
                'current_password.current_password' =>
                'Password saat ini salah.',

                'password.confirmed' =>
                'Konfirmasi password baru tidak cocok.',
            ],
            [
                'current_password' => 'password saat ini',
                'password' => 'password baru',
            ],
        );

        $user = $request->user();

        $user->update([
            'password' => Hash::make($request->password),

            'public_key' => $request->public_key,

            'encrypted_private_key' =>
            $request->encrypted_private_key,

            'emek_password' =>
            $request->emek_password,

            'emek_password_salt' =>
            $request->emek_password_salt,

            'emek_recovery' =>
            $request->emek_recovery,

            'emek_recovery_salt' =>
            $request->emek_recovery_salt,

            'must_change_password' => false,
        ]);

        return redirect()->route('dashboard');
    }
}
