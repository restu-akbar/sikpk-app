<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class KeyController extends Controller
{
    public function publicKey(Request $request)
    {
        $owners = $request->query('owner', []);

        $publicKeys = User::query()
            ->when(
                empty($owners),
                fn($q) => $q->where('role', 'ketua'),
                fn($q) => $q->whereIn('id', $owners),
            )
            ->whereNotNull('public_key')
            ->pluck('public_key', 'id');

        return response()->json($publicKeys);
    }

    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'emek_password' => $user->emek_password,
            'emek_password_salt' => $user->emek_password_salt,
            'encrypted_private_key' => $user->encrypted_private_key,
        ]);
    }
}
