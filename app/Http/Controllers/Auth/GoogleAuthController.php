<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Reporter;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function callback()
    {
        try {
            if (request()->has('error')) {
                return redirect()->route('google.login')
                    ->with('error', 'Login dibatalkan atau tidak diizinkan oleh Google');
            }
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $email = $googleUser->getEmail();
            if (!str_ends_with($email, '@polban.ac.id')) {
                return redirect()
                    ->route('google.login')
                    ->with('toast', [
                        'type' => 'error',
                        'message' => 'Login hanya diperbolehkan menggunakan akun email kampus (@polban.ac.id).'
                    ]);
            }

            $user = Reporter::firstOrCreate(
                ['email' => $email],
                ['name' => $googleUser->getName()]
            );

            Auth::guard('google')->login($user);

            return redirect()->route('landing');
        } catch (\Exception $e) {
            return redirect()->route('google.login')
                ->with('error', $e ?? 'Login gagal, silakan coba lagi');
        }
    }

    public function logout()
    {
        Auth::guard('google')->logout();
        return redirect('/');
    }
}
