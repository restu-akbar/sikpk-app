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
}
