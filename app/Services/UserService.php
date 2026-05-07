<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

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
}
