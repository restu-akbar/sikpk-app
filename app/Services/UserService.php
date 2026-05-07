<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UserService extends BaseService
{
    protected function query(): Builder
    {
        return User::query();
    }

    public function getAnggota(int $perPage = 10)
    {
        return $this->query()
            ->where('role', 'anggota')
            ->latest()
            ->paginate($perPage);
    }

    public function createAnggota(array $data): array
    {
        $plainPassword = Str::password();

        $user = $this->query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => 'anggota',

            'password' => Hash::make($plainPassword),
        ]);

        return [
            'user' => $user,
            'plain_password' => $plainPassword,
        ];
    }

    public function updateAnggota(User $user, array $data): User
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        return $user->fresh();
    }

    public function deleteAnggota(User $user): bool
    {
        return $user->delete();
    }
}
