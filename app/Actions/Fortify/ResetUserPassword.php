<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\ResetsUserPasswords;
use Illuminate\Validation\Rules\Password;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => Password::defaults(),
            'emek_password' => ['required'],
            'emek_password_salt' => ['required'],
        ])->validate();

        $user->forceFill([
            'password' => Hash::make($input['password']),
            'emek_password' => $input['emek_password'],
            'emek_password_salt' => $input['emek_password_salt'],
        ])->save();
    }
}
