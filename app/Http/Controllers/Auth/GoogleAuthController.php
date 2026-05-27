<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Reporter;
use Illuminate\Support\Facades\Auth;
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
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();

        $email = $googleUser->getEmail();

        if (!str_ends_with($email, '@polban.ac.id')) {
            return redirect('/')->with('error', 'Email tidak diizinkan');
        }

        $user = Reporter::firstOrCreate(
            ['email' => $email],
            [
                'name' => $googleUser->getName(),
            ]
        );

        Auth::guard('google')->login($user);

        return redirect('/dashboard');
    }

    public function logout()
    {
        Auth::guard('google')->logout();
        return redirect('/');
    }
}
