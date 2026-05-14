<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request)
    {
        return response()->json([
            'crypto' => [
                'encrypted_private_key'
                => $request->user()->encrypted_private_key,

                'emek_password'
                => $request->user()->emek_password,

                'emek_password_salt'
                => $request->user()->emek_password_salt,
            ],
        ]);
    }
}
