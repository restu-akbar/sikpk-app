<?php

namespace App\Services;

use App\Mail\AccountCreatedMail;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    public function __construct(
        protected MailService $mailService
    ) {}

    protected function query(): Builder
    {
        return User::query();
    }

    public function getAnggota(int $perPage = 10)
    {
        return $this->query()
            ->withCount('handledReports')
            ->latest()
            ->paginate($perPage);
    }

    public function getAssignableAnggota(int $perPage = 10)
    {
        return $this->query()
            ->where('must_change_password', false)
            ->withCount(['handledReports' => fn (Builder $query) => $query->whereNotIn(
                'progress',
                Report::ARCHIVED_PROGRESS
            )])
            ->latest()
            ->paginate($perPage);
    }

    public function createAnggota(array $data): void
    {
        if (($data['role'] ?? null) === 'ketua') {
            throw new \Exception('Jabatan Ketua tidak dapat ditetapkan melalui form ini.');
        }

        $plainPassword = Str::password();

        $user = $this->query()->create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'role'            => $data['role'] ?? 'anggota',
            'academic_role'   => $data['academic_role'] ?? null,
            'entry_year'      => $data['entry_year'] ?? null,
            'department'    => $data['department'] ?? null,
            'study_program' => $data['study_program'] ?? null,
            'password'      => Hash::make($plainPassword),
        ]);

        $this->mailService->send(
            $user->email,
            new AccountCreatedMail(
                $user->name,
                $user->email,
                $plainPassword
            )
        );
    }

    public function updateAnggota(User $user, array $data): User
    {
        $role = $data['role'] ?? $user->role;

        if ($role === 'ketua' && $user->role !== 'ketua') {
            throw new \Exception('Jabatan Ketua tidak dapat ditetapkan melalui form ini.');
        }

        if ($user->role === 'ketua' && $role !== 'ketua') {
            throw new \Exception('Jabatan Ketua tidak dapat diubah.');
        }

        $user->update([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'role'            => $data['role'] ?? $user->role,
            'academic_role'   => $data['academic_role'] ?? null,
            'entry_year'      => $data['entry_year'] ?? null,
            'department'    => $data['department'] ?? null,
            'study_program' => $data['study_program'] ?? null,
        ]);

        return $user->fresh();
    }

    public function deleteAnggota(User $user): bool
    {
        if ($user->id === auth()->id()) {
            throw new \Exception('Anda tidak dapat menghapus akun sendiri.');
        }

        if ($user->role === 'ketua') {
            throw new \Exception('Ketua tidak dapat dihapus.');
        }

        return $user->delete();
    }
}
