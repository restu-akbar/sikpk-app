<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

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

        $data = array_filter($request->only([
            'public_key',
            'encrypted_private_key',
            'emek_password',
            'emek_password_salt',
            'emek_recovery',
            'emek_recovery_salt',
        ]), fn($value) => !is_null($value));
        $data['password'] = Hash::make($request->password);

        $user->update($data);

        if ($request->routeIs('getting-started')) {
            return redirect('/dashboard')->with('success', true);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Password berhasil diubah')]);
        return back();
    }

    public function complete(Request $request)
    {
        $request->user()->update([
            'must_change_password' => false,
        ]);

        return redirect()
            ->route('dashboard')
            ->with('success', 'Setup user berhasil');
    }
}
