<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\StudyProgram;
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
        return Inertia::render('master/user/Index', [
            'users'          => $this->userService->getAnggota(),
            'departments'    => Department::orderBy('name')->get(['id', 'name']),
            'study_programs' => StudyProgram::with('department')
                ->orderBy('name')
                ->get(['id', 'department_id', 'name', 'degree_level']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'unique:users,email'],
            'role'             => ['required', Rule::in(['ketua', 'wakil_ketua', 'sekretaris', 'anggota'])],
            'academic_role'    => ['nullable', Rule::in(['dosen', 'mahasiswa'])],
            'entry_year'       => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'department_id'    => ['nullable', 'exists:departments,id'],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
        ]);

        $this->userService->createAnggota($validated);

        return back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role'             => ['required', Rule::in(['ketua', 'wakil_ketua', 'sekretaris', 'anggota'])],
            'academic_role'    => ['nullable', Rule::in(['dosen', 'mahasiswa'])],
            'entry_year'       => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'department_id'    => ['nullable', 'exists:departments,id'],
            'study_program_id' => ['nullable', 'exists:study_programs,id'],
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
