<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index()
    {
        return Inertia::render('master/user/Index', [
            'users' => $this->userService->getAnggota(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required'],
            'email' => [
                'required',
                'email',
                'unique:users,email',
            ]
        ]);

        $this->userService->createAnggota($validated);

        return back()->with(
            'success',
            'User berhasil ditambahkan',
        );
    }

    public function update(
        Request $request,
        User $user
    ) {
        $validated = $request->validate([
            'name' => ['required'],
            'email' => [
                'required',
                'email',
                Rule::unique('users')
                    ->ignore($user->id),
            ]
        ]);

        $this->userService->updateAnggota(
            $user,
            $validated
        );

        return back()->with(
            'success',
            'User berhasil diupdate',
        );
    }

    public function destroy(User $user)
    {
        $this->userService->deleteAnggota($user);

        return back()->with(
            'success',
            'User berhasil dihapus',
        );
    }
}
