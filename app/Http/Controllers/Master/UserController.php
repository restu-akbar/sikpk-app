<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function index()
    {
        return Inertia::render('master/users/Index', [
            'users' => $this->userService->getAnggota(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'unique:users,email'],
            'role'          => ['required', Rule::in(['ketua', 'wakil_ketua', 'sekretaris', 'anggota'])],
            'academic_role' => ['required', Rule::in(['dosen', 'mahasiswa'])],
            'entry_year'    => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'department'    => ['nullable', 'string', 'max:255'],
            'study_program' => ['nullable', 'string', 'max:255'],
        ]);

        $this->userService->createAnggota($validated);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'          => ['required', Rule::in(['ketua', 'wakil_ketua', 'sekretaris', 'anggota'])],
            'academic_role' => ['required', Rule::in(['dosen', 'mahasiswa'])],
            'entry_year'    => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'department'    => ['nullable', 'string', 'max:255'],
            'study_program' => ['nullable', 'string', 'max:255'],
        ]);

        $this->userService->updateAnggota($user, $validated);

        return back()->with('success', 'Anggota berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->userService->deleteAnggota($user);

        return back()->with('success', 'Anggota berhasil dihapus.');
    }
}
